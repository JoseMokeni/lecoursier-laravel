<?php

namespace Tests\Feature\Api;

use App\Models\Milestone;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class MilestoneControllerTest extends TestCase
{
    use DatabaseMigrations, DatabaseRefresh;

    private string $adminUserToken;
    private string $regularUserToken;
    private Milestone $milestone;
    private string $tenantId = 'milestone-test';

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
        $adminUser = User::create([
            'name' => 'API User',
            'username' => 'api_user_admin',
            'email' => 'api@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // get token by post to login endpoint
        $this->adminUserToken = $this->withHeaders([
            'x-tenant-id' => 'milestone-test',
        ])->postJson('/api/login', [
            'username' => $adminUser->username,
            'password' => 'password',
        ])->json('token');

        // Create a regular user
        $regularUser = User::create([
            'name' => 'API User',
            'username' => 'api_user_regular',
            'email' => 'api@regular.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'status' => 'active',
        ]);

        // get token by post to login endpoint
        $this->regularUserToken = $this->withHeaders([
            'x-tenant-id' => 'milestone-test',
        ])->postJson('/api/login', [
            'username' => $regularUser->username,
            'password' => 'password',
        ])->json('token');

        // create a milestone
        $this->milestone = Milestone::create([
            'name' => 'Test Milestone',
            'longitudinal' => 1.234567,
            'latitudinal' => 2.345678,
        ]);
    }

    #[Test]
    /** Test that admin users can retrieve all milestones */
    public function admin_can_get_all_milestones()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->getJson('/api/milestones');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'longitudinal',
                        'latitudinal',
                        'favorite',
                        'createdAt',
                        'updatedAt',
                    ],
                ],
            ]);

        $this->assertCount(1, $response->json('data'));

        // assert that the milestone is in the response
        $this->assertEquals($this->milestone->id, $response->json('data.0.id'));
        $this->assertEquals($this->milestone->name, $response->json('data.0.name'));
        $this->assertEquals($this->milestone->longitudinal, $response->json('data.0.longitudinal'));
        $this->assertEquals($this->milestone->latitudinal, $response->json('data.0.latitudinal'));
    }

    #[Test]
    /** Test that regular users cannot retrieve milestones */
    public function regular_users_cannot_get_milestones()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->getJson('/api/milestones');

        $response->assertStatus(403);
    }

    #[Test]
    /** Test that admin users can create new milestones */
    public function admin_can_create_milestone()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->postJson('/api/milestones', [
            'name' => 'New Milestone',
            'longitudinal' => '3.456789',
            'latitudinal' => '4.567890',
        ]);

        // assert that the milestone is in the database
        $this->assertDatabaseHas('milestones', [
            'name' => 'New Milestone',
            'longitudinal' => '3.456789',
            'latitudinal' => '4.567890',
        ]);

        $response->assertStatus(201);
        $this->assertEquals('New Milestone', $response->json('data.name'));
        $this->assertEquals('3.456789', $response->json('data.longitudinal'));
        $this->assertEquals('4.567890', $response->json('data.latitudinal'));
        $this->assertEquals(false, $response->json('data.favorite'));
    }

    #[Test]
    /** Test that regular users cannot create milestones */
    public function regular_users_cannot_create_milestone()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->postJson('/api/milestones', [
            'name' => 'New Milestone',
            'longitudinal' => '3.456789',
            'latitudinal' => '4.567890',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    /** Test that admin users can update existing milestones */
    public function admin_can_update_milestone()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->putJson('/api/milestones/' . $this->milestone->id, [
            'name' => 'Updated Milestone',
            'longitudinal' => '5.678901',
            'latitudinal' => '6.789012',
        ]);

        // assert that the milestone is updated in the database
        $this->assertDatabaseHas('milestones', [
            'id' => $this->milestone->id,
            'name' => 'Updated Milestone',
            'longitudinal' => '5.678901',
            'latitudinal' => '6.789012',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Milestone', $response->json('data.name'));
        $this->assertEquals('5.678901', $response->json('data.longitudinal'));
        $this->assertEquals('6.789012', $response->json('data.latitudinal'));
    }

    #[Test]
    /** Test that regular users cannot update milestones */
    public function regular_users_cannot_update_milestone()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->putJson('/api/milestones/' . $this->milestone->id, [
            'name' => 'Updated Milestone',
            'longitudinal' => '5.678901',
            'latitudinal' => '6.789012',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    /** Test that admin users can delete milestones */
    public function admin_can_delete_milestone()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->deleteJson('/api/milestones/' . $this->milestone->id);

        // assert that the milestone is deleted from the database
        $this->assertDatabaseMissing('milestones', [
            'id' => $this->milestone->id,
        ]);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Milestone deleted successfully']);
    }

    #[Test]
    /** Test that regular users cannot delete milestones */
    public function regular_users_cannot_delete_milestone()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->deleteJson('/api/milestones/' . $this->milestone->id);

        $response->assertStatus(403);
    }

    #[Test]
    /** Test validation errors when submitting incomplete milestone data */
    public function validation_error_when_creating_milestone_with_missing_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->postJson('/api/milestones', [
            'name' => '',
            'longitudinal' => '',
            'latitudinal' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'longitudinal', 'latitudinal']);
        $this->assertDatabaseMissing('milestones', [
            'name' => '',
            'longitudinal' => '',
            'latitudinal' => '',
        ]);
    }

    #[Test]
    /** Test that unauthenticated users cannot access milestone endpoints */
    public function unauthenticated_users_cannot_access_milestone_endpoints()
    {
        $response = $this->getJson('/api/milestones');

        $response->assertStatus(401);

        $response = $this->postJson('/api/milestones', [
            'name' => 'New Milestone',
            'longitudinal' => '3.456789',
            'latitudinal' => '4.567890',
        ]);

        $response->assertStatus(401);

        $response = $this->putJson('/api/milestones/' . $this->milestone->id, [
            'name' => 'Updated Milestone',
            'longitudinal' => '5.678901',
            'latitudinal' => '6.789012',
        ]);

        $response->assertStatus(401);

        $response = $this->deleteJson('/api/milestones/' . $this->milestone->id);

        $response->assertStatus(401);
    }

    #[Test]
    /** Test that admin users can retrieve a single milestone */
    public function admin_can_get_single_milestone()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->getJson('/api/milestones/' . $this->milestone->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'longitudinal',
                    'latitudinal',
                    'favorite',
                    'createdAt',
                    'updatedAt',
                ],
            ]);

        // Assert that the milestone data is correct
        $this->assertEquals($this->milestone->id, $response->json('data.id'));
        $this->assertEquals($this->milestone->name, $response->json('data.name'));
        $this->assertEquals($this->milestone->longitudinal, $response->json('data.longitudinal'));
        $this->assertEquals($this->milestone->latitudinal, $response->json('data.latitudinal'));
    }

    #[Test]
    /** Test that regular users cannot retrieve a single milestone */
    public function regular_users_cannot_get_single_milestone()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->getJson('/api/milestones/' . $this->milestone->id);

        $response->assertStatus(403);
    }

    #[Test]
    /** Test that admin users can retrieve favorite milestones */
    public function admin_can_get_favorite_milestones()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->getJson('/api/milestones?favorite=true');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'longitudinal',
                        'latitudinal',
                        'favorite',
                        'createdAt',
                        'updatedAt',
                    ],
                ],
            ]);

        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    /** Test that regular users cannot retrieve favorite milestones */
    public function regular_users_cannot_get_favorite_milestones()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularUserToken,
            'x-tenant-id' => 'milestone-test',
        ])->getJson('/api/milestones?favorite=true');

        $response->assertStatus(403);
    }

}
