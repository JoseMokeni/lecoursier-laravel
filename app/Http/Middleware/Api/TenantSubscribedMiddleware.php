<?php

namespace App\Http\Middleware\Api;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantSubscribedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->headers->get('x-tenant-id');
        $tenant = Tenant::find($tenantId);

        // Check if the tenant is subscribed
        if (!$tenant->subscribed(config('cashier.products.default'))) {
            // Check if the trial has ended
            $tenantCreatedAt = $tenant->created_at;

            $trialDays = config('cashier.trial_days', 14);
            $trialEndDate = $tenantCreatedAt->addDays($trialDays);
            $currentDate = now();

            if ($currentDate->greaterThan($trialEndDate)) {
                // The trial has ended, return json error response
                return response()->json([
                    'message' => 'The trial has ended. Please contact administrator to subscribe.',
                    'error' => 'not_subscribed',
                ], 403);
            }
        }

        return $next($request);
    }
}
