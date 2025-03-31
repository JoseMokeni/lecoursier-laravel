<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Utilities\DatabaseRefresh;
use Illuminate\Support\Facades\Auth;

class LoginTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseRefresh;

    /**
     * Test the login page loads correctly.
     */
    public function test_login_page_loads()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('pages.login');
    }

    /**
     * Test successful login for admin user.
     */
    public function test_successful_login_for_admin()
    {
        // Create a tenant
        $tenantId = 'test-company';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create admin user in tenant context
        $user = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $loginData = [
            'company_code' => $tenantId,
            'username' => 'admin',
            'password' => 'password',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertEquals($tenantId, session('tenant_id'));
    }

    /**
     * Test login fails with invalid company code.
     */
    public function test_login_fails_with_invalid_company_code()
    {
        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $loginData = [
            'company_code' => 'non-existent-company',
            'username' => 'admin',
            'password' => 'password',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('company_code');
        $this->assertGuest();
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials()
    {
        // Create a tenant
        $tenantId = 'test-company-invalid';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create user in tenant context
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $loginData = [
            'company_code' => $tenantId,
            'username' => 'admin',
            'password' => 'wrong-password', // Wrong password
        ];

        $response = $this->post('/login', $loginData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * Test login fails with nonexistent username.
     */
    public function test_login_fails_with_nonexistent_username()
    {
        // Create a tenant
        $tenantId = 'test-company-nonexistent';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create user in tenant context
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $loginData = [
            'company_code' => $tenantId,
            'username' => 'nonexistent_user', // Username that doesn't exist
            'password' => 'password',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * Test non-admin users cannot login.
     */
    public function test_non_admin_users_cannot_login()
    {
        // Create a tenant
        $tenantId = 'test-company-non-admin';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create non-admin user in tenant context
        User::create([
            'name' => 'Regular User',
            'username' => 'regular',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'role' => 'user', // Non-admin role
            'status' => 'active',
        ]);

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $loginData = [
            'company_code' => $tenantId,
            'username' => 'regular',
            'password' => 'password',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    /**
     * Test inactive users cannot login.
     */
    public function test_inactive_users_cannot_login()
    {
        // Create a tenant
        $tenantId = 'test-company-inactive';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create inactive admin user in tenant context
        User::create([
            'name' => 'Inactive Admin',
            'username' => 'inactive',
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'inactive', // Inactive status
        ]);

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $loginData = [
            'company_code' => $tenantId,
            'username' => 'inactive',
            'password' => 'password',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('status');
        $this->assertGuest();
    }

    /**
     * Test login validation errors.
     */
    public function test_login_validation_errors()
    {
        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $loginData = [
            'company_code' => '',
            'username' => '',
            'password' => '',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['company_code', 'username', 'password']);
    }

    /**
     * Test authenticated user is redirected from login page.
     */
    public function test_authenticated_user_redirected_from_login_page()
    {
        // Create a tenant
        $tenantId = 'test-company-redirect';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Initialize tenant context
        tenancy()->initialize($tenant);

        // Create admin user in tenant context
        $user = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Store tenant ID in session
        session(['tenant_id' => $tenantId]);
        session()->save();

        // Login the user
        $this->actingAs($user);

        // Try to access login page while authenticated
        $response = $this->get('/login');

        $response->assertRedirect('/dashboard');
    }

    /**
     * Test login page with invalid tenant ID in session.
     */
    public function test_login_page_with_invalid_tenant_id_in_session()
    {
        // Set an invalid tenant ID in session
        session(['tenant_id' => 'non-existent-tenant']);
        session()->save();

        // Access login page with invalid tenant ID in session
        $response = $this->get('/login');

        // Should render login page and clear invalid tenant ID from session
        $response->assertStatus(200);
        $response->assertViewIs('pages.login');
        $this->assertNull(session('tenant_id'));
    }

    /**
     * Test login validation error messages are correct.
     */
    public function test_login_validation_error_messages()
    {
        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $loginData = [
            'company_code' => '',
            'username' => '',
            'password' => '',
        ];

        $response = $this->post('/login', $loginData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['company_code', 'username', 'password']);

        // Get the session errors
        $errors = session('errors');
        $this->assertEquals('Le code d\'entreprise est obligatoire', $errors->first('company_code'));
        $this->assertEquals('Le nom d\'utilisateur est obligatoire', $errors->first('username'));
        $this->assertEquals('Le mot de passe est obligatoire', $errors->first('password'));
    }

    /**
     * Test login with tenant ID in session but not logged in.
     */
    public function test_login_with_tenant_id_in_session_but_not_logged_in()
    {
        // Create a tenant
        $tenantId = 'test-company-session';
        $tenant = Tenant::create(['id' => $tenantId]);

        // Store tenant ID in session but don't login
        session(['tenant_id' => $tenantId]);
        session()->save();

        // Access login page
        $response = $this->get('/login');

        // Should show login page since not authenticated
        $response->assertStatus(200);
        $response->assertViewIs('pages.login');

        // Tenant ID should still be in session
        $this->assertEquals($tenantId, session('tenant_id'));
    }
}
