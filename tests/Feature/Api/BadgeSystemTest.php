<?php

namespace Tests\Feature\Api;

use App\Events\TaskCompleted;
use App\Models\Badge;
use App\Models\Milestone;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStats;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class BadgeSystemTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    private string $userToken;
    private User $user;
    private Task $task;
    private Milestone $milestone;
    private Badge $badge;
    private string $tenantId = 'badge-test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshTenantDatabase();

        // Create a tenant
        $tenant = Tenant::create([
            'id' => $this->tenantId,
            'name' => 'Badge Test Tenant',
        ]);

        tenancy()->initialize($tenant);

        // Create a test user
        $this->user = User::create([
            'name' => 'Badge Test User',
            'username' => 'badge_test_user',
            'email' => 'badge@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // Get user token
        $this->userToken = $this->withHeaders([
            'x-tenant-id' => $this->tenantId,
        ])->postJson('/api/login', [
            'username' => $this->user->username,
            'password' => 'password',
        ])->json('token');

        // Create a milestone
        $this->milestone = Milestone::create([
            'name' => 'Test Milestone',
            'longitudinal' => '1.234567',
            'latitudinal' => '2.345678',
        ]);

        // Create a task
        $this->task = Task::create([
            'name' => 'Badge Test Task',
            'description' => 'Test Task for Badge System',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'user_id' => $this->user->id,
            'milestone_id' => $this->milestone->id,
        ]);

        // Create a test badge
        $this->badge = Badge::create([
            'name' => 'First Task',
            'description' => 'Complete your first task',
            'icon' => '🎯',
            'category' => 'task_completion',
            'criteria' => [
                'tasks_completed' => 1,
            ],
            'points' => 10,
            'rarity' => 'bronze',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function task_completion_fires_event_and_creates_stats()
    {
        // Don't fake events - we want to test the real workflow

        // Complete the task via API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'x-tenant-id' => $this->tenantId,
        ])->postJson("/api/tasks/{$this->task->id}/complete");

        $response->assertStatus(200);

        // Refresh the task to get updated status
        $this->task->refresh();
        $this->assertEquals('completed', $this->task->status);

        // Assert user stats were created by the event listener
        $this->user->refresh();
        $this->assertNotNull($this->user->stats);
        $this->assertEquals(1, $this->user->stats->total_tasks_completed);
    }

    #[Test]
    public function user_stats_are_created_when_completing_task()
    {
        // Ensure user has no stats initially
        $this->assertNull($this->user->stats);

        // Complete the task (this will trigger the event and listener)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'x-tenant-id' => $this->tenantId,
        ])->postJson("/api/tasks/{$this->task->id}/complete");

        $response->assertStatus(200);

        // Refresh task and user to get updated relationships
        $this->task->refresh();
        $this->user->refresh();

        // Assert task was completed
        $this->assertEquals('completed', $this->task->status);

        // Assert user stats were created by the event listener
        $this->assertNotNull($this->user->stats);
        $this->assertEquals(1, $this->user->stats->total_tasks_completed);
        $this->assertEquals(25, $this->user->stats->total_points); // 15 points from task completion + 10 points from badge
    }

    #[Test]
    public function badge_is_awarded_when_criteria_met()
    {
        // Ensure user has no badges initially
        $this->assertEquals(0, $this->user->badges()->count());

        // Complete the task (this will trigger the event and badge awarding)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'x-tenant-id' => $this->tenantId,
        ])->postJson("/api/tasks/{$this->task->id}/complete");

        $response->assertStatus(200);

        // Refresh user to get updated relationships
        $this->user->refresh();

        // Assert badge was awarded by the event listener
        $this->assertEquals(1, $this->user->badges()->count());
        $this->assertTrue($this->user->badges()->where('badge_id', $this->badge->id)->exists());
    }

    #[Test]
    public function can_get_user_badges_via_api()
    {
        // Award the badge manually for testing
        UserBadge::create([
            'user_id' => $this->user->id,
            'badge_id' => $this->badge->id,
            'earned_at' => now(),
            'progress' => ['tasks_completed' => 1],
        ]);

        // Get badges via API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'x-tenant-id' => $this->tenantId,
        ])->getJson('/api/badges/earned');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'badge' => [
                        'id',
                        'name',
                        'description',
                        'icon',
                        'category',
                        'points',
                        'rarity',
                    ],
                    'earned_at',
                    'progress',
                ]
            ],
            'meta' => [
                'total_earned',
            ]
        ]);

        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals($this->badge->name, $response->json('data.0.badge.name'));
    }

    #[Test]
    public function can_get_user_stats_via_api()
    {
        // Create user stats
        UserStats::create([
            'user_id' => $this->user->id,
            'level' => 2,
            'experience_points' => 1500,
            'total_tasks_completed' => 5,
            'total_points' => 50,
            'current_streak' => 3,
            'longest_streak' => 5,
        ]);

        // Get stats via API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'x-tenant-id' => $this->tenantId,
        ])->getJson('/api/user/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'userId',
                'level',
                'experiencePoints',
                'experienceToNextLevel',
                'progressToNextLevel',
                'totalTasksCompleted',
                'totalPoints',
                'currentStreak',
                'longestStreak',
                'badgesCount',
                'completionRate',
                'performanceScore',
            ]
        ]);

        $this->assertEquals(2, $response->json('data.level'));
        $this->assertEquals(5, $response->json('data.totalTasksCompleted'));
        $this->assertEquals(50, $response->json('data.totalPoints'));
    }

    #[Test]
    public function can_get_all_badges_with_progress()
    {
        // Get all badges
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'x-tenant-id' => $this->tenantId,
        ])->getJson('/api/badges');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'badge' => [
                        'id',
                        'name',
                        'description',
                        'icon',
                        'category',
                        'points',
                        'rarity',
                    ],
                    'earned',
                    'progress',
                ]
            ],
            'meta' => [
                'total_badges',
                'earned_badges',
            ]
        ]);

        $this->assertEquals(1, $response->json('meta.total_badges'));
        $this->assertEquals(0, $response->json('meta.earned_badges'));
        $this->assertFalse($response->json('data.0.earned'));
    }

    #[Test]
    public function can_get_leaderboard()
    {
        // Create another user with stats for leaderboard
        $anotherUser = User::create([
            'name' => 'Another User',
            'username' => 'another_user',
            'email' => 'another@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        UserStats::create([
            'user_id' => $this->user->id,
            'total_points' => 100,
            'level' => 3,
        ]);

        UserStats::create([
            'user_id' => $anotherUser->id,
            'total_points' => 150,
            'level' => 4,
        ]);

        // Get leaderboard
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'x-tenant-id' => $this->tenantId,
        ])->getJson('/api/leaderboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'rank',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'points',
                    'level',
                    'badges_count',
                ]
            ],
            'meta' => [
                'period',
                'limit',
            ]
        ]);

        // Check that users are ordered by points (descending)
        $this->assertEquals(1, $response->json('data.0.rank'));
        $this->assertEquals(150, $response->json('data.0.points'));
        $this->assertEquals($anotherUser->name, $response->json('data.0.user.name'));
    }
}
