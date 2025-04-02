<?php

namespace App\Http\Middleware\Api;

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
        // Get tenant ID from the request header
        $tenantId = $request->header('x-tenant-id');

        // Check if the tenant ID is provided and active
        if ($tenantId) {
            $tenantService = app()->make('App\Services\TenantService');
            $tenant = $tenantService->getTenant($tenantId);

            if (!$tenant || $tenant->status !== 'active') {
                return response()->json([
                    'message' => 'The tenant is inactive. Please connect to the web admin dashboard to activate it.',
                    'error' => 'inactive_tenant'
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'x-tenant-id header is required.',
                'error' => 'missing_tenant_id'
            ], 400);
        }

        return $next($request);
    }
}
