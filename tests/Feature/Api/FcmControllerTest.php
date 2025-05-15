<?php

namespace Tests\Feature\Api;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class FcmControllerTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    private string $userToken;
    private User $user;
    private string $tenantId = 'fcm-test';

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

        // Create a user
        $this->user = User::create([
            'name' => 'FCM Test User',
            'username' => 'fcm_test_user',
            'email' => 'fcm@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // Get token by post to login endpoint
        $this->userToken = $this->withHeaders([
            'x-tenant-id' => $this->tenantId,
        ])->postJson('/api/login', [
            'username' => $this->user->username,
            'password' => 'password',
        ])->json('token');
    }

    #[Test]
    /** Test updating FCM device token */
    public function update_device_token()
    {
        $token = 'test-fcm-token-123456789';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'x-tenant-id'   => $this->tenantId,
        ])->putJson('/api/update-device-token', [
            'token' => $token
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Device token updated successfully',
            ]);

        // Verify that the token was actually saved
        $this->user->refresh();
        $this->assertEquals($token, $this->user->fcm_token);
    }

    #[Test]
    /** Test validation for updating FCM device token */
    public function update_device_token_validation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'x-tenant-id'   => $this->tenantId,
        ])->putJson('/api/update-device-token', [
            // Missing token
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    #[Test]
    /** Test that unauthenticated users cannot update FCM token */
    public function unauthenticated_users_cannot_update_token()
    {
        $response = $this->withHeaders([
            'x-tenant-id'   => $this->tenantId,
        ])->putJson('/api/update-device-token', [
            'token' => 'test-token'
        ]);

        $response->assertStatus(401);
    }
}
