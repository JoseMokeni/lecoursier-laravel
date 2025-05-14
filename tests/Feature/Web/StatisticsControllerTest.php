<?php
// filepath: /home/josemokeni/PFE/lecoursier-laravel/tests/Feature/Web/StatisticsControllerTest.php

namespace Tests\Feature\Web;

use App\Models\Milestone;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class StatisticsControllerTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    protected $tenant;
    protected $admin;
    protected $tenantId = 'testcompany';
    protected $mockStatsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTenantDatabase();
        $this->tenant = Tenant::create(['id' => $this->tenantId]);
        tenancy()->initialize($this->tenant);
        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@company.com',
            'username' => $this->tenantId,
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);
        session(['tenant_id' => $this->tenantId]);

        // Create a mock of the StatisticsService
        $this->mockStatsService = Mockery::mock(StatisticsService::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function actingAsAdmin()
    {
        return $this->actingAs($this->admin);
    }

    private function setupMockStatisticsService()
    {
        // Configure the mock to return test data
        $this->mockStatsService->shouldReceive('setDateRange')->andReturnSelf();

        $this->mockStatsService->shouldReceive('getAllStats')->andReturn([
            'task_stats' => ['total' => 5, 'pending' => 2, 'in_progress' => 1, 'completed' => 2, 'canceled' => 0, 'completion_rate' => 40],
            'task_time_stats' => [
                'avg_completion_seconds' => 2.5,
                'max_completion_seconds' => 5,
                'min_completion_seconds' => 1
            ],
            'tasks_by_priority' => ['low' => 1, 'medium' => 2, 'high' => 2],
            'tasks_by_day' => ['Lundi' => 1, 'Mardi' => 2, 'Mercredi' => 0, 'Jeudi' => 1, 'Vendredi' => 1, 'Samedi' => 0, 'Dimanche' => 0],
            'tasks_by_month' => [
                ['month' => 'Jan', 'count' => 10],
                ['month' => 'Feb', 'count' => 15]
            ],
            'milestone_stats' => [
                'total' => 3,
                'avg_tasks' => 1.67,
                'favorites' => 2,
                'most_used' => ['name' => 'Test Milestone', 'tasks_count' => 5],
                'tasks_per_milestone' => 1.67
            ],
            'users_stats_top_5' => []
        ]);

        $this->mockStatsService->shouldReceive('getCurrentFilterInfo')->andReturn([
            'start_date' => '01/01/2025',
            'end_date' => '31/01/2025',
            'period' => 'Current Month',
            'is_filtered' => true,
            'description' => 'Statistiques du 01/01/2025 au 31/01/2025'
        ]);

        // Mock pagination result for couriers
        $this->mockStatsService->shouldReceive('getTasksByUserPaginated')->andReturn(
            new \Illuminate\Pagination\LengthAwarePaginator(
                [
                    [
                        'id' => 1,
                        'name' => 'Courier 1',
                        'username' => 'courier1',
                        'total_tasks' => 10,
                        'completed_tasks' => 8,
                        'pending_tasks' => 1,
                        'in_progress_tasks' => 1,
                        'completion_rate' => 80
                    ],
                    [
                        'id' => 2,
                        'name' => 'Courier 2',
                        'username' => 'courier2',
                        'total_tasks' => 5,
                        'completed_tasks' => 3,
                        'pending_tasks' => 1,
                        'in_progress_tasks' => 1,
                        'completion_rate' => 60
                    ]
                ],
                2, // total
                10, // per page
                1, // current page
                ['path' => request()->url()]
            )
        );

        // Replace the service in the container
        $this->app->instance(StatisticsService::class, $this->mockStatsService);
    }

    public function test_statistics_index_page_loads()
    {
        $this->setupMockStatisticsService();

        $response = $this->actingAsAdmin()->get('/statistics');
        $response->assertStatus(200);
        $response->assertViewIs('pages.statistics.index');
        $response->assertViewHasAll([
            'taskStats',
            'taskTimeStats',
            'tasksByPriority',
            'tasksByDay',
            'tasksByMonth',
            'milestoneStats',
            'usersStats',
            'startDate',
            'endDate',
            'filterType',
            'filterInfo',
        ]);
    }

    public function test_statistics_couriers_page_loads()
    {
        $this->setupMockStatisticsService();

        $response = $this->actingAsAdmin()->get('/statistics/couriers');
        $response->assertStatus(200);
        $response->assertViewIs('pages.statistics.couriers');
        $response->assertViewHasAll([
            'couriers',
            'startDate',
            'endDate',
            'filterType',
            'filterInfo',
        ]);

        // Verify pagination data
        $couriers = $response->viewData('couriers');
        $this->assertCount(2, $couriers);
        $this->assertEquals('Courier 1', $couriers[0]['name']);
        $this->assertEquals('Courier 2', $couriers[1]['name']);
    }
}
