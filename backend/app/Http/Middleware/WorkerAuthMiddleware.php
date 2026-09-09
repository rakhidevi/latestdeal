<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkerAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('services.worker.key') ?: config('services.worker.api_key') ?: env('WORKER_API_KEY') ?: env('API_KEY');
        
        if (empty($expectedKey)) {
            // Fails closed if not configured
            return response()->json(['error' => 'Server misconfigured'], 500);
        }

        // Try Bearer token first, fallback to custom header, then fallback to JSON payload
        $token = $request->bearerToken() ?: $request->header('X-Worker-Key') ?: $request->input('worker_api_key');

        if (!$token || !hash_equals((string) $expectedKey, (string) $token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
