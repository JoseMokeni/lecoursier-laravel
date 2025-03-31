<?php

namespace Tests\Unit;

use App\Http\Controllers\ContactController;
use App\Mail\ContactFormMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    /**
     * Test successful contact form submission
     */
    public function test_contact_form_submits_successfully()
    {
        Mail::fake();

        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'subject' => 'Test Subject',
            'message' => 'This is a test message that meets the minimum length requirement',
            'privacy' => 'accepted',
        ];

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/contact', $formData);

        // Assert redirection with success message
        $response->assertSessionHas('success', 'Votre message a été envoyé. Nous vous répondrons dans les plus brefs délais.');
        $response->assertRedirect();

        // Assert mail was sent
        Mail::assertSent(ContactFormMail::class, function ($mail) use ($formData) {
            unset($formData['privacy']);
            return $mail->data == $formData &&
                   $mail->hasTo(config('mail.contact_address', 'contact@lecoursier.app'));
        });
    }

    /**
     * Test validation failure for missing required fields
     */
    public function test_contact_form_validates_required_fields()
    {
        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/contact', []);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message', 'privacy']);
        $response->assertStatus(302); // Redirects back with errors
    }

    /**
     * Test validation failure for invalid email
     */
    public function test_contact_form_validates_email_format()
    {
        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $formData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'phone' => '1234567890',
            'subject' => 'Test Subject',
            'message' => 'This is a test message that meets the minimum length requirement',
            'privacy' => 'accepted',
        ];

        $response = $this->post('/contact', $formData);

        $response->assertSessionHasErrors(['email']);
        $response->assertStatus(302); // Redirects back with errors
    }

    /**
     * Test validation failure for short message
     */
    public function test_contact_form_validates_message_length()
    {
        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'subject' => 'Test Subject',
            'message' => 'Too short',
            'privacy' => 'accepted',
        ];

        $response = $this->post('/contact', $formData);

        $response->assertSessionHasErrors(['message']);
        $response->assertStatus(302); // Redirects back with errors
    }

    /**
     * Test mail exception handling (if mail server is down)
     */
    public function test_contact_form_handles_mail_exceptions()
    {
        Mail::shouldReceive('to')
            ->once()
            ->andReturnSelf();

        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('Failed to send email'));

        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'subject' => 'Test Subject',
            'message' => 'This is a test message that meets the minimum length requirement',
            'privacy' => 'accepted',
        ];

        // Disable CSRF middleware for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/contact', $formData);

        // This would depend on your error handling. If you're not currently handling mail exceptions,
        // you might want to update the ContactController first before implementing this test.
        $response->assertStatus(500); // Or whatever status code you choose to return for mail failures
    }

    /**
     * Test validation failure for too short name
     */
    public function test_contact_form_validates_min_name_length()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $formData = [
            'name' => 'A', // Too short (minimum is 2 characters)
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'subject' => 'Test Subject',
            'message' => 'This is a test message that meets the minimum length requirement',
            'privacy' => 'accepted',
        ];

        $response = $this->post('/contact', $formData);

        $response->assertSessionHasErrors(['name']);
        $response->assertStatus(302);
    }

    /**
     * Test validation failure for too long name
     */
    public function test_contact_form_validates_max_name_length()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $formData = [
            'name' => str_repeat('A', 101), // Exceeds maximum length of 100
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'subject' => 'Test Subject',
            'message' => 'This is a test message that meets the minimum length requirement',
            'privacy' => 'accepted',
        ];

        $response = $this->post('/contact', $formData);

        $response->assertSessionHasErrors(['name']);
        $response->assertStatus(302);
    }

    /**
     * Test validation failure for too long email
     */
    public function test_contact_form_validates_max_email_length()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $formData = [
            'name' => 'John Doe',
            'email' => str_repeat('a', 90) . '@example.com', // Creates an email > 100 chars
            'phone' => '1234567890',
            'subject' => 'Test Subject',
            'message' => 'This is a test message that meets the minimum length requirement',
            'privacy' => 'accepted',
        ];

        $response = $this->post('/contact', $formData);

        $response->assertSessionHasErrors(['email']);
        $response->assertStatus(302);
    }

    /**
     * Test validation failure for too long phone number
     */
    public function test_contact_form_validates_max_phone_length()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => str_repeat('1', 21), // Exceeds maximum length of 20
            'subject' => 'Test Subject',
            'message' => 'This is a test message that meets the minimum length requirement',
            'privacy' => 'accepted',
        ];

        $response = $this->post('/contact', $formData);

        $response->assertSessionHasErrors(['phone']);
        $response->assertStatus(302);
    }

    /**
     * Test submission with optional phone field omitted
     */
    public function test_contact_form_allows_null_phone()
    {
        Mail::fake();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            // Phone field intentionally omitted
            'subject' => 'Test Subject',
            'message' => 'This is a test message that meets the minimum length requirement',
            'privacy' => 'accepted',
        ];

        $response = $this->post('/contact', $formData);

        // Assert redirection with success message
        $response->assertSessionHas('success');
        $response->assertRedirect();

        // Assert mail was sent
        Mail::assertSent(ContactFormMail::class);
    }

    /**
     * Test validation failure for too long message
     */
    public function test_contact_form_validates_max_message_length()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'subject' => 'Test Subject',
            'message' => str_repeat('A', 1001), // Exceeds maximum length of 1000
            'privacy' => 'accepted',
        ];

        $response = $this->post('/contact', $formData);

        $response->assertSessionHasErrors(['message']);
        $response->assertStatus(302);
    }
}
