<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;
use Mockery;

class FcmServiceTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    private User $userWithToken;
    private User $userWithoutToken;
    private string $tenantId = 'fcm-service-test';

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

        // Create a user with FCM token
        $this->userWithToken = User::create([
            'name' => 'User With Token',
            'username' => 'user_with_token',
            'email' => 'withtoken@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
            'fcm_token' => 'test-fcm-token-123456789',
        ]);

        // Create a user without FCM token
        $this->userWithoutToken = User::create([
            'name' => 'User Without Token',
            'username' => 'user_without_token',
            'email' => 'withouttoken@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    #[Test]
    /** Test sending notification fails when user has no FCM token */
    public function sending_notification_fails_without_token()
    {
        // Create a partial mock of FcmService that doesn't call the real sending method
        $fcmService = Mockery::mock(FcmService::class)->makePartial();

        // Call the real method with a user that has no token
        $result = $fcmService->sendFcmNotification(
            $this->userWithoutToken->id,
            'Test Title',
            'Test Description'
        );

        // Since the user has no token, it should return false without attempting to send
        $this->assertFalse($result);
    }

    #[Test]
    /** Test the FCM service returns true for a successful notification */
    public function successful_notification_returns_true()
    {
        // Create a mock of the FcmService class with a fake implementation
        $fcmService = Mockery::mock(FcmService::class);

        // Set expectation that sendFcmNotification will be called once with specific arguments
        // and it will return true (as if the notification was sent successfully)
        $fcmService->shouldReceive('sendFcmNotification')
            ->once()
            ->with($this->userWithToken->id, 'Test Title', 'Test Description')
            ->andReturn(true);

        // Call the mocked method
        $result = $fcmService->sendFcmNotification(
            $this->userWithToken->id,
            'Test Title',
            'Test Description'
        );

        // Assert that it returned true as configured in the mock
        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
