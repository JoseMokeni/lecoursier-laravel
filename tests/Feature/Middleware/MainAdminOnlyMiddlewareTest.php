<?php

namespace Tests\Feature\Middleware;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class MainAdminOnlyMiddlewareTest extends TestCase
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
    }

    /**
     * Test middleware allows access to main admin.
     */
    public function test_middleware_allows_main_admin_access()
    {
        // Create a tenant
        $tenantId = 'test-tenant-main-admin';
        $tenant = Tenant::create([
            'id' => $tenantId,
            'status' => 'active'
        ]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create admin user in tenant context with username matching tenant ID
        $mainAdmin = User::create([
            'name' => 'Main Admin',
            'username' => $tenantId, // Important: username matches tenant ID
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

        // Make request to a route protected by MainAdminOnlyMiddleware
        $response = $this->get('/tenants/settings');

        // The middleware should allow access
        $this->assertNotTrue($response->isRedirect('/dashboard'));
    }

    /**
     * Test middleware redirects non-main admin.
     */
    public function test_middleware_redirects_non_main_admin()
    {
        // Create a tenant
        $tenantId = 'test-tenant-regular-admin';
        $tenant = Tenant::create([
            'id' => $tenantId,
            'status' => 'active'
        ]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create admin user in tenant context with username NOT matching tenant ID
        $regularAdmin = User::create([
            'name' => 'Regular Admin',
            'username' => 'regular-admin', // Doesn't match tenant ID
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

        // Make request to a route protected by MainAdminOnlyMiddleware
        $response = $this->get('/tenants/settings');

        // Should redirect to dashboard with error message
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error', 'Seuls l\'administrateur principal a accès à cette ressource.');
    }
}
