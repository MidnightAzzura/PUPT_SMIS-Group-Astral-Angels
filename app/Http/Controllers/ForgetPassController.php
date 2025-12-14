<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ForgetPassController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('auth.ForgetPass');
    }

    public function sendResetLink(Request $request)
    {
       // Basic format validation first
       $request->validate([
           'email' => 'required|email',
       ]);

       // Explicit checks to return clear custom messages
       $email = strtolower($request->email);

       if (!str_ends_with($email, '@gmail.com')) {
           return back()->with('error', 'Only Gmail accounts are accepted.')->withInput($request->except('password'));
       }

       if (!Users::where('email', $email)->exists()) {
           return back()->with('error', 'The email address you entered is not registered.')->withInput($request->except('password'));
       }

        // Generate a token
        $token = Str::random(64);

        // Store token using updateOrInsert to avoid duplicate email issues
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send reset email
        try {
            Mail::send('auth.email-reset', ['token' => $token, 'email' => $request->email], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('SMIS Reset Password');
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email. ' . $e->getMessage());
        }
        return back()->with('success', 'We have sent a password reset link to your email!');
    }

    // Shows or redirect to the reset password form
    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

 // In ForgetPassController.php
 //Stores the new password set by the user

public function store(Request $request)
{
    // Basic format and password validation
    $request->validate([
           'email' => 'required|email',
           'password' => 'required|min:8|confirmed',
           'token' => 'required',
    ]);

    $email = strtolower($request->email);

    // Explicit checks for Gmail and registration
    if (!str_ends_with($email, '@gmail.com')) {
        return back()->with('error', 'Only Gmail accounts are accepted.')->withInput($request->except('password'));
    }

    if (!Users::where('email', $email)->exists()) {
        return back()->with('error', 'The email address you entered is not registered.')->withInput($request->except('password'));
    }

    $resetData = DB::table('password_reset_tokens')->where('email', $request->email)->first();

    if (!$resetData || !Hash::check($request->token, $resetData->token)) {
        return back()->with('error', 'Invalid or expired reset link.');
    }
    
    // Check if token is more than 10 minutes old
    if (now()->diffInMinutes($resetData->created_at) > 10) {
        // Delete expired token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return back()->with('error', 'Your password reset link has expired. Please request a new one.');
    }

    Users::where('email', $request->email)->update([
        'password' => Hash::make($request->password) // Hash the new password and rewrite the old one
    ]);

    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    return redirect()->route('login')->with('success', 'Your password has been reset successfully.');
}

}
