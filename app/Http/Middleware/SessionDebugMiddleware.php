<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class SessionDebugMiddleware
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
        // Check if tenant_id exists in session
        $tenantId = session('tenant_id');

        if ($tenantId) {
            // Check if the tenant exists
            $tenant = Tenant::find($tenantId);

            if (!$tenant) {
                // Invalid tenant, clear it from session
                Log::warning("Invalid tenant ID in session: {$tenantId}. Removing from session.");
                session()->forget('tenant_id');
                session()->save();
            }
        }

        return $next($request);
    }
}
