<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Tenancy\Facades\Tenancy;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     *
     * @return View
     */
    public function create(): View
    {
        return view('pages.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param RegisterRequest $request
     * @return RedirectResponse
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Get validated data
        $validated = $request->validated();

        // Check if tenant with this ID already exists
        if (Tenant::find($validated['code'])) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'Ce code de compagnie est déjà utilisé']);
        }

        // Create a new tenant with company code as tenant id
        $domains = config('tenancy.central_domains');
        $tenant = Tenant::create([
            'id' => $validated['code']
        ]);

        $tenant->domains()->createMany(array_map(function ($domain) {
            return ['domain' => $domain];
        }, $domains));

        // Switch to the tenant environment
        tenancy()->initialize($tenant);
        // Or alternatively use the Tenancy facade
        // Tenancy::setTenant($tenant);

        // Create a new company in the tenant database
        $company = Company::create($validated);

        // Create a user with the company code as username
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['code'],
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt($validated['code'])
        ]);

        // End the tenant context if needed
        // tenancy()->end();

        // Redirect to admin login page with success message
        return redirect('/admin')
            ->with('success', 'Company registered successfully. You can now login using your company code as both username and password.');
    }
}
