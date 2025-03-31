<?php

namespace Tests\Feature\Auth;

use App\Mail\WelcomeUserMail;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Company;
use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Utilities\DatabaseRefresh;
use Exception;

class RegisterTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseRefresh;

    /**
     * Test the registration page loads correctly.
     */
    public function test_registration_page_loads()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('pages.register');
    }

    /**
     * Test successful registration.
     */
    public function test_successful_registration()
    {
        Mail::fake();

        $formData = [
            "name" => Faker::create()->name(),
            "email" => Faker::create()->email(),
            "phone" => Faker::create()->phoneNumber(),
            "code" => Faker::create()->word(),
        ];

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/register', $formData);
        $response->assertSessionHas('success');
        $response->assertRedirect('/dashboard');

        // Assert that the tenant was created
        $tenant = Tenant::where('id', $formData['code'])->first();
        $this->assertNotNull($tenant);
        $this->assertEquals($formData['code'], $tenant->id);

        // Assert if tenant id is in the session
        $this->assertEquals($formData['code'], session('tenant_id'));

        // Initialize the tenant
        tenancy()->initialize($tenant);

        // Assert that the company was created
        $company = Company::where('email', $formData['email'])->first();
        $this->assertNotNull($company);
        $this->assertEquals($formData['email'], $company->email);
        $this->assertEquals($formData['name'], $company->name);
        $this->assertEquals($formData['phone'], $company->phone);
        $this->assertEquals($formData['code'], $company->code);

        // Assert that the user was created
        $user = User::where('email', $formData['email'])->first();
        $this->assertNotNull($user);
        $this->assertEquals($formData['email'], $user->email);
        $this->assertEquals($formData['name'], $user->name);

        // Assert that the user is authenticated
        $this->assertAuthenticatedAs($user);

        // Assert that the welcome email was sent
        Mail::assertSent(WelcomeUserMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /**
     * Test registration fails with duplicate company code.
     */
    public function test_registration_fails_with_duplicate_code()
    {
        Mail::fake();

        $this->refreshTenantDatabase();

        // Create a tenant first
        $existingCode = 'existing_company';
        Tenant::create(['id' => $existingCode]);

        $formData = [
            "name" => Faker::create()->name(),
            "email" => Faker::create()->email(),
            "phone" => Faker::create()->phoneNumber(),
            "code" => $existingCode, // Using existing code to trigger validation error
        ];

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/register', $formData);

        // Should redirect back with errors
        $response->assertRedirect();
        $response->assertSessionHasErrors('code');

        // Verify that no welcome email was sent
        Mail::assertNotSent(WelcomeUserMail::class);
    }

    /**
     * Test registration fails with invalid input.
     */
    public function test_registration_fails_with_invalid_input()
    {
        $formData = [
            "name" => "", // Empty name to trigger validation error
            "email" => "invalid-email", // Invalid email format
            "phone" => "",
            "code" => "",
        ];

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/register', $formData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email', 'phone', 'code']);
    }

    /**
     * Test registration completes when mail fails.
     */
    public function test_registration_completes_when_mail_fails()
    {
        // Make Mail facade throw an exception when used
        Mail::shouldReceive('to')->andThrow(new Exception('Mail server connection failed'));

        $formData = [
            "name" => Faker::create()->name(),
            "email" => Faker::create()->email(),
            "phone" => Faker::create()->phoneNumber(),
            "code" => Faker::create()->word(),
        ];

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/register', $formData);

        // Registration should still complete successfully despite mail error
        $response->assertSessionHas('success');
        $response->assertRedirect('/dashboard');

        // Assert that the tenant was created
        $tenant = Tenant::where('id', $formData['code'])->first();
        $this->assertNotNull($tenant);

        // Initialize the tenant
        tenancy()->initialize($tenant);

        // Assert that the user was created
        $user = User::where('email', $formData['email'])->first();
        $this->assertNotNull($user);

        // Assert that the user is authenticated
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test validation error messages are correct.
     */
    public function test_validation_error_messages()
    {
        $formData = [
            "name" => "",
            "email" => "invalid-email",
            "phone" => "",
            "code" => "",
        ];

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/register', $formData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email', 'phone', 'code']);

        // Check for specific error messages
        $response->assertSessionHasErrors([
            'name' => 'Company name is required',
            'email' => 'Please enter a valid email address',
            'phone' => 'Company phone number is required',
            'code' => 'Company code is required'
        ]);
    }
}
