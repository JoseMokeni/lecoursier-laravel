<?php

namespace Tests\Feature\Web;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;
use Illuminate\Support\Facades\Artisan;

class TenantControllerTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseRefresh;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTenantDatabase();

        // Disable CSRF middleware for these tests
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    /**
     * Mock the TenantService to avoid database issues
     */
    protected function mockTenantService()
    {
        $mock = $this->mock('App\Services\TenantService');
        $this->app->instance('App\Services\TenantService', $mock);
        return $mock;
    }

    /**
     * Test tenant settings page loads correctly.
     */
    public function test_tenant_settings_page_loads()
    {
        // Create a tenant
        $tenantId = 'test-tenant-settings';
        $tenant = Tenant::create([
            'id' => $tenantId,
            'status' => 'active'
        ]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create main admin user in tenant context
        $mainAdmin = User::create([
            'name' => 'Main Admin',
            'username' => $tenantId, // Username matches tenant ID
            'email' => 'main-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Set tenant ID in session
        session(['tenant_id' => $tenantId]);
        session()->save();

        // Login the main admin
        $this->actingAs($mainAdmin);

        // Create a stub for the tenancy() helper
        $this->app->singleton('tenancy.tenant', function () use ($tenant) {
            return $tenant;
        });

        // Make request to the tenant settings page
        $response = $this->get('/tenants/settings');

        $response->assertStatus(200);
    }

    /**
     * Test tenant activation.
     */
    public function test_tenant_activation()
    {
        // Create an inactive tenant
        $tenantId = 'test-tenant-activation';
        $tenant = Tenant::create([
            'id' => $tenantId,
            'status' => 'inactive'
        ]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create main admin user in tenant context
        $mainAdmin = User::create([
            'name' => 'Main Admin',
            'username' => $tenantId, // Username matches tenant ID
            'email' => 'main-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Set tenant ID in session
        session(['tenant_id' => $tenantId]);
        session()->save();

        // Login the main admin
        $this->actingAs($mainAdmin);

        // Mock TenantService to return expected results
        $mockService = $this->mockTenantService();
        $mockService->shouldReceive('activateTenant')
            ->once()
            ->with($tenantId)
            ->andReturn($tenant);

        // Make request to activate the tenant
        $response = $this->put("/tenants/{$tenantId}/activate");

        $response->assertRedirect(route('tenant.settings'))
            ->assertSessionHas('success', 'Locataire activé avec succès.');
    }

    /**
     * Test tenant deactivation.
     */
    public function test_tenant_deactivation()
    {
        // Create an active tenant
        $tenantId = 'test-tenant-deactivation';
        $tenant = Tenant::create([
            'id' => $tenantId,
            'status' => 'active'
        ]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create main admin user in tenant context
        $mainAdmin = User::create([
            'name' => 'Main Admin',
            'username' => $tenantId, // Username matches tenant ID
            'email' => 'main-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Set tenant ID in session
        session(['tenant_id' => $tenantId]);
        session()->save();

        // Login the main admin
        $this->actingAs($mainAdmin);

        // Mock TenantService to return expected results
        $mockService = $this->mockTenantService();
        $mockService->shouldReceive('deactivateTenant')
            ->once()
            ->with($tenantId)
            ->andReturn($tenant);

        // Make request to deactivate the tenant
        $response = $this->put("/tenants/{$tenantId}/deactivate");

        $response->assertRedirect(route('tenant.settings'))
            ->assertSessionHas('success', 'Locataire désactivé avec succès.');
    }

    /**
     * Test tenant settings page is not accessible to non-main admin.
     */
    public function test_tenant_settings_not_accessible_to_regular_admin()
    {
        // Create a tenant
        $tenantId = 'test-tenant-access';
        $tenant = Tenant::create([
            'id' => $tenantId,
            'status' => 'active'
        ]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create regular admin user in tenant context
        $regularAdmin = User::create([
            'name' => 'Regular Admin',
            'username' => 'regular-admin', // Username doesn't match tenant ID
            'email' => 'regular-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Set tenant ID in session
        session(['tenant_id' => $tenantId]);
        session()->save();

        // Login the regular admin
        $this->actingAs($regularAdmin);

        // Make request to the tenant settings page
        $response = $this->get('/tenants/settings');

        // Should be redirected due to MainAdminOnlyMiddleware
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error', 'Seuls l\'administrateur principal a accès à cette ressource.');
    }
}
