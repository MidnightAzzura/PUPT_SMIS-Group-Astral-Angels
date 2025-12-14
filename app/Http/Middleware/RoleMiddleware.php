<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
     public function handle(Request $request, Closure $next, $roles = null): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->withErrors(['error' => 'You must be logged in to access this page.']);
        }

        $user = Auth::user();
        $userRole = strtolower($user->role ?? ($user->is_admin ? 'admin' : 'user'));

        // allow superadmin always
        if ($userRole === 'superadmin') {
            return $next($request);
        }

        if ($roles) {
            $allowedRoles = array_map('trim', explode('|', strtolower($roles)));
            if (! in_array($userRole, $allowedRoles)) {
                // logout and invalidate session on role mismatch
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['error' => 'Access denied. Please log in with an appropriate account.']);
            }
        }

        return $next($request);
    }
}
