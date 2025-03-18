<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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
        if ($request->has('debug-session')) {
            $tenantId = session('tenant_id');
            $tenant = $tenantId ? \App\Models\Tenant::find($tenantId) : null;

            return response()->json([
                'session_has_tenant_id' => !is_null($tenantId),
                'tenant_id_value' => $tenantId,
                'tenant_exists' => !is_null($tenant),
                'session_all' => session()->all()
            ]);
        }

        return $next($request);
    }
}
