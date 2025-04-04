<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Mail\WelcomeUserMail;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

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
        $tenant = Tenant::create([
            'id' => $validated['code'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
        ]);

        // Switch to the tenant environment
        tenancy()->initialize($tenant);

        // Save the default password before encryption
        $defaultPassword = $validated['code'];

        // Create a user with the company code as username
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['code'],
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt($defaultPassword)
        ]);

        // Prepare user data for the welcome email
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['code'],
            'original_password' => $defaultPassword
        ];

        // Send welcome email with credentials
        try {
            Mail::to($validated['email'])
                ->send(new WelcomeUserMail($userData, $tenant->id));
        } catch (\Exception $e) {
            // Log the error but don't stop the process
            Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        // Automatically authenticate the user
        Auth::login($user);

        $request->session()->regenerate();

        // Store tenant ID in session for future requests
        $request->session()->put('tenant_id', $tenant->id);

        // Redirect directly to dashboard instead of login page
        return redirect('/dashboard')
            ->with('success', 'Company registered successfully. You are now logged in. Check your email for login details.');
    }
}
