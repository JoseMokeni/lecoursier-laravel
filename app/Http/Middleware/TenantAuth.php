<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if tenant ID exists in session
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            // No tenant ID, redirect to login
            return redirect()->route('login');
        }

        // Check if user is authenticated
        // set the tenant context
        $tenant = \App\Models\Tenant::find($tenantId);
        tenancy()->initialize($tenant);

        if (Auth::guest()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
