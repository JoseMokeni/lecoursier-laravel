<?php

namespace Tests\Feature\Api;

use App\Models\Milestone;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class TaskControllerTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    private string $adminUserToken;
    private string $regularUserToken;
    private Task $task;
    private User $adminUser;
    private User $regularUser;
    private Milestone $milestone;
    private string $tenantId = 'task-test';

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

        // Create an admin user
        $this->adminUser = User::create([
            'name' => 'API User',
            'username' => 'api_user_admin',
            'email' => 'api@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // get token by post to login endpoint
        $this->adminUserToken = $this->withHeaders([
            'x-tenant-id' => 'task-test',
        ])->postJson('/api/login', [
            'username' => $this->adminUser->username,
            'password' => 'password',
        ])->json('token');

        // Create a regular user
        $this->regularUser = User::create([
            'name' => 'API User',
            'username' => 'api_user_regular',
            'email' => 'api@regular.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // get token by post to login endpoint
        $this->regularUserToken = $this->withHeaders([
            'x-tenant-id' => 'task-test',
        ])->postJson('/api/login', [
            'username' => $this->regularUser->username,
            'password' => 'password',
        ])->json('token');

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
            'user_id' => $this->adminUser->id,
            'milestone_id' => $this->milestone->id,
        ]);
    }

    #[Test]
    /** Test that admin users can retrieve all tasks */
    public function admin_can_get_all_tasks()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id'   => $this->tenantId,
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id','name','description','priority','status',
                        'dueDate','completedAt','userId','milestoneId',
                        'createdAt','updatedAt',
                    ],
                ],
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->task->id, $response->json('data.0.id'));
    }

    #[Test]
    /** Test that admin users can create new tasks */
    public function admin_can_create_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id' => 'task-test',
        ])->postJson('/api/tasks', [
            'name' => 'New Task',
            'description' => 'New Task Description',
            'priority' => 'high',
            'status' => 'pending',
            'dueDate' => now()->addDays(10)->toDateString(),
            'userId' => $this->adminUser->id,
            'milestoneId' => $this->milestone->id,
        ]);

        // assert that the task is in the database
        $this->assertDatabaseHas('tasks', [
            'name' => 'New Task',
            'description' => 'New Task Description',
            'priority' => 'high',
        ]);

        $response->assertStatus(201);
        $this->assertEquals('New Task', $response->json('data.name'));
        $this->assertEquals('New Task Description', $response->json('data.description'));
        $this->assertEquals('high', $response->json('data.priority'));
        $this->assertEquals('pending', $response->json('data.status'));
    }

    #[Test]
    /** Test that regular users cannot create tasks */
    public function regular_users_cannot_create_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id' => 'task-test',
        ])->postJson('/api/tasks', [
            'name' => 'New Task',
            'description' => 'New Task Description',
            'priority' => 'high',
            'status' => 'pending',
            'dueDate' => now()->addDays(10)->toDateString(),
            'userId' => $this->regularUser->id,
            'milestoneId' => $this->milestone->id,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    /** Test that admin users can update existing tasks */
    public function admin_can_update_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id'   => $this->tenantId,
        ])->putJson('/api/tasks/' . $this->task->id, [
            'description' => 'Updated Task Description',
            'priority'    => 'high',
            'status'      => 'in_progress',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id'          => $this->task->id,
            'description' => 'Updated Task Description',
            'priority'    => 'high',
            'status'      => 'in_progress',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Task Description', $response->json('data.description'));
    }

    #[Test]
    /** Test that admin users can delete tasks */
    public function admin_can_delete_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id' => 'task-test',
        ])->deleteJson('/api/tasks/' . $this->task->id);

        // assert that the task is deleted from the database
        $this->assertDatabaseMissing('tasks', [
            'id' => $this->task->id,
        ]);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Task deleted successfully']);
    }

    #[Test]
    /** Test that regular users cannot delete tasks */
    public function regular_users_cannot_delete_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id' => 'task-test',
        ])->deleteJson('/api/tasks/' . $this->task->id);

        $response->assertStatus(403);
    }

    #[Test]
    /** Test validation errors when submitting incomplete task data */
    public function validation_error_when_creating_task_with_missing_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id' => 'task-test',
        ])->postJson('/api/tasks', [
            'name' => '',
            'description' => '',
            'userId' => '',
            'milestoneId' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description', 'user_id', 'milestone_id']);
        $this->assertDatabaseMissing('tasks', [
            'name' => '',
            'description' => '',
        ]);
    }

    #[Test]
    /** Test that unauthenticated users cannot access task endpoints */
    public function unauthenticated_users_cannot_access_task_endpoints()
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);

        $response = $this->postJson('/api/tasks', [
            'name' => 'New Task',
            'description' => 'New Task Description',
            'priority' => 'high',
            'status' => 'pending',
            'dueDate' => now()->addDays(10)->toDateString(),
            'userId' => $this->adminUser->id,
            'milestoneId' => $this->milestone->id,
        ]);

        $response->assertStatus(401);

        $response = $this->putJson('/api/tasks/' . $this->task->id, [
            'description' => 'Updated Task Description',
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        $response->assertStatus(401);

        $response = $this->deleteJson('/api/tasks/' . $this->task->id);

        $response->assertStatus(401);
    }

    #[Test]
    /** Test that users can retrieve a single task */
    public function users_can_get_single_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id' => 'task-test',
        ])->getJson('/api/tasks/' . $this->task->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'priority',
                    'status',
                    'dueDate',
                    'completedAt',
                    'userId',
                    'milestoneId',
                    'createdAt',
                    'updatedAt',
                ],
            ]);

        // Assert that the task data is correct
        $this->assertEquals($this->task->id, $response->json('data.id'));
        $this->assertEquals($this->task->name, $response->json('data.name'));
        $this->assertEquals($this->task->description, $response->json('data.description'));
        $this->assertEquals($this->task->priority, $response->json('data.priority'));
        $this->assertEquals($this->task->status, $response->json('data.status'));
    }

    #[Test]
    /** Test that admin users can complete tasks */
    public function admin_can_complete_tasks()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id'   => $this->tenantId,
        ])->putJson('/api/tasks/' . $this->task->id, [
            'status'      => 'completed',
            'completedAt' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(200);
        $this->assertEquals('completed', $response->json('data.status'));
        $this->assertNotNull($response->json('data.completedAt'));
    }

    #[Test]
    /** Test that admin users can start tasks */
    public function admin_can_start_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id'   => $this->tenantId,
        ])->postJson('/api/tasks/' . $this->task->id . '/start');

        $response->assertStatus(200);
        $this->assertEquals('in_progress', $response->json('data.status'));
        $this->assertDatabaseHas('tasks', [
            'id'     => $this->task->id,
            'status' => 'in_progress',
        ]);
    }

    #[Test]
    /** Test that users can start their assigned tasks */
    public function user_can_start_assigned_task()
    {
        // Create a task assigned to the regular user
        $userTask = Task::create([
            'name' => 'User Task',
            'description' => 'Task assigned to regular user',
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'user_id' => $this->regularUser->id,
            'milestone_id' => $this->milestone->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id'   => $this->tenantId,
        ])->postJson('/api/tasks/' . $userTask->id . '/start');

        $response->assertStatus(200);
        $this->assertEquals('in_progress', $response->json('data.status'));
        $this->assertDatabaseHas('tasks', [
            'id'     => $userTask->id,
            'status' => 'in_progress',
        ]);
    }

    #[Test]
    /** Test that users cannot start tasks that are not assigned to them */
    public function user_cannot_start_unassigned_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id'   => $this->tenantId,
        ])->postJson('/api/tasks/' . $this->task->id . '/start');

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', [
            'id'     => $this->task->id,
            'status' => 'pending',
        ]);
    }

    #[Test]
    /** Test that admin users can complete tasks using the complete endpoint */
    public function admin_can_complete_task_via_endpoint()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id'   => $this->tenantId,
        ])->postJson('/api/tasks/' . $this->task->id . '/complete');

        $response->assertStatus(200);
        $this->assertEquals('completed', $response->json('data.status'));
        $this->assertNotNull($response->json('data.completedAt'));
        $this->assertDatabaseHas('tasks', [
            'id'     => $this->task->id,
            'status' => 'completed',
        ]);
    }

    #[Test]
    /** Test that users can complete their assigned tasks */
    public function user_can_complete_assigned_task()
    {
        // Create a task assigned to the regular user
        $userTask = Task::create([
            'name' => 'User Task to Complete',
            'description' => 'Task assigned to regular user',
            'priority' => 'medium',
            'status' => 'in_progress',
            'due_date' => now()->addDays(7),
            'user_id' => $this->regularUser->id,
            'milestone_id' => $this->milestone->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id'   => $this->tenantId,
        ])->postJson('/api/tasks/' . $userTask->id . '/complete');

        $response->assertStatus(200);
        $this->assertEquals('completed', $response->json('data.status'));
        $this->assertNotNull($response->json('data.completedAt'));
        $this->assertDatabaseHas('tasks', [
            'id'     => $userTask->id,
            'status' => 'completed',
        ]);
    }

    #[Test]
    /** Test that users cannot complete tasks that are not assigned to them */
    public function user_cannot_complete_unassigned_task()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id'   => $this->tenantId,
        ])->postJson('/api/tasks/' . $this->task->id . '/complete');

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', [
            'id'     => $this->task->id,
            'status' => 'pending',
        ]);
    }
}
