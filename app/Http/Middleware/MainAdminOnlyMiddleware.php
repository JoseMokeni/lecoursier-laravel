<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MainAdminOnlyMiddleware
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
        $tenantId = session('tenant_id');

        if (Auth::user()->username !== $tenantId) {
            return redirect('/dashboard')->with('error', 'Seuls l\'administrateur principal a accès à cette ressource.');
        }

        return $next($request);
    }
}
