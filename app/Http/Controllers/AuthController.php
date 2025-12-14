<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showSignupForm()
    {
        return view('auth.signup');
    }

    public function login(Request $request)
    {
       $request->validate([
        'identifier' => [
            'required',
            function ($attribute, $value, $fail) {
                // Only validate as email if it contains @ symbol
                if (strpos($value, '@') !== false) {
                    if (!str_ends_with(strtolower($value), '@gmail.com')) {
                        $fail('If using email to login, only Gmail accounts are accepted.');
                    }
                }
            }
        ],
        'password' => 'required',
    ]);

        // Check if user is in cooldown
        if (session('cooldown_until') && now()->lt(session('cooldown_until'))) {
            return redirect()->back()->withErrors(['error' => 'Please wait before attempting to login again.'])
                                   ->with('cooldown_until', session('cooldown_until'));
        }

        $user = Users::whereRaw('BINARY office = ?', [$request->identifier])
                    ->orWhereRaw('BINARY email = ?', [$request->identifier])
                    ->first();

        // If account doesn't exist
        if (!$user) {
            return redirect()->back()
                ->withErrors(['error' => 'Account does not exist'])
                ->withInput($request->except('password'));
        }

        // Account exists but password incorrect
        if (!Hash::check($request->password, $user->password)) {
            $maxAttempts = 3;
            $attempts = session('login_attempts', 0) + 1;
            session(['login_attempts' => $attempts]);

            // If reached max attempts, set cooldown
            if ($attempts >= $maxAttempts) {
                $cooldownMinutes = 1;
                $cooldownUntil = now()->addMinute($cooldownMinutes);
                session(['cooldown_until' => $cooldownUntil]);
                session(['login_attempts' => 0]); // Reset attempts
                return redirect()->back()
                    ->withErrors(['error' => 'Too many failed attempts. Please wait 1 minute before trying again.'])
                    ->with('cooldown_until', $cooldownUntil);
            }

            $retriesLeft = $maxAttempts - $attempts;
            return redirect()->back()
                ->withErrors(['error' => "Invalid Office/Email or Password. {$retriesLeft} retries left."])
                ->withInput($request->except('password'));
        }

        // Reset cooldown on successful login
        session()->forget('cooldown_until');
        session()->forget('login_attempts');

        // Log in the user
        Auth::login($user);

        // Regenerate the session to avoid session fixation
        $request->session()->regenerate();

        // Store the user's role in the session
        session(['role' => $user->role]);

        // Redirect based on the user's role
        if (strtolower($user->role) === 'admin' || strtolower($user->role) === 'superadmin') {
            return redirect()->route('Admin.admin');  // Admin and SuperAdmin use the same dashboard
        } else {
            return redirect()->route('dashboard.use');  // Non-admin users are redirected elsewhere
        }
    }
    

    public function logout(Request $request)
    {
           // Log out the user
    Auth::logout();

    // Invalidate the session
    $request->session()->invalidate();

    // Regenerate the CSRF token to prevent CSRF attacks
    $request->session()->regenerateToken();

    return redirect()->route('login');
    }


}