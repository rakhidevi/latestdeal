<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudioAdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Require authentication
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Authentication required for Commerce Intelligence Studio.');
        }

        $user = Auth::user();

        // Check if user has an authorized role (Developer, Operations, Executive)
        $allowedRoles = ['developer', 'operations', 'executive', 'admin'];
        if (!in_array($user->role ?? 'guest', $allowedRoles)) {
            Log::warning("Unauthorized Studio access attempt by user {$user->id}");
            abort(403, 'Unauthorized. You do not have permission to access the Commerce Intelligence Studio.');
        }

        return $next($request);
    }
}
