<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Tests\Utilities\DatabaseRefresh;
use Laravel\Sanctum\Sanctum;

class ApiLoginTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseRefresh;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up the database and tenant context
        $this->refreshTenantDatabase();
    }

    /**
     * Test successful login through API.
     */
    public function test_successful_login()
    {
        // Create a tenant
        $tenantId = 'test-company-api';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create active user in tenant context
        $user = User::create([
            'name' => 'API User',
            'username' => 'apiuser',
            'email' => 'api@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // Test login with valid credentials
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->postJson('/api/login', [
            'username' => 'apiuser',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'username',
                    'name',
                    'email',
                    'role',
                ],
            ]);

        // Verify the user data is correct
        $response->assertJson([
            'user' => [
                'username' => 'apiuser',
                'name' => 'API User',
                'email' => 'api@example.com',
                'role' => 'user',
            ],
        ]);
    }

    /**
     * Test login fails with incorrect password.
     */
    public function test_login_fails_with_incorrect_password()
    {
        // Create a tenant
        $tenantId = 'test-company-api-wrong-pwd';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create user in tenant context
        User::create([
            'name' => 'API User',
            'username' => 'apiuser',
            'email' => 'api@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // Test login with incorrect password
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->postJson('/api/login', [
            'username' => 'apiuser',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid credentials',
            ]);
    }

    /**
     * Test login fails with non-existent username.
     */
    public function test_login_fails_with_nonexistent_username()
    {
        // Create a tenant
        $tenantId = 'test-company-api-wrong-user';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Test login with non-existent username
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->postJson('/api/login', [
            'username' => 'nonexistent',
            'password' => 'password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid credentials',
            ]);
    }

    /**
     * Test login fails for inactive user.
     */
    public function test_login_fails_for_inactive_user()
    {
        // Create a tenant
        $tenantId = 'test-company-api-inactive';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create inactive user in tenant context
        User::create([
            'name' => 'Inactive API User',
            'username' => 'inactiveuser',
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'inactive',
        ]);

        // Test login with inactive user credentials
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->postJson('/api/login', [
            'username' => 'inactiveuser',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'User is inactive',
            ]);
    }

    /**
     * Test login validation errors.
     */
    public function test_login_validation_errors()
    {
        // Create a tenant
        $tenantId = 'test-company-api-validation';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Test login with missing fields
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    /**
     * Test token revocation on login.
     */
    public function test_token_revocation_on_login()
    {
        // Create a tenant
        $tenantId = 'test-company-api-token';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create user in tenant context
        $user = User::create([
            'name' => 'Token User',
            'username' => 'tokenuser',
            'email' => 'token@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // Create an initial token for the user
        $initialToken = $user->createToken('initial_token')->plainTextToken;

        // Login again to revoke the previous token
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->postJson('/api/login', [
            'username' => 'tokenuser',
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        // Verify that the initial token no longer exists in the database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'initial_token',
        ]);
    }

    /**
     * Test tenant context middleware.
     */
    public function test_tenant_context_middleware()
    {
        // Test API endpoint without tenant ID
        $response = $this->postJson('/api/login', [
            'username' => 'apiuser',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Tenant ID is required as x-tenant-id header',
            ]);

        // Test API endpoint with invalid tenant ID
        $response = $this->withHeaders([
            'x-tenant-id' => 'non-existent-tenant',
        ])->postJson('/api/login', [
            'username' => 'apiuser',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Invalid tenant ID',
            ]);
    }

    /**
     * Test admin only middleware with regular user.
     */
    public function test_admin_only_middleware_regular_user()
    {
        // Create a tenant
        $tenantId = 'test-company-admin-middleware-regular';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create regular user in tenant context
        $regularUser = User::create([
            'name' => 'Regular User',
            'username' => 'regularuser',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // Create a protected route for testing admin middleware
        $this->app->router->group(['middleware' => ['api',  'api.tenant.context', 'api.auth', 'api.admin.only']], function ($router) {
            $router->get('/api/admin/test', function () {
                return response()->json(['success' => true]);
            });
        });

        // Log in the regular user and get a token
        $loginResponse = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->postJson('/api/login', [
            'username' => 'regularuser',
            'password' => 'password',
        ]);

        $regularUserToken = $loginResponse->json('token');

        // Test with regular user token
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
            'Authorization' => 'Bearer ' . $regularUserToken,
        ])->getJson('/api/admin/test');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied. Admin privileges required.',
            ]);
    }

    /**
     * Test admin only middleware with admin user.
     */
    public function test_admin_only_middleware_admin_user()
    {
        // Create a tenant
        $tenantId = 'test-company-admin-middleware-admin';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create regular user in tenant context
        $adminUser = User::create([
            'name' => 'Admin User',
            'username' => 'adminuser',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create a protected route for testing admin middleware
        $this->app->router->group(['middleware' => ['api',  'api.tenant.context', 'api.auth', 'api.admin.only']], function ($router) {
            $router->get('/api/admin/test', function () {
                return response()->json(['success' => true]);
            });
        });

        // Log in the regular user and get a token
        $loginResponse = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->postJson('/api/login', [
            'username' => 'adminuser',
            'password' => 'password',
        ]);

        $adminUserToken = $loginResponse->json('token');

        // Test with regular user token
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
            'Authorization' => 'Bearer ' . $adminUserToken,
        ])->getJson('/api/admin/test');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test unauthenticated access to protected routes.
     */
    public function test_unauthenticated_access()
    {
        // Create a tenant
        $tenantId = 'test-company-unauthenticated';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Create a protected route for testing auth middleware
        $this->app->router->group(['middleware' => ['api', 'api.auth', 'api.tenant.context']], function ($router) {
            $router->get('/api/protected', function () {
                return response()->json(['success' => true]);
            });
        });

        // Test accessing protected route without authentication
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->getJson('/api/protected');

        $response->assertStatus(401);
    }

    /**
     * Test auth middleware with expired token.
     */
    public function test_auth_middleware_with_expired_token()
    {
        // Create a tenant
        $tenantId = 'test-company-expired-token';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create user in tenant context
        $user = User::create([
            'name' => 'Expired Token User',
            'username' => 'expireduser',
            'email' => 'expired@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // Create a protected route for testing auth middleware
        $this->app->router->group(['middleware' => ['api', 'api.tenant.context', 'api.auth']], function ($router) {
            $router->get('/api/user/protected', function () {
                return response()->json(['success' => true]);
            });
        });

        // Create an invalid/random token
        $invalidToken = 'invalid_token_string';

        // Test with invalid token
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
            'Authorization' => 'Bearer ' . $invalidToken,
        ])->getJson('/api/user/protected');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized. Invalid token.',
            ]);
    }

    /**
     * Test auth middleware with inactive user token.
     */
    public function test_auth_middleware_with_inactive_user_token()
    {
        // Create a tenant
        $tenantId = 'test-company-inactivated-user';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create active user in tenant context
        $user = User::create([
            'name' => 'Initially Active User',
            'username' => 'initiallyactive',
            'email' => 'initiallyactive@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // Create a protected route for testing auth middleware
        $this->app->router->group(['middleware' => ['api', 'api.tenant.context', 'api.auth']], function ($router) {
            $router->get('/api/user/status-check', function () {
                return response()->json(['success' => true]);
            });
        });

        // Log in the active user and get a token
        $loginResponse = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->postJson('/api/login', [
            'username' => 'initiallyactive',
            'password' => 'password',
        ]);

        $userToken = $loginResponse->json('token');

        // Mark user as inactive
        $user->status = 'inactive';
        $user->save();

        // Test with token from now-inactive user
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
            'Authorization' => 'Bearer ' . $userToken,
        ])->getJson('/api/user/status-check');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Unauthorized. User is inactive.',
            ]);
    }

    /**
     * Test admin middleware with super-admin role.
     */
    public function test_admin_only_middleware_super_admin_user()
    {
        // Create a tenant
        $tenantId = 'test-company-admin-middleware-super-admin';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create super-admin user in tenant context
        $superAdminUser = User::create([
            'name' => 'Super Admin User',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'role' => 'super-admin', // Different admin role name to test
            'status' => 'active',
        ]);

        // Create a protected route for testing admin middleware
        $this->app->router->group(['middleware' => ['api', 'api.tenant.context', 'api.auth', 'api.admin.only']], function ($router) {
            $router->get('/api/admin/super-test', function () {
                return response()->json(['success' => true]);
            });
        });

        // Log in the super-admin user and get a token
        $loginResponse = $this->withHeaders([
            'x-tenant-id' => $tenantId,
        ])->postJson('/api/login', [
            'username' => 'superadmin',
            'password' => 'password',
        ]);

        $superAdminToken = $loginResponse->json('token');

        // Test with super-admin token (should fail because role is not exactly 'admin')
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId,
            'Authorization' => 'Bearer ' . $superAdminToken,
        ])->getJson('/api/admin/super-test');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied. Admin privileges required.',
            ]);
    }

    /**
     * Test middleware roles when tenant context changes.
     */
    public function test_middleware_across_different_tenants()
    {
        // Create tenant 1
        $tenantId1 = 'test-company-tenant1';
        $tenant1 = Tenant::create(['id' => $tenantId1]);

        // Create tenant 2
        $tenantId2 = 'test-company-tenant2';
        $tenant2 = Tenant::create(['id' => $tenantId2]);

        // Initialize tenant 1 context
        tenancy()->initialize($tenant1);

        // Create admin user in tenant 1
        $adminUser = User::create([
            'name' => 'Cross-Tenant Admin',
            'username' => 'crossadmin',
            'email' => 'crossadmin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create a protected route for testing admin middleware
        $this->app->router->group(['middleware' => ['api', 'api.tenant.context', 'api.auth', 'api.admin.only']], function ($router) {
            $router->get('/api/admin/cross-tenant', function () {
                return response()->json(['success' => true]);
            });
        });

        // Log in the admin user in tenant 1 and get a token
        $loginResponse = $this->withHeaders([
            'x-tenant-id' => $tenantId1,
        ])->postJson('/api/login', [
            'username' => 'crossadmin',
            'password' => 'password',
        ]);

        $adminToken = $loginResponse->json('token');

        // Try to access admin route in tenant 2 with admin token from tenant 1
        $response = $this->withHeaders([
            'x-tenant-id' => $tenantId2,
            'Authorization' => 'Bearer ' . $adminToken,
        ])->getJson('/api/admin/cross-tenant');

        // Should fail because the token is from a different tenant
        $response->assertStatus(401);
    }
}
