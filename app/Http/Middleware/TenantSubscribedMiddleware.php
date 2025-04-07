<?php

namespace App\Http\Middleware;

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
        $tenant = tenancy()->tenant;

        // Check if the tenant is subscribed
        if (!$tenant->subscribed(config('cashier.products.default'))) {
            // Check if the trial has ended
            $tenantCreatedAt = $tenant->created_at;

            $trialDays = config('cashier.trial_days', 14);
            $trialEndDate = $tenantCreatedAt->addDays($trialDays);
            $currentDate = now();

            if ($currentDate->greaterThan($trialEndDate)) {
                // The trial has ended, redirect to the billing page
                return redirect()->route('billing')->with('error', 'Your trial has ended. Please subscribe to continue using the service.');
            }
        }

        return $next($request);
    }
}
