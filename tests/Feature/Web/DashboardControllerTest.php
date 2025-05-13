<?php
// filepath: /home/josemokeni/PFE/lecoursier-laravel/tests/Feature/Web/DashboardControllerTest.php

namespace Tests\Feature\Web;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class DashboardControllerTest extends TestCase
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

    public function test_dashboard_displays_task_statistics()
    {
        Task::factory()->create(['status' => 'in_progress', 'user_id' => $this->admin->id]);
        Task::factory()->create(['status' => 'completed', 'user_id' => $this->admin->id]);
        Task::factory()->create(['status' => 'pending', 'user_id' => $this->admin->id]);
        $response = $this->actingAsAdmin()->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('pages.dashboard.index');
        $response->assertViewHasAll([
            'users', 'userCount', 'totalTasks', 'tasksInProgress', 'tasksCompleted', 'tasksPending',
        ]);
        $this->assertEquals(3, $response->viewData('totalTasks'));
        $this->assertEquals(1, $response->viewData('tasksInProgress'));
        $this->assertEquals(1, $response->viewData('tasksCompleted'));
        $this->assertEquals(1, $response->viewData('tasksPending'));
    }
}
