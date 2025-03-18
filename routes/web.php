<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        // Public routes that don't require authentication
        Route::get('/', function () {
            return view('pages/landing');
        })->name('landing');

        // Session debug route - add this route temporarily
        Route::get('/debug-session', function () {
            $tenantId = session('tenant_id');
            $tenant = $tenantId ? \App\Models\Tenant::find($tenantId) : null;

            return [
                'session_has_tenant_id' => !is_null($tenantId),
                'tenant_id_value' => $tenantId,
                'tenant_exists' => !is_null($tenant),
                'session_all' => session()->all()
            ];
        });

        // Add a one-time session reset endpoint for troubleshooting
        Route::get('/reset-session', function () {
            // End tenancy if initialized
            try {
                if (tenancy()->initialized) {
                    tenancy()->endTenancy();
                }
            } catch (\Exception $e) {
                // Ignore errors
            }

            // Clear all session data
            session()->flush();

            // Clear authentication
            Auth::logout();

            // Clear cookies
            $cookies = request()->cookies->all();
            $response = redirect('/')->with('status', 'Session has been completely reset');

            // Forget all cookies
            foreach ($cookies as $name => $value) {
                $response->withCookie(cookie()->forget($name));
            }

            return $response;
        })->name('reset.session');

        Route::get('/hello', function () {
            return 'Hello from Le Coursier Saas (Laravel)';
        });

        // Contact form submission
        Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

        // Privacy Policy route
        Route::get('/privacy-policy', function () {
            return view('pages.privacy');
        })->name('privacy.policy');

        // Registration routes
        Route::get('/register', [RegisterController::class, 'create'])->name('register');
        Route::post('/register', [RegisterController::class, 'store']);

        // Login routes
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store']);

    });
}

