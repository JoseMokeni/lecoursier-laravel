<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check if the request has a bearer token
        if (!$request->bearerToken()) {
            return response()->json([
                'message' => 'Unauthorized. Please provide a valid token.',
                'status' => 'error'
            ], 401);
        }

        // Check user from the token
        $user = $request->user('api');
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. Invalid token.',
                'status' => 'error'
            ], 401);
        }

        // Check if the user is active
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Unauthorized. User is inactive.',
                'status' => 'error'
            ], 403);
        }

        // User is authenticated, proceed with the request
        return $next($request);
    }
}
