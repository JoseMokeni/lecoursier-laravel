<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\TenantController;
use App\Http\Controllers\Web\StatisticsController;
use App\Http\Controllers\ErrorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;

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

        // Password change routes
        Route::get('/change-password', [UserController::class, 'changePassword'])->name('password.change');
        Route::post('/change-password', [UserController::class, 'updatePassword'])->name('password.update');

        // Error routes (moved outside middleware groups for direct access)
        Route::get('/errors/tenant-inactive', [ErrorController::class, 'tenantInactive'])->name('error.tenant-inactive');
        Route::get('/errors/tenant-required', [ErrorController::class, 'tenantRequired'])->name('error.tenant-required');
    });

    Route::group([
        'middleware' => ['web', 'tenant.auth']
    ], function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware(['web.tenant.subscribed'])
            ->name('dashboard');

        // Statistics routes
        Route::get('/statistics', [StatisticsController::class, 'index'])
            ->middleware(['web.active.tenant', 'web.tenant.subscribed'])
            ->name('statistics.index');
        Route::get('/statistics/couriers', [StatisticsController::class, 'couriers'])
            ->middleware(['web.active.tenant', 'web.tenant.subscribed'])
            ->name('statistics.couriers');

        // User management routes
        Route::get('/users', [UserController::class, 'index'])
            ->middleware(['web.active.tenant', 'web.tenant.subscribed'])
            ->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])
            ->middleware(['web.active.tenant', 'web.tenant.subscribed'])
            ->name('users.create');
        Route::post('/users', [UserController::class, 'store'])
            ->middleware(['web.active.tenant', 'web.tenant.subscribed'])
            ->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])
            ->middleware(['web.active.tenant', 'web.tenant.subscribed'])
            ->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])
            ->middleware(['web.active.tenant', 'web.tenant.subscribed'])
            ->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])
            ->middleware(['web.active.tenant', 'web.tenant.subscribed'])
            ->name('users.destroy');

        // Tenant management routes (admin only)
        Route::get('/tenants/settings', [TenantController::class, 'settings'])
            ->middleware(['main.admin.only', 'web.tenant.subscribed'])
            ->name('tenant.settings');

        Route::put('/tenants/{id}/activate', [TenantController::class, 'activate'])
            ->middleware(['main.admin.only', 'web.tenant.subscribed'])
            ->name('tenant.activate');
        Route::put('/tenants/{id}/deactivate', [TenantController::class, 'deactivate'])
            ->middleware(['main.admin.only', 'web.tenant.subscribed'])
            ->name('tenant.deactivate');

        // Billing routes
        Route::get('/billing', function () {
            return view('pages.billing.index');
        })->middleware(['main.admin.only'])->name('billing');

        Route::get('/billing/plans', function () {
            // get monthly and yearly prices using price ids
            $stripe = Cashier::stripe();
            $prices = $stripe->prices->all([
                'product' => config('cashier.products.default'),
                'active' => true,
                'expand' => ['data.product'],
            ]);
            $monthlyPrice = null;
            $yearlyPrice = null;

            foreach ($prices->data as $price) {
                Log::info('Price: ', [$price]);
                if ($price->id === config('cashier.prices.monthly')) {
                    $monthlyPrice = $price->unit_amount / 100;
                } elseif ($price->id === config('cashier.prices.yearly')) {
                    $yearlyPrice = $price->unit_amount / 100;
                }
            }

            $yearlyDiscount = $monthlyPrice * 12 - $yearlyPrice;
            return view('pages.tenants.plans', [
                'monthlyPrice' => $monthlyPrice,
                'yearlyPrice' => $yearlyPrice,
                'yearlyDiscount' => $yearlyDiscount,
            ]);
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
                    'success_url' => route('dashboard'),
                    'cancel_url' => route('billing'),
                ]);
        })
            ->middleware(['main.admin.only'])
            ->name('billing.checkout');

        Route::get('/billing/portal', function (Request $request) {
            return tenancy()->tenant->redirectToBillingPortal(route('billing'));
        })->middleware(['main.admin.only'])->name('billing.portal');

        // Task history view
        Route::get('/tasks/history', [\App\Http\Controllers\Web\TaskController::class, 'history'])
            ->middleware(['web.active.tenant', 'web.tenant.subscribed'])
            ->name('tasks.history');
    });
}
