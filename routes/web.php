<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\TenantController;
use App\Http\Controllers\ErrorController;
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
            $tenant = $tenantId ? Tenant::find($tenantId) : null;

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

        // Error routes (moved outside middleware groups for direct access)
        Route::get('/errors/tenant-inactive', [ErrorController::class, 'tenantInactive'])->name('error.tenant-inactive');
        Route::get('/errors/tenant-required', [ErrorController::class, 'tenantRequired'])->name('error.tenant-required');
    });

    Route::group([
        'middleware' => ['web', 'tenant.auth']
    ], function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // User management routes
        Route::get('/users', [UserController::class, 'index'])
            ->middleware(['web.active.tenant'])
            ->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])
            ->middleware(['web.active.tenant'])
            ->name('users.create');
        Route::post('/users', [UserController::class, 'store'])
            ->middleware(['web.active.tenant'])
            ->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])
            ->middleware(['web.active.tenant'])
            ->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])
            ->middleware(['web.active.tenant'])
            ->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])
            ->middleware(['web.active.tenant'])
            ->name('users.destroy');

        // Tenant management routes (admin only)
        Route::get('/tenants/settings', [TenantController::class, 'settings'])
            ->middleware(['main.admin.only'])
            ->name('tenant.settings');
        Route::put('/tenants/{id}/activate', [TenantController::class, 'activate'])
            ->middleware(['main.admin.only'])
            ->name('tenant.activate');
        Route::put('/tenants/{id}/deactivate', [TenantController::class, 'deactivate'])
            ->middleware(['main.admin.only'])
            ->name('tenant.deactivate');

        // Billing routes
        Route::get('/billing/plans', function () {
            return view('pages.tenants.plans');
        })
            ->middleware(['main.admin.only'])
            ->name('billing.plans');

        Route::get('/billing/checkout/{type?}', function (Request $request, $type = 'monthly') {
            $tenant = Tenant::find(session()->get('tenant_id'));
            if (!$tenant) {
                return redirect()->route('error.tenant-required');
            }

            $priceId = config('cashier.prices.monthly');

            $productId = config('cashier.products.default');

            if ($type === 'yearly') {
                $priceId = config('cashier.prices.yearly');
            }

            return $tenant
                ->newSubscription($productId, $priceId)
                ->checkout([
                    'success_url' => route('tenant.settings'),
                    'cancel_url' => route('tenant.settings'),
                ]);
        })
            ->middleware(['main.admin.only'])
            ->name('billing.checkout');
    });
}

