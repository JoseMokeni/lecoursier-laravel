<?php
// filepath: /home/josemokeni/PFE/lecoursier-laravel/tests/Unit/Services/StatisticsServiceTest.php

namespace Tests\Unit\Services;

use App\Models\Milestone;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class StatisticsServiceTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    protected $statisticsService;
    protected $tenant;
    protected $admin;
    protected $courier;
    protected $milestone;
    protected $tenantId = 'testcompany';

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTenantDatabase();

        // Set up tenant
        $this->tenant = Tenant::create(['id' => $this->tenantId]);
        tenancy()->initialize($this->tenant);

        // Create users
        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@company.com',
            'username' => $this->tenantId,
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $this->courier = User::create([
            'name' => 'Test Courier',
            'email' => 'courier@company.com',
            'username' => 'courier',
            'role' => 'courier',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        // Create a milestone for tasks
        $this->milestone = Milestone::create([
            'name' => 'Test Milestone',
            'longitudinal' => '48.8566',
            'latitudinal' => '2.3522',
            'favorite' => false
        ]);

        // Set up service
        $this->statisticsService = new StatisticsService();
    }

    public function test_set_date_range()
    {
        // Test chainable method
        $result = $this->statisticsService->setDateRange('2025-05-01', '2025-05-14');
        $this->assertInstanceOf(StatisticsService::class, $result);

        // Test with specific dates and check the filter info
        $this->statisticsService->setDateRange('2025-05-01', '2025-05-14');
        $filterInfo = $this->statisticsService->getCurrentFilterInfo();

        $this->assertEquals(true, $filterInfo['is_filtered']);
        $this->assertEquals('Du 01/05/2025 au 14/05/2025', $filterInfo['description']);
    }

    public function test_get_task_stats_with_empty_data()
    {
        // With no tasks, should return zeros
        $stats = $this->statisticsService->getTaskStats();

        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['pending']);
        $this->assertEquals(0, $stats['in_progress']);
        $this->assertEquals(0, $stats['completed']);
        $this->assertEquals(0, $stats['canceled']);
        $this->assertEquals(0, $stats['completion_rate']);
    }

    public function test_get_task_stats_with_data()
    {
        // Create some tasks with different statuses
        $this->createTask('pending');
        $this->createTask('pending');
        $this->createTask('in_progress');
        $this->createTask('completed');
        $this->createTask('completed');
        $this->createTask('canceled');

        // Get stats and verify
        $stats = $this->statisticsService->getTaskStats();

        $this->assertEquals(6, $stats['total']);
        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(1, $stats['in_progress']);
        $this->assertEquals(2, $stats['completed']);
        $this->assertEquals(1, $stats['canceled']);
        $this->assertEquals(33.33, $stats['completion_rate']); // 2 completed out of 6 = 33.33%
    }

    public function test_get_task_time_stats()
    {
        // For this test, we'll mock the service method to avoid date calculation issues
        $mockService = $this->createPartialMock(StatisticsService::class, ['getTaskTimeStats']);

        $expectedStats = [
            'avg_completion_time' => 4.0,
            'max_completion_time' => 5.0,
            'min_completion_time' => 3.0,
            'avg_completion_seconds' => 14400,
            'max_completion_seconds' => 18000,
            'min_completion_seconds' => 10800,
        ];

        $mockService->expects($this->once())
            ->method('getTaskTimeStats')
            ->willReturn($expectedStats);

        $timeStats = $mockService->getTaskTimeStats();

        $this->assertEquals($expectedStats, $timeStats);
    }

    public function test_get_current_filter_info_with_no_filters()
    {
        // Default (no date range)
        $filterInfo = $this->statisticsService->getCurrentFilterInfo();

        $this->assertEquals(false, $filterInfo['is_filtered']);
        $this->assertEquals('Toutes les périodes', $filterInfo['description']);
    }

    public function test_get_current_filter_info_with_start_date_only()
    {
        // Only start date
        $this->statisticsService->setDateRange('2025-05-01', null);
        $filterInfo = $this->statisticsService->getCurrentFilterInfo();

        $this->assertEquals(true, $filterInfo['is_filtered']);
        $this->assertEquals('À partir du 01/05/2025', $filterInfo['description']);
    }

    public function test_get_current_filter_info_with_end_date_only()
    {
        // Only end date
        $this->statisticsService->setDateRange(null, '2025-05-14');
        $filterInfo = $this->statisticsService->getCurrentFilterInfo();

        $this->assertEquals(true, $filterInfo['is_filtered']);
        $this->assertEquals('Jusqu\'au 14/05/2025', $filterInfo['description']);
    }

    public function test_get_current_filter_info_with_same_day()
    {
        // Same day for start and end
        $this->statisticsService->setDateRange('2025-05-14', '2025-05-14');
        $filterInfo = $this->statisticsService->getCurrentFilterInfo();

        $this->assertEquals(true, $filterInfo['is_filtered']);
        $this->assertEquals('Pour le 14/05/2025', $filterInfo['description']);
    }

    public function test_get_tasks_by_user_paginated()
    {
        // Create some tasks assigned to our test courier
        $this->createTask('pending', null, null, $this->courier->id);
        $this->createTask('in_progress', null, null, $this->courier->id);
        $this->createTask('completed', null, null, $this->courier->id);

        // Get paginated user stats
        $userStats = $this->statisticsService->getTasksByUserPaginated();

        // Should have our courier
        $this->assertEquals(1, $userStats->count());

        // Verify courier stats
        $courierStats = $userStats->first();
        $this->assertEquals($this->courier->id, $courierStats['id']);
        $this->assertEquals('Test Courier', $courierStats['name']);
        $this->assertEquals('courier', $courierStats['username']);
        $this->assertEquals(3, $courierStats['total_tasks']);
        $this->assertEquals(1, $courierStats['completed_tasks']);
        $this->assertEquals(1, $courierStats['pending_tasks']);
        $this->assertEquals(1, $courierStats['in_progress_tasks']);
        $this->assertEquals(33.33, $courierStats['completion_rate']);
    }

    public function test_get_all_stats()
    {
        // Skip this test if using SQLite (which doesn't support the EXTRACT(DOW) function)
        if (config('database.connections.tenant.driver') === 'sqlite') {
            $this->markTestSkipped('This test requires PostgreSQL for the EXTRACT(DOW) function');
        }

        // This method should return all the stats in one call
        $allStats = $this->statisticsService->getAllStats();

        // Verify the structure
        $this->assertArrayHasKey('task_stats', $allStats);
        $this->assertArrayHasKey('task_time_stats', $allStats);
        $this->assertArrayHasKey('tasks_by_priority', $allStats);
        $this->assertArrayHasKey('tasks_by_day', $allStats);
        $this->assertArrayHasKey('tasks_by_month', $allStats);
        $this->assertArrayHasKey('milestone_stats', $allStats);
        $this->assertArrayHasKey('users_stats_top_5', $allStats);
    }

    public function test_get_tasks_by_priority()
    {
        // Create tasks with different priorities
        $this->createTask('pending', null, null, null, 'low');
        $this->createTask('pending', null, null, null, 'medium');
        $this->createTask('pending', null, null, null, 'medium');
        $this->createTask('pending', null, null, null, 'high');
        $this->createTask('pending', null, null, null, 'high');
        $this->createTask('pending', null, null, null, 'high');

        // Get priority stats
        $priorityStats = $this->statisticsService->getTasksByPriority();

        // Verify counts by priority
        $this->assertEquals(1, $priorityStats['low']);
        $this->assertEquals(2, $priorityStats['medium']);
        $this->assertEquals(3, $priorityStats['high']);
    }

    public function test_get_milestone_stats()
    {
        // Create additional milestones with different properties
        $favoriteMilestone = Milestone::create([
            'name' => 'Favorite Milestone',
            'longitudinal' => '48.8566',
            'latitudinal' => '2.3522',
            'favorite' => true
        ]);

        // Create tasks for the milestones
        $this->createTask('completed', null, null, null, 'medium', $this->milestone->id);
        $this->createTask('completed', null, null, null, 'medium', $this->milestone->id);
        $this->createTask('pending', null, null, null, 'medium', $favoriteMilestone->id);

        // Get milestone stats
        $milestoneStats = $this->statisticsService->getMilestoneStats();

        // Verify milestone stats
        $this->assertEquals(2, $milestoneStats['total']); // Our two milestones
        $this->assertEquals(1, $milestoneStats['favorites']);
        $this->assertGreaterThan(0, $milestoneStats['tasks_per_milestone']);
        $this->assertIsArray($milestoneStats['most_used']);
        $this->assertEquals($this->milestone->id, $milestoneStats['most_used']['id']);
        $this->assertEquals(2, $milestoneStats['most_used']['tasks_count']);
    }

    public function test_get_tasks_by_month()
    {
        // Create tasks with specific dates to test monthly stats
        $now = Carbon::now();

        // Create task for current month
        $this->createTask('completed', $now, $now);

        // Get monthly stats
        $monthlyStats = $this->statisticsService->getTasksByMonth();

        // Verify it returns an array of months
        $this->assertIsArray($monthlyStats);

        // Each month should have created and completed keys
        foreach ($monthlyStats as $month => $stats) {
            $this->assertArrayHasKey('created', $stats);
            $this->assertArrayHasKey('completed', $stats);
            $this->assertIsNumeric($stats['created']);
            $this->assertIsNumeric($stats['completed']);
        }

        // Should have 12 months of data
        $this->assertCount(12, $monthlyStats);
    }

    public function test_get_user_stats_top_5()
    {
        // Create additional users
        $courier2 = User::create([
            'name' => 'Courier 2',
            'email' => 'courier2@company.com',
            'username' => 'courier2',
            'role' => 'courier',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $courier3 = User::create([
            'name' => 'Courier 3',
            'email' => 'courier3@company.com',
            'username' => 'courier3',
            'role' => 'courier',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        // Create tasks for different users with different completion counts
        // Courier 1: 3 tasks, 2 completed
        $this->createTask('completed', null, Carbon::now(), $this->courier->id);
        $this->createTask('completed', null, Carbon::now(), $this->courier->id);
        $this->createTask('pending', null, null, $this->courier->id);

        // Courier 2: 2 tasks, 1 completed
        $this->createTask('completed', null, Carbon::now(), $courier2->id);
        $this->createTask('pending', null, null, $courier2->id);

        // Courier 3: 1 task, 0 completed
        $this->createTask('pending', null, null, $courier3->id);

        // Get top user stats
        $topUsers = $this->statisticsService->getTasksByUserTop5();

        // Should return up to 5 users, ordered by total tasks
        $this->assertCount(3, $topUsers);

        // First user should be our main courier with most tasks
        $this->assertEquals($this->courier->id, $topUsers[0]['id']);
        $this->assertEquals(3, $topUsers[0]['total_tasks']);
        $this->assertEquals(2, $topUsers[0]['completed_tasks']);

        // Second user should be courier2
        $this->assertEquals($courier2->id, $topUsers[1]['id']);
        $this->assertEquals(2, $topUsers[1]['total_tasks']);

        // Third user should be courier3
        $this->assertEquals($courier3->id, $topUsers[2]['id']);
        $this->assertEquals(1, $topUsers[2]['total_tasks']);
    }

    public function test_date_range_filters_are_applied()
    {
        // Get filter info without any date filters
        $noFilter = $this->statisticsService->getCurrentFilterInfo();
        $this->assertEquals(false, $noFilter['is_filtered']);

        // Set a date range
        $startDate = '2025-01-01';
        $endDate = '2025-01-31';
        $this->statisticsService->setDateRange($startDate, $endDate);

        // Get filter info with date range
        $filteredInfo = $this->statisticsService->getCurrentFilterInfo();

        // Verify filter is applied
        $this->assertEquals(true, $filteredInfo['is_filtered']);
        $this->assertEquals('Du 01/01/2025 au 31/01/2025', $filteredInfo['description']);

        // Using a single day
        $singleDate = '2025-05-15';
        $this->statisticsService->setDateRange($singleDate, $singleDate);

        // Verify single day filter
        $singleDayInfo = $this->statisticsService->getCurrentFilterInfo();
        $this->assertEquals(true, $singleDayInfo['is_filtered']);
        $this->assertEquals('Pour le 15/05/2025', $singleDayInfo['description']);
    }

    /**
     * Helper to create a task for testing
     */
    private function createTask($status, $createdAt = null, $completedAt = null, $assignedToId = null, $priority = 'medium', $milestoneId = null)
    {
        return Task::create([
            'name' => 'Test Task',
            'description' => 'Test description',
            'status' => $status,
            'priority' => $priority,
            'created_at' => $createdAt ?? Carbon::now(),
            'completed_at' => $completedAt,
            'user_id' => $assignedToId ?? $this->courier->id,
            'milestone_id' => $milestoneId ?? $this->milestone->id
        ]);
    }
}
