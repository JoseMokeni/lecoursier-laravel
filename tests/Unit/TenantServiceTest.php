<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class TenantServiceTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    private TenantService $tenantService;
    private Tenant $tenant;
    private string $tenantId = 'tenant-service-test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshTenantDatabase();

        // Create a tenant
        $this->tenant = Tenant::create([
            'id' => $this->tenantId,
            'name' => 'Test Tenant',
            'status' => 'inactive', // Start with inactive tenant
        ]);

        $this->tenantService = new TenantService();
    }

    #[Test]
    /** Test getting a tenant by ID */
    public function get_tenant_by_id()
    {
        $tenant = $this->tenantService->getTenant($this->tenantId);

        $this->assertNotNull($tenant);
        $this->assertEquals($this->tenantId, $tenant->id);
        $this->assertEquals('Test Tenant', $tenant->name);
    }

    #[Test]
    /** Test getting a non-existent tenant */
    public function get_nonexistent_tenant()
    {
        $tenant = $this->tenantService->getTenant('nonexistent-tenant');

        $this->assertNull($tenant);
    }

    #[Test]
    /** Test activating a tenant */
    public function activate_tenant()
    {
        // Assert tenant starts inactive
        $this->assertEquals('inactive', $this->tenant->status);

        // Activate the tenant
        $activatedTenant = $this->tenantService->activateTenant($this->tenantId);

        // Assert tenant was activated
        $this->assertNotNull($activatedTenant);
        $this->assertEquals('active', $activatedTenant->status);

        // Reload from database to verify persistence
        $this->tenant->refresh();
        $this->assertEquals('active', $this->tenant->status);
    }

    #[Test]
    /** Test activating a non-existent tenant */
    public function activate_nonexistent_tenant()
    {
        $activatedTenant = $this->tenantService->activateTenant('nonexistent-tenant');

        $this->assertNull($activatedTenant);
    }

    #[Test]
    /** Test deactivating a tenant */
    public function deactivate_tenant()
    {
        // First activate the tenant
        $this->tenant->status = 'active';
        $this->tenant->save();

        // Assert tenant starts active
        $this->assertEquals('active', $this->tenant->status);

        // Deactivate the tenant
        $deactivatedTenant = $this->tenantService->deactivateTenant($this->tenantId);

        // Assert tenant was deactivated
        $this->assertNotNull($deactivatedTenant);
        $this->assertEquals('inactive', $deactivatedTenant->status);

        // Reload from database to verify persistence
        $this->tenant->refresh();
        $this->assertEquals('inactive', $this->tenant->status);
    }

    #[Test]
    /** Test deactivating a non-existent tenant */
    public function deactivate_nonexistent_tenant()
    {
        $deactivatedTenant = $this->tenantService->deactivateTenant('nonexistent-tenant');

        $this->assertNull($deactivatedTenant);
    }
}
