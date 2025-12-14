<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use App\Services\SidebarNotificationService;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserCredentials;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $notificationService;

    public function __construct(SidebarNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }


//Updating Permissions independently not in edit nor create only for the User
    public function updatePermissions(Request $request, Users $user)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);
    
        $user->syncPermissions($request->permissions ?? []);
    
        // Re‑fetch everything for the view
        $users           = Users::all();
        $permissions     = ['view-pending','view-approved','view-released','view-cancelled', 'add user', 'edit user', 'delete user', 'Add-Supply', 'Edit-Supply', 'Delete-Supply', 'Edit-Ris', 'Deny'];
        $userPermissions = $user->getPermissionNames()->toArray();
    
        return view('usersmanage.index', compact(
            'users',
            'permissions',
            'userPermissions'
        ))->with('success', 'Permissions updated successfully.');
    }
    

    public function index()
    {
        // Fetch users with their permissions relationship loaded and paginate 5 per page
        $users = Users::with('permissions')->paginate(5);

        // Get actual permissions from database instead of hardcoded array
        $permissions = \Spatie\Permission\Models\Permission::pluck('name')->toArray();

        // Get notification counts for sidebar
        $user = Auth::user();
        $notificationCounts = $this->notificationService->getNotificationCounts($user->Office);

        return view('usersmanage.index', compact('users', 'permissions', 'notificationCounts'));
    }

    public function store(Request $request)
    {
        // First, let's check what permissions actually exist
        $existingPermissions = Permission::pluck('name')->toArray();
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'required|string|max:2',
            'last_name' => 'required|string|max:255',
            'office' => 'required|string|regex:/^[A-Za-z\s.,_]+$/|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@gmail.com')) {
                        $fail('Only Gmail accounts are accepted.');
                    }
                },
            ],
            'role'      => 'required|in:SuperAdmin,admin,user',
            'permissions'    => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'permissions.*.exists' => 'The permission ":value" is invalid. Available permissions: ' . implode(', ', $existingPermissions),
            'email.ends_with' => 'Only Gmail accounts are accepted.',
        ]);

        // --- MODIFIED ---
        // Instead of taking a password from the request, we generate a secure temporary one.
        $temporaryPassword = Str::random(10); // e.g., "aK8!bZp3$T"
        $email = strtolower($request->email); // --- ADDED --- (for token generation)

        $user = Users::create([ //Adding a new User
            'first_name'      => $request->first_name,
            'middle_initial'  => $request->middle_initial,
            'last_name'       => $request->last_name,
            'office'          => $request->office,
            'email'           => $email, // --- MODIFIED --- (uses the lowercased variable)
            'password'        => Hash::make($temporaryPassword), // --- MODIFIED ---
            'role'            => $request->role,
        ]);

        // 1) Sync roles (Spatie) - (Your code, unchanged)
        $user->syncRoles([$request->role]);

        // 2) Sync the selected permissions (if provided) - (Your code, unchanged)
        $user->syncPermissions($request->permissions ?? []);


        // --- ALL LOGIC BELOW IS ADDED TO CREATE THE SECURE LINK ---

        // 3. Generate the RAW token for the one-time reset link
        $token = Str::random(64);

        // 4. Store the HASHED token
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token), // Hashed for security
                'created_at' => now(),
            ]
        );

        // 5. Build the SIGNED URL right here in the controller
        $expires = now()->addMinutes(10)->timestamp;
        $rawKey = config('app.key');
        if (strpos($rawKey, 'base64:') === 0) {
            $rawKey = base64_decode(substr($rawKey, 7));
        }
        $sig = hash_hmac('sha256', $token . '|' . $email . '|' . $expires, $rawKey);
        
        $resetUrl = route('email.reset', [
            'token'   => $token, // Pass RAW token in URL
            'email'   => $email,
            'expires' => $expires,
            'sig'     => $sig
        ]);

        // --- END OF NEW LOGIC ---


        // 6) Send welcome email with credentials
        try {
            $fullName = $request->first_name . ' ' . $request->middle_initial . '. ' . $request->last_name;
            
            // --- MODIFIED ---
            // We now pass the $temporaryPassword and the new $resetUrl to your Mailable class.
            Mail::to($request->email)
                ->send(new NewUserCredentials($fullName, $request->email, $temporaryPassword, $resetUrl)); 
            
            $emailSent = true;
        } catch (\Exception $e) {
            \Log::error('Failed to send email to new user: ' . $e->getMessage());
            $emailSent = false;
        }

        // 7) Now fetch the user's current permissions to pass back to the view - (Your code, unchanged)
        $userPermissions = $user->getPermissionNames()->toArray();

        // 8) Fetch all users to display in the index view - (Your code, unchanged)
        $users = Users::with('permissions')->get();

        // Use actual permissions from database - (Your code, unchanged)
        $permissions = Permission::pluck('name')->toArray();

        $successMessage = 'User added successfully!';
        if ($emailSent) {
            $successMessage .= ' Login credentials have been emailed to the user.';
        } else {
            $successMessage .= ' However, we could not send the login credentials email.';
        }

        return redirect()
            ->route('usersmanage.index')
            ->with('success', $successMessage);
    }

    public function destroy($id) //Deletion of the user
    {
        Users::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'User and its permissions deleted successfully');
    }
    public function edit($id)
    {
        // 1. Fetch the user being edited
        $user = Users::findOrFail($id);
    
        // 2. Fetch paginated users for the table (index view needs this)
        $users = Users::with('permissions')->paginate(5);
    
        // 3. Define the master list of permission slugs (kept for the blade form)
        $permissions = ['view-pending', 'view-approved', 'view-released', 'view-cancelled', 'add user', 'edit user', 'delete user', 'Add-Supply', 'Edit-Supply', 'Delete-Supply', 'Edit-Ris', 'Deny'];
    
        // 4. Fetch the user's current assigned permission NAMES
        $userPermissions = $user->getPermissionNames()->toArray();
    
        // 5. Provide notification counts for sidebar (use authenticated user)
        $authUser = Auth::user();
        $notificationCounts = $this->notificationService->getNotificationCounts($authUser->Office);
    
        // 6. Return to the index view with everything it needs
        return view('usersmanage.index', compact(
            'users',
            'permissions',
            'userPermissions',
            'notificationCounts'
        ));
    }

    //updating the user and giving permissions edit modal
    public function update(Request $request, $id)
    {
        // NEW: normalize email from hidden "Email" when visible "email" is absent
        if (!$request->has('email') && $request->has('Email')) {
            $request->merge(['email' => $request->input('Email')]);
        }

        // Determine which column(s) exist for email
        $emailColumn = Schema::hasColumn('users', 'email')
            ? 'email'
            : (Schema::hasColumn('users', 'Email') ? 'Email' : 'email');

        // Get existing permissions for better error message
        $existingPermissions = \Spatie\Permission\Models\Permission::pluck('name')->toArray();

        // VALIDATION: include email (gmail-only, unique except current user)
        $request->validate([
            'role'            => 'required|in:superadmin,admin,user',
            'first_name'      => 'required|string|max:255',
            'middle_initial'  => 'nullable|string|max:2',
            'last_name'       => 'required|string|max:255',
            'email'           => [
                'required',
                'email',
                Rule::unique('users', $emailColumn)->ignore($id),
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@gmail.com')) {
                        $fail('Only Gmail accounts are accepted.');
                    }
                },
            ],
            'permissions'     => 'nullable|array',
            'permissions.*'   => 'string|exists:permissions,name',
        ], [
            'permissions.*.exists' => 'The permission ":value" is invalid. Available permissions: ' . implode(', ', $existingPermissions),
        ]);

        $users = Users::findOrFail($id);

        // Build update data
        $updateData = [
            'first_name'     => $request->input('first_name'),
            'middle_initial' => $request->input('middle_initial'),
            'last_name'      => $request->input('last_name'),
            'role'           => $request->input('role'),
        ];

        // NEW: persist email into whichever column(s) exist
        $newEmail = trim($request->input('email'));
        if (Schema::hasColumn('users', 'email')) {
            $updateData['email'] = $newEmail;
        }
        if (Schema::hasColumn('users', 'Email')) {
            $updateData['Email'] = $newEmail;
        }

        $users->update($updateData);

        // Sync roles and permissions
        $users->syncRoles([$request->role]);
        $users->syncPermissions($request->permissions ?? []);

        // Fetch the updated permissions for the user
        $userPermissions = $users->getPermissionNames()->toArray();
        $permissions = Permission::pluck('name')->toArray();

        return redirect()
            ->route('usersmanage.index')
            ->with([
                'success' => 'Account and Permissions updated successfully!',
                'userPermissions' => $userPermissions,
                'permissions' => $permissions,
            ]);
    }

   
}




