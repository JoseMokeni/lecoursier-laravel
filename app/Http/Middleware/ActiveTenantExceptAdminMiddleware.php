<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActiveTenantExceptAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get tenant ID from the session
        $tenantId = session('tenant_id');
        // Check if the tenant ID is active
        if ($tenantId) {
            $tenantService = app()->make('App\Services\TenantService');
            $tenant = $tenantService->getTenant($tenantId);

            if (!$tenant || $tenant->status !== 'active') {
                // If the tenant is not active, check if the user is the main admin (username == tenantId)
                if (Auth::check() && Auth::user()->role === 'admin' && Auth::user()->username === $tenantId) {
                    // Allow admin access even if the tenant is inactive
                    return $next($request);
                } else {
                    return redirect()->route('error.tenant-inactive');
                }
            }
        } else {
            return redirect()->route('error.tenant-required');
        }

        return $next($request);
    }
}
