<?php

namespace Tests\Unit;

use App\Models\Milestone;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class TaskPolicyTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    private TaskPolicy $taskPolicy;
    private User $adminUser;
    private User $regularUser;
    private User $anotherRegularUser;
    private Task $task;
    private Milestone $milestone;
    private string $tenantId = 'task-policy-test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshTenantDatabase();

        $this->taskPolicy = new TaskPolicy();

        // Create a tenant
        $tenant = Tenant::create([
            'id' => $this->tenantId,
            'name' => 'Test Tenant',
        ]);

        tenancy()->initialize($tenant);

        // Create users
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'username' => 'admin_policy',
            'email' => 'admin_policy@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->regularUser = User::create([
            'name' => 'Regular User',
            'username' => 'regular_policy',
            'email' => 'regular_policy@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->anotherRegularUser = User::create([
            'name' => 'Another Regular User',
            'username' => 'another_policy',
            'email' => 'another_policy@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // Create a milestone for task association
        $this->milestone = Milestone::create([
            'name' => 'Test Milestone',
            'longitudinal' => '1.234567',
            'latitudinal' => '2.345678',
        ]);

        // Create a task
        $this->task = Task::create([
            'name' => 'Test Task',
            'description' => 'Test Task Description',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'user_id' => $this->regularUser->id,
            'milestone_id' => $this->milestone->id,
        ]);
    }

    #[Test]
    /** Test that any user can view all tasks */
    public function any_user_can_view_any_tasks()
    {
        $this->assertTrue($this->taskPolicy->viewAny($this->adminUser));
        $this->assertTrue($this->taskPolicy->viewAny($this->regularUser));
    }

    #[Test]
    /** Test that any user can view a specific task */
    public function any_user_can_view_task()
    {
        $this->assertTrue($this->taskPolicy->view($this->adminUser, $this->task));
        $this->assertTrue($this->taskPolicy->view($this->regularUser, $this->task));
        $this->assertTrue($this->taskPolicy->view($this->anotherRegularUser, $this->task));
    }

    #[Test]
    /** Test that only admin users can create tasks */
    public function only_admin_can_create_task()
    {
        $this->assertTrue($this->taskPolicy->create($this->adminUser));
        $this->assertFalse($this->taskPolicy->create($this->regularUser));
    }

    #[Test]
    /** Test that admin users can update any task */
    public function admin_can_update_any_task()
    {
        $this->assertTrue($this->taskPolicy->update($this->adminUser, $this->task));
    }

    #[Test]
    /** Test that regular users can update only their own tasks */
    public function regular_users_can_update_own_tasks()
    {
        // Regular user can update their own task
        $this->assertTrue($this->taskPolicy->update($this->regularUser, $this->task));

        // Other regular users cannot update tasks they don't own
        $this->assertFalse($this->taskPolicy->update($this->anotherRegularUser, $this->task));
    }

    #[Test]
    /** Test that only admin users can delete tasks */
    public function only_admin_can_delete_task()
    {
        $this->assertTrue($this->taskPolicy->delete($this->adminUser, $this->task));
        $this->assertFalse($this->taskPolicy->delete($this->regularUser, $this->task));
        $this->assertFalse($this->taskPolicy->delete($this->anotherRegularUser, $this->task));
    }
}
