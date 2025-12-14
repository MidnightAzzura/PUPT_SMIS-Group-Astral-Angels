<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserSettingsController extends Controller
{
    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Password change request received', [
                'user_id' => Auth::id(),
                'has_current_password' => $request->filled('current_password'),
                'has_new_password' => $request->filled('new_password'),
                'has_confirmation' => $request->filled('new_password_confirmation')
            ]);

            // Validate the form data
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d_]+$/'
                ],
                'new_password_confirmation' => 'required|string'
            ], [
                'current_password.required' => 'Current password is required',
                'new_password.required' => 'New password is required',
                'new_password.min' => 'Password must be at least 8 characters',
                'new_password.regex' => 'Password must contain at least one letter and one number. Underscores are allowed.',
                'new_password.confirmed' => 'Password confirmation does not match',
                'new_password_confirmation.required' => 'Please confirm your new password'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                Log::warning('Password change validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the authenticated user
            $user = Auth::user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                Log::warning('Incorrect current password attempt', [
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'current_password' => ['The current password is incorrect.']
                    ]
                ], 422);
            }

            // Update the password
            $user->password = Hash::make($request->new_password);
            $user->save();

            Log::info('Password changed successfully', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully! You can now use your new password to login.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Password change error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Save user theme preference
     */
    public function saveThemePreference(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'theme' => 'required|string|in:light,dark',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get authenticated user
        $user = Auth::user();
        
        // Store preference in user_preferences table or similar
        // This requires you to have a preferences column in your users table
        // or a separate user_preferences table
        
        // For now, we'll use a simple JSON column approach
        $preferences = json_decode($user->preferences ?? '{}', true);
        $preferences['theme'] = $request->theme;
        $user->preferences = json_encode($preferences);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Theme preference saved successfully.',
            'theme' => $request->theme
        ]);
    }
}
