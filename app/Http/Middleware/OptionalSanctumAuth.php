<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptionalSanctumAuth
{
    /**
     * Handle an incoming request.
     * Allows both Sanctum token and session authentication
     */
    public function handle(Request $request, Closure $next)
    {
        // Try Sanctum authentication first
        if ($request->bearerToken()) {
            // Token provided, Sanctum will handle it
            return $next($request);
        }
        
        // No token, try session authentication
        if (Auth::guard('web')->check()) {
            // User is authenticated via session
            Auth::setUser(Auth::guard('web')->user());
            return $next($request);
        }
        
        // No authentication at all - return 401
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated',
        ], 401);
    }
}
