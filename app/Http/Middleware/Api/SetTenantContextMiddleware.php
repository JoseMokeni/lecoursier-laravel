<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get tenant ID from the header
        $tenantId = $request->header('x-tenant-id');

        // If tenant ID is not provided, return a 403 response
        if (!$tenantId) {
            return response()->json(['error' => 'Tenant ID is required as x-tenant-id header'], 403);
        }

        // If tenant ID is provided, initialize tenancy
        // Check if the tenant ID is valid
        $tenant = \App\Models\Tenant::find($tenantId);
        if (!$tenant) {
            return response()->json(['error' => 'Invalid tenant ID'], 403);
        }
        // Set the tenant context
        tenancy()->initialize($tenant);
        // Optionally, you can set the tenant ID in the request for further use
        $request->attributes->set('tenant_id', $tenantId);

        return $next($request);
    }
}
