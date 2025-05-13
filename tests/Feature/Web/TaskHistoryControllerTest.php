<?php
// filepath: /home/josemokeni/PFE/lecoursier-laravel/tests/Feature/Web/TaskHistoryControllerTest.php

namespace Tests\Feature\Web;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class TaskHistoryControllerTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    protected $tenant;
    protected $admin;
    protected $tenantId = 'testcompany';

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
    }

    private function actingAsAdmin()
    {
        return $this->actingAs($this->admin);
    }

    public function test_history_displays_tasks()
    {
        Task::factory()->count(3)->create(['status' => 'completed', 'priority' => 'high', 'user_id' => $this->admin->id]);
        $response = $this->actingAsAdmin()->get('/tasks/history');
        $response->assertStatus(200);
        $response->assertViewIs('pages.tasks.history');
        $response->assertViewHas('tasks');
        $this->assertGreaterThanOrEqual(3, $response->viewData('tasks')->count());
    }

    public function test_history_filters_by_status()
    {
        Task::factory()->create(['status' => 'completed', 'user_id' => $this->admin->id]);
        Task::factory()->create(['status' => 'pending', 'user_id' => $this->admin->id]);
        $response = $this->actingAsAdmin()->get('/tasks/history?status=completed');
        $response->assertStatus(200);
        $tasks = $response->viewData('tasks');
        foreach ($tasks as $task) {
            $this->assertEquals('completed', $task->status);
        }
    }

    public function test_history_filters_by_priority()
    {
        Task::factory()->create(['priority' => 'high', 'user_id' => $this->admin->id]);
        Task::factory()->create(['priority' => 'low', 'user_id' => $this->admin->id]);
        $response = $this->actingAsAdmin()->get('/tasks/history?priority=high');
        $response->assertStatus(200);
        $tasks = $response->viewData('tasks');
        foreach ($tasks as $task) {
            $this->assertEquals('high', $task->priority);
        }
    }

    public function test_history_searches_by_name_and_description()
    {
        Task::factory()->create(['name' => 'Special Task', 'description' => 'Unique description', 'user_id' => $this->admin->id]);
        $response = $this->actingAsAdmin()->get('/tasks/history?search=Special');
        $response->assertStatus(200);
        $tasks = $response->viewData('tasks');
        $this->assertTrue($tasks->contains(function($task) { return str_contains($task->name, 'Special'); }));
    }

    public function test_history_filters_by_date_today()
    {
        Task::factory()->create(['created_at' => now(), 'user_id' => $this->admin->id]);
        Task::factory()->create(['created_at' => now()->subDays(2), 'user_id' => $this->admin->id]);
        $response = $this->actingAsAdmin()->get('/tasks/history?date_filter=today');
        $response->assertStatus(200);
        $tasks = $response->viewData('tasks');
        foreach ($tasks as $task) {
            $this->assertEquals(now()->toDateString(), $task->created_at->toDateString());
        }
    }

    public function test_history_filters_by_date_week()
    {
        Task::factory()->create(['created_at' => now()->startOfWeek(), 'user_id' => $this->admin->id]);
        Task::factory()->create(['created_at' => now()->subWeeks(2), 'user_id' => $this->admin->id]);
        $response = $this->actingAsAdmin()->get('/tasks/history?date_filter=week');
        $response->assertStatus(200);
        $tasks = $response->viewData('tasks');
        foreach ($tasks as $task) {
            $this->assertTrue($task->created_at->between(now()->startOfWeek(), now()->endOfWeek()));
        }
    }

    public function test_history_filters_by_date_month()
    {
        Task::factory()->create(['created_at' => now()->startOfMonth(), 'user_id' => $this->admin->id]);
        Task::factory()->create(['created_at' => now()->subMonths(2), 'user_id' => $this->admin->id]);
        $response = $this->actingAsAdmin()->get('/tasks/history?date_filter=month');
        $response->assertStatus(200);
        $tasks = $response->viewData('tasks');
        foreach ($tasks as $task) {
            $this->assertEquals(now()->month, $task->created_at->month);
            $this->assertEquals(now()->year, $task->created_at->year);
        }
    }

    public function test_history_pagination()
    {
        Task::factory()->count(15)->create(['user_id' => $this->admin->id]);
        $response = $this->actingAsAdmin()->get('/tasks/history');
        $response->assertStatus(200);
        $tasks = $response->viewData('tasks');
        $this->assertTrue($tasks->count() <= 10); // default pagination is 10
    }
}
