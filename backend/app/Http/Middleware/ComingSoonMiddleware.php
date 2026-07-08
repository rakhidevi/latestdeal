<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ComingSoonMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if we are in coming soon mode
        if (env('APP_ENV') === 'production' && env('COMING_SOON_ENABLED', true)) {
            
            // Allow bypassing via query parameter
            if ($request->has('admin_bypass') && $request->admin_bypass === 'true') {
                session(['bypassed_coming_soon' => true]);
                return redirect($request->url());
            }

            // Check if user has bypassed or is accessing admin routes
            if (!session('bypassed_coming_soon') && !$request->is('admin*') && !$request->is('login')) {
                return response()->view('coming-soon');
            }
        }

        return $next($request);
    }
}
