<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveTenantMiddleware
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
                return redirect()->route('error.tenant-inactive');
            }
        } else {
            return redirect()->route('error.tenant-required');
        }

        return $next($request);
    }
}
