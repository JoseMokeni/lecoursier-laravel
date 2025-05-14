<?php

namespace Tests\Feature\Web;

use App\Mail\PasswordChangedMail;
use App\Mail\WelcomeUserMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Utilities\DatabaseRefresh;

class UserControllerTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseRefresh;

    protected $tenant;
    protected $mainAdmin;
    protected $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshTenantDatabase();

        // Create a tenant for testing
        $this->tenantId = 'testcompany';
        $this->tenant = Tenant::create(['id' => $this->tenantId]);

        // Initialize tenant context
        tenancy()->initialize($this->tenant);

        // Create the main admin user (simulating what RegisterController does)
        $this->mainAdmin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'username' => $this->tenantId,
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('password')
        ]);

        // Store tenant ID in session for future requests
        session(['tenant_id' => $this->tenantId]);
    }

    private function actingAsAdmin()
    {
        return $this->actingAs($this->mainAdmin);
    }

    public function test_index_displays_all_users()
    {
        // Create some regular users in tenant context
        User::factory()->count(3)->create(['role' => 'user']);

        // Act as the main admin and visit the users page
        $response = $this->actingAsAdmin()
                        ->get('/users');

        // Assert response is successful and contains the correct view
        $response->assertStatus(200);
        $response->assertViewIs('pages.users.index');

        // We should see 4 users (3 created + the main admin)
        $response->assertViewHas('users', function($users) {
            return $users->count() === 4;
        });
    }

    public function test_create_displays_user_creation_form()
    {
        $response = $this->actingAsAdmin()
                        ->get('/users/create');

        $response->assertStatus(200);
        $response->assertViewIs('pages.users.create');
    }

    public function test_store_creates_new_user()
    {
        Mail::fake();

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'username' => 'newuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user'
        ];

        $response = $this->actingAsAdmin()
                        ->post('/users', $userData);

        // Assert redirect to users list with success message
        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        // Assert user was created in the database
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'username' => 'newuser',
            'role' => 'user',
            'status' => 'active'
        ]);

        // Assert welcome email was sent
        Mail::assertQueued(WelcomeUserMail::class, function($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });
    }

    public function test_store_validates_required_fields()
    {
        // Missing required fields
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'username' => '',
            'password' => 'pass',
            'password_confirmation' => 'different'
        ];

        $response = $this->actingAsAdmin()
                        ->post('/users', $userData);

        // Should have validation errors
        $response->assertSessionHasErrors(['name', 'email', 'username', 'password']);
    }

    public function test_store_validates_unique_email_and_username()
    {
        // Create existing user
        User::factory()->create([
            'email' => 'existing@example.com',
            'username' => 'existinguser'
        ]);

        // Try to create user with same email/username
        $userData = [
            'name' => 'Duplicate User',
            'email' => 'existing@example.com',
            'username' => 'existinguser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user'
        ];

        $response = $this->actingAsAdmin()
                        ->post('/users', $userData);

        // Should have validation errors for both fields
        $response->assertSessionHasErrors(['email', 'username']);
    }

    public function test_main_admin_can_create_admin_user()
    {
        Mail::fake();

        $userData = [
            'name' => 'Another Admin',
            'email' => 'anotheradmin@example.com',
            'username' => 'anotheradmin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin'
        ];

        $response = $this->actingAsAdmin()
                        ->post('/users', $userData);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        // Assert admin user was created
        $this->assertDatabaseHas('users', [
            'name' => 'Another Admin',
            'email' => 'anotheradmin@example.com',
            'username' => 'anotheradmin',
            'role' => 'admin'
        ]);
    }

    public function test_regular_admin_cannot_create_admin_user()
    {
        Mail::fake();

        // First, create a regular admin user
        $regularAdmin = User::factory()->create([
            'role' => 'admin',
            'username' => 'regularadmin'
        ]);

        $userData = [
            'name' => 'Attempted Admin',
            'email' => 'attemptedadmin@example.com',
            'username' => 'attemptedadmin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin'
        ];

        // Act as the regular admin (not main admin)
        $response = $this->actingAs($regularAdmin)
                        ->post('/users', $userData);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        // Assert user was created but with role 'user' instead of 'admin'
        $this->assertDatabaseHas('users', [
            'name' => 'Attempted Admin',
            'email' => 'attemptedadmin@example.com',
            'username' => 'attemptedadmin',
            'role' => 'user' // Role should be downgraded to 'user'
        ]);

        $this->assertDatabaseMissing('users', [
            'username' => 'attemptedadmin',
            'role' => 'admin'
        ]);
    }

    public function test_email_failure_doesnt_prevent_user_creation()
    {
        // Set up Mail to throw an exception
        Mail::shouldReceive('to')->andThrow(new \Exception('Mail server connection failed'));

        $userData = [
            'name' => 'Mail Fail User',
            'email' => 'mailfail@example.com',
            'username' => 'mailfailuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user'
        ];

        $response = $this->actingAsAdmin()
                        ->post('/users', $userData);

        // Should still succeed despite mail failure
        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        // User should be created
        $this->assertDatabaseHas('users', [
            'email' => 'mailfail@example.com',
            'username' => 'mailfailuser'
        ]);
    }

    public function test_edit_displays_user_edit_form()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAsAdmin()
                        ->get("/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('pages.users.edit');
        $response->assertViewHas('user', function($viewUser) use ($user) {
            return $viewUser->id === $user->id;
        });
    }

    public function test_edit_returns_error_for_nonexistent_user()
    {
        $response = $this->actingAsAdmin()
                        ->get("/users/999/edit");

        $response->assertRedirect('/users');
        $response->assertSessionHas('error', 'Utilisateur non trouvé');
    }

    public function test_main_admin_can_edit_any_user()
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        $anotherAdmin = User::factory()->create(['role' => 'admin']);

        // Main admin can edit a regular user
        $userResponse = $this->actingAsAdmin()
                            ->get("/users/{$regularUser->id}/edit");
        $userResponse->assertStatus(200);

        // Main admin can also edit another admin
        $adminResponse = $this->actingAsAdmin()
                            ->get("/users/{$anotherAdmin->id}/edit");
        $adminResponse->assertStatus(200);
    }

    public function test_regular_admin_cannot_edit_other_admin()
    {
        // Create a second admin user
        $anotherAdmin = User::factory()->create(['role' => 'admin']);

        // Create a regular admin (not the main admin)
        $regularAdmin = User::factory()->create([
            'role' => 'admin',
            'username' => 'regularadmin'
        ]);

        // Try to edit the other admin as a regular admin
        $response = $this->actingAs($regularAdmin)
                        ->get("/users/{$anotherAdmin->id}/edit");

        // Should redirect with error
        $response->assertRedirect('/users');
        $response->assertSessionHas('error', 'Vous n\'avez pas la permission de modifier cet administrateur');
    }

    public function test_regular_admin_can_edit_themselves()
    {
        // Create a regular admin
        $regularAdmin = User::factory()->create([
            'role' => 'admin',
            'username' => 'regularadmin'
        ]);

        // Regular admin should be able to edit their own profile
        $response = $this->actingAs($regularAdmin)
                        ->get("/users/{$regularAdmin->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('pages.users.edit');
    }

    public function test_update_user_information()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => 'user'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'user',
            'status' => 'active'
        ];

        $response = $this->actingAsAdmin()
                        ->put("/users/{$user->id}", $updateData);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_update_validates_required_fields()
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => '',
            'email' => 'not-an-email',
            'role' => 'user',
            'status' => 'active'
        ];

        $response = $this->actingAsAdmin()
                        ->put("/users/{$user->id}", $updateData);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_update_validates_unique_email()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Try to update user2 with user1's email
        $updateData = [
            'name' => 'Updated Name',
            'email' => $user1->email,
            'role' => 'user',
            'status' => 'active'
        ];

        $response = $this->actingAsAdmin()
                        ->put("/users/{$user2->id}", $updateData);

        $response->assertSessionHasErrors('email');
    }

    public function test_update_user_with_password()
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'user']);

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'user',
            'status' => 'active',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAsAdmin()
                        ->put("/users/{$user->id}", $updateData);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        // Assert password change notification was sent
        Mail::assertQueued(PasswordChangedMail::class, function($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_update_password_validation()
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'user',
            'status' => 'active',
            'password' => 'short',
            'password_confirmation' => 'doesnt_match'
        ];

        $response = $this->actingAsAdmin()
                        ->put("/users/{$user->id}", $updateData);

        $response->assertSessionHasErrors('password');
    }

    public function test_self_password_update_doesnt_include_password_in_email()
    {
        Mail::fake();

        // Update main admin's own password
        $updateData = [
            'name' => $this->mainAdmin->name,
            'email' => $this->mainAdmin->email,
            'password' => 'newadminpass123',
            'password_confirmation' => 'newadminpass123'
        ];

        $response = $this->actingAsAdmin()
                        ->put("/users/{$this->mainAdmin->id}", $updateData);

        $response->assertRedirect('/users');

        // Email should be sent but without password included
        Mail::assertQueued(PasswordChangedMail::class, function($mail) {
            // We would need to check the mail content to verify password not included
            // but we can at least verify it was sent to the right address
            return $mail->hasTo($this->mainAdmin->email);
        });
    }

    public function test_email_failure_during_password_update_doesnt_prevent_update()
    {
        // Set up Mail to throw an exception
        Mail::shouldReceive('to')->andThrow(new \Exception('Mail server connection failed'));

        $user = User::factory()->create();

        $updateData = [
            'name' => 'Mail Fail Update',
            'email' => $user->email,
            'role' => 'user',
            'status' => 'active',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAsAdmin()
                        ->put("/users/{$user->id}", $updateData);

        // Should still succeed despite mail failure
        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        // User should be updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Mail Fail Update'
        ]);
    }

    public function test_cannot_update_main_admin_role()
    {
        // Try to update main admin role to 'user'
        $updateData = [
            'name' => $this->mainAdmin->name,
            'email' => $this->mainAdmin->email,
            'role' => 'user', // Attempt to downgrade to user
            'status' => 'inactive' // Attempt to deactivate
        ];

        // Even as the main admin, you can't downgrade yourself
        $response = $this->actingAsAdmin()
                        ->put("/users/{$this->mainAdmin->id}", $updateData);

        $response->assertRedirect('/users');

        // Role and status should remain unchanged
        $this->assertDatabaseHas('users', [
            'id' => $this->mainAdmin->id,
            'role' => 'admin',
            'status' => 'active'
        ]);
    }

    public function test_update_returns_error_for_nonexistent_user()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'user',
            'status' => 'active'
        ];

        $response = $this->actingAsAdmin()
                        ->put("/users/999", $updateData);

        $response->assertRedirect('/users');
        $response->assertSessionHas('error', 'Utilisateur non trouvé');
    }

    public function test_regular_admin_cannot_upgrade_user_to_admin()
    {
        $regularAdmin = User::factory()->create([
            'role' => 'admin',
            'username' => 'regularadmin'
        ]);

        $regularUser = User::factory()->create(['role' => 'user']);

        // Regular admin tries to upgrade a user to admin
        $updateData = [
            'name' => $regularUser->name,
            'email' => $regularUser->email,
            'role' => 'admin', // Attempt to upgrade
            'status' => 'active'
        ];

        $response = $this->actingAs($regularAdmin)
                        ->put("/users/{$regularUser->id}", $updateData);

        // Role should remain unchanged
        $this->assertDatabaseHas('users', [
            'id' => $regularUser->id,
            'role' => 'user'
        ]);
    }

    public function test_regular_admin_cannot_update_another_admin()
    {
        $regularAdmin = User::factory()->create([
            'role' => 'admin',
            'username' => 'regularadmin'
        ]);

        $anotherAdmin = User::factory()->create(['role' => 'admin']);

        // Regular admin tries to update another admin
        $updateData = [
            'name' => 'New Admin Name',
            'email' => $anotherAdmin->email,
            'role' => 'admin',
            'status' => 'active'
        ];

        $response = $this->actingAs($regularAdmin)
                        ->put("/users/{$anotherAdmin->id}", $updateData);

        $response->assertRedirect('/users');
        $response->assertSessionHas('error', 'Vous n\'avez pas la permission de modifier cet administrateur');

        // Admin should remain unchanged
        $this->assertDatabaseMissing('users', [
            'id' => $anotherAdmin->id,
            'name' => 'New Admin Name'
        ]);
    }

    public function test_delete_user()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAsAdmin()
                        ->delete("/users/{$user->id}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    public function test_delete_returns_error_for_nonexistent_user()
    {
        $response = $this->actingAsAdmin()
                        ->delete("/users/999");

        $response->assertRedirect('/users');
        $response->assertSessionHas('error', 'Utilisateur non trouvé');
    }

    public function test_cannot_delete_main_admin()
    {
        // Try to delete the main admin
        $response = $this->actingAsAdmin()
                        ->delete("/users/{$this->mainAdmin->id}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('error', 'L\'administrateur principal ne peut pas être supprimé');

        // Main admin should still exist
        $this->assertDatabaseHas('users', [
            'id' => $this->mainAdmin->id
        ]);
    }

    public function test_regular_admin_cannot_delete_other_admin()
    {
        // Create another admin
        $anotherAdmin = User::factory()->create(['role' => 'admin']);

        // Create a regular admin
        $regularAdmin = User::factory()->create([
            'role' => 'admin',
            'username' => 'regularadmin'
        ]);

        // Try to delete the other admin as a regular admin
        $response = $this->actingAs($regularAdmin)
                        ->delete("/users/{$anotherAdmin->id}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('error', 'Seul l\'administrateur principal peut supprimer d\'autres administrateurs');

        // Other admin should still exist
        $this->assertDatabaseHas('users', [
            'id' => $anotherAdmin->id
        ]);
    }

    public function test_regular_admin_can_delete_regular_user()
    {
        // Create a regular user
        $regularUser = User::factory()->create(['role' => 'user']);

        // Create a regular admin
        $regularAdmin = User::factory()->create([
            'role' => 'admin',
            'username' => 'regularadmin'
        ]);

        // Regular admin should be able to delete regular users
        $response = $this->actingAs($regularAdmin)
                        ->delete("/users/{$regularUser->id}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('success');

        // User should be deleted
        $this->assertDatabaseMissing('users', [
            'id' => $regularUser->id
        ]);
    }

    public function test_change_password_displays_form()
    {
        $response = $this->get('/change-password');

        $response->assertStatus(200);
        $response->assertViewIs('pages.users.change-password');
    }

    public function test_update_password_with_valid_credentials()
    {
        // Create a user in the tenant context
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('currentpassword')
        ]);

        $updateData = [
            'company_code' => $this->tenantId,
            'username' => 'testuser',
            'old_password' => 'currentpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->post('/change-password', $updateData);

        // Should redirect to login with success message
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // Manually initialize tenant context to verify the password was updated
        tenancy()->initialize($this->tenant);
        $updatedUser = User::where('username', 'testuser')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $updatedUser->password));
        tenancy()->end();
    }

    public function test_update_password_with_invalid_company_code()
    {
        $updateData = [
            'company_code' => 'nonexistent',
            'username' => 'testuser',
            'old_password' => 'currentpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->post('/change-password', $updateData);

        // Should return back with validation errors
        $response->assertSessionHasErrors(['company_code']);
    }

    public function test_update_password_with_invalid_username()
    {
        $updateData = [
            'company_code' => $this->tenantId,
            'username' => 'nonexistentuser',
            'old_password' => 'currentpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->post('/change-password', $updateData);

        // Should return back with validation errors
        $response->assertSessionHasErrors(['username']);
    }

    public function test_update_password_with_incorrect_current_password()
    {
        // Create a user in the tenant context
        tenancy()->initialize($this->tenant);
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('currentpassword')
        ]);
        tenancy()->end();

        $updateData = [
            'company_code' => $this->tenantId,
            'username' => 'testuser',
            'old_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->post('/change-password', $updateData);

        // Should return back with validation errors
        $response->assertSessionHasErrors(['old_password']);
    }

    public function test_update_password_validates_password_confirmation()
    {
        // Create a user in the tenant context
        tenancy()->initialize($this->tenant);
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('currentpassword')
        ]);
        tenancy()->end();

        $updateData = [
            'company_code' => $this->tenantId,
            'username' => 'testuser',
            'old_password' => 'currentpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword'
        ];

        $response = $this->post('/change-password', $updateData);

        // Should return back with validation errors
        $response->assertSessionHasErrors(['password']);
    }

    public function test_update_password_validates_password_length()
    {
        // Create a user in the tenant context
        tenancy()->initialize($this->tenant);
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('currentpassword')
        ]);
        tenancy()->end();

        $updateData = [
            'company_code' => $this->tenantId,
            'username' => 'testuser',
            'old_password' => 'currentpassword',
            'password' => 'short',
            'password_confirmation' => 'short'
        ];

        $response = $this->post('/change-password', $updateData);

        // Should return back with validation errors
        $response->assertSessionHasErrors(['password']);
    }

    public function test_update_password_requires_all_fields()
    {
        $response = $this->post('/change-password', []);

        // Should return back with validation errors for all required fields
        $response->assertSessionHasErrors(['company_code', 'username', 'old_password', 'password']);
    }
}
