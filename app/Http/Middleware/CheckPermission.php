<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        if (Auth::check() && Auth::user()->permissions->contains('name', $permission)) {
            // Check if the user's permissions contain the required permission
            return $next($request);
        }

        // Log unauthorized access attempts for debugging
        Log::warning('Unauthorized access attempt', [
            'user_id' => Auth::id(),
            'required_permission' => $permission,
            'user_permissions' => Auth::user()->permissions->pluck('name')->toArray(), // Ensure proper access
        ]);

        abort(403, 'Unauthorized action.');
    }
}
