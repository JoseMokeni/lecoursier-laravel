<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Tenancy\Facades\Tenancy;

class LoginController extends Controller
{
    /**
     * Display the login view.
     *
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        // check the session for tenant ID
        $tenantId = session('tenant_id');
        if ($tenantId) {
            // find the tenant
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                // switch to tenant context
                tenancy()->initialize($tenant);
                if (Auth::check()) {
                    return redirect()->intended('/dashboard');
                }
            }
        }

        return view('pages.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Check if tenant exists
        $tenant = Tenant::find($validated['company_code']);
        if (!$tenant) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['company_code' => 'Code d\'entreprise invalide']);
        }

        // Switch to tenant context
        tenancy()->initialize($tenant);

        // Attempt authentication
        if (Auth::attempt([
            'username' => $validated['username'],
            'password' => $validated['password']
        ])) {
            $request->session()->regenerate();

            // Store tenant ID in session for future requests
            $request->session()->put('tenant_id', $tenant->id);

            return redirect()->intended('/dashboard');
        }

        // Authentication failed
        return back()
            ->withInput($request->except('password'))
            ->withErrors([
                'username' => 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.',
            ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
