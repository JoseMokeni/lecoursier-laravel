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
                if ($request->user()->username == session()->get('tenant_id')){
                    return redirect()->route('billing')->with('error', 'Votre période d\'essai ou votre abonnement est terminé. Veuillez vous abonner pour continuer à utiliser le service.');
                }
                else {
                    // if the user was trying to access dashboard, just next with error

                    if ($request->routeIs('dashboard')) {
                        return $next($request);
                    }
                    return redirect()->route('dashboard')->with('error', 'Votre période d\'essai ou votre abonnement est terminé. Veuillez vous abonner pour continuer à utiliser le service.');
                }
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
