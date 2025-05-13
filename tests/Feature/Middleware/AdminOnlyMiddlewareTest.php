<?php

namespace Tests\Feature\Middleware;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class AdminOnlyMiddlewareTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    private User $adminUser;
    private User $regularUser;
    private string $tenantId = 'admin-middleware-test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshTenantDatabase();

        // Create a tenant
        $tenant = Tenant::create([
            'id' => $this->tenantId,
            'name' => 'Test Tenant',
        ]);

        tenancy()->initialize($tenant);

        // Create admin user
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'username' => 'admin_user',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create regular user
        $this->regularUser = User::create([
            'name' => 'Regular User',
            'username' => 'regular_user',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    #[Test]
    /** Test that admin users can access admin-only routes */
    public function admin_can_access_admin_routes()
    {
        // Create a protected route for testing admin middleware
        $this->app->router->group(['middleware' => ['web', 'admin.only']], function ($router) {
            $router->get('/admin/test', function () {
                return response('Admin route accessed');
            });
        });

        $response = $this->actingAs($this->adminUser)
            ->withSession(['tenant_id' => $this->tenantId])
            ->get('/admin/test');

        $response->assertStatus(200)
            ->assertSee('Admin route accessed');
    }

    #[Test]
    /** Test that regular users are redirected from admin-only routes */
    public function regular_users_cannot_access_admin_routes()
    {
        // Create a protected route for testing admin middleware
        $this->app->router->group(['middleware' => ['web', 'admin.only']], function ($router) {
            $router->get('/admin/test', function () {
                return response('Admin route accessed');
            });
        });

        $response = $this->actingAs($this->regularUser)
            ->withSession(['tenant_id' => $this->tenantId])
            ->get('/admin/test');

        $response->assertRedirect('/login')
            ->assertSessionHas('error', 'Seuls les administrateurs ont accès à cette ressource.');
    }

    #[Test]
    /** Test that unauthenticated users can access admin-only routes */
    public function unauthenticated_users_can_access_admin_routes()
    {
        // Create a protected route for testing admin middleware
        $this->app->router->group(['middleware' => ['web', 'admin.only']], function ($router) {
            $router->get('/admin/test', function () {
                return response('Admin route accessed');
            });
        });

        $response = $this->get('/admin/test');

        // The AdminOnlyMiddleware doesn't block unauthenticated users, only non-admin authenticated users
        $response->assertStatus(200)
            ->assertSee('Admin route accessed');
    }
}
