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

            $trialDays = config('cashier.trial_days', 14); // Default trial days
            $trialEndDate = $tenantCreatedAt->addDays($trialDays);
            $currentDate = now();

            if ($currentDate->greaterThan($trialEndDate)) {
                // set in session subscribed==false
                session()->put('subscribed', false);
                // remove remaining days if present
                session()->forget('remaining_days');
                return redirect()->route('billing')->with('error', 'Your trial or your subscription has ended. Please subscribe to continue using the service.');
            } else {
                // the trial is still active
                session()->put('subscribed', true);

                // Calculate remaining days and convert to integer (floor to be conservative)
                $remainingDays = $currentDate->diffInDays($trialEndDate);

                // If less than 1 day remains but trial hasn't ended, show "1 day"
                if ($remainingDays == 0 && $currentDate->lt($trialEndDate)) {
                    $remainingDays = 1;
                }

                // Store as integer
                session()->put('remaining_days', (int) $remainingDays);
                return $next($request);
            }
        }

        session()->forget('remaining_days');

        session()->put('subscribed', true);
        return $next($request);
    }
}
