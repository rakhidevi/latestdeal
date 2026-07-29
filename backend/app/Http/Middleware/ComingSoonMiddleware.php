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
        // Coming soon mode is permanently disabled for production
        // if (env('APP_ENV') === 'production' && env('COMING_SOON_ENABLED', true)) { ... }

        return $next($request);
    }
}
