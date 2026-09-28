<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // ─── Profile ──────────────────────────────────────────────────────────

    public function test_authenticated_user_can_get_own_profile(): void
    {
        $user = $this->makeAdmin();

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/profile')
            ->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/v1/profile')
            ->assertStatus(401);
    }

    public function test_user_can_update_own_info(): void
    {
        $user = $this->makeAdmin();

        $this->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/profile/info', [
                'f_name' => 'NewFirst',
                'l_name' => 'NewLast',
            ])->assertStatus(202)
                ->assertJsonFragment(['f_name' => 'NewFirst']);
    }

    public function test_update_info_rejects_duplicate_email(): void
    {
        $role  = Role::firstOrCreate(['name' => 'admin']);

        $user1 = User::factory()->create([
            'email' => 'unique_user_1@test.com',
            'role_id' => $role->id,
        ]);

        $user2 = User::factory()->create([
            'email' => 'unique_user_2@test.com',
            'role_id' => $role->id,
        ]);

        $this->withHeaders($this->authHeaders($user1))
            ->putJson('/api/v1/profile/info', ['email' => 'unique_user_2@test.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_update_password(): void
    {
        $user = $this->makeAdmin();

        $this->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/profile/password', [
                'password'              => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])->assertStatus(202)
                ->assertJsonFragment(['message' => 'Password updated successfully']);
    }

    public function test_update_password_requires_confirmation(): void
    {
        $user = $this->makeAdmin();

        $this->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/profile/password', [
                'password'              => 'newpassword123',
                'password_confirmation' => 'wrongconfirm',
            ])->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    public function test_update_password_requires_min_8_chars(): void
    {
        $user = $this->makeAdmin();

        $this->withHeaders($this->authHeaders($user))
            ->putJson('/api/v1/profile/password', [
                'password'              => 'short',
                'password_confirmation' => 'short',
            ])->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    // ─── Admin user CRUD ──────────────────────────────────────────────────

    protected function makeAdmin(): User
    {
        // 1. Create or find the Admin role
        $role = Role::firstOrCreate(['name' => 'admin']);

        // 2. Create required permissions matching your AuthServiceProvider Gates
        $viewUsersPermission = Permission::firstOrCreate(['name' => 'View Users']);
        $editUsersPermission = Permission::firstOrCreate(['name' => 'Edit Users']);

        // 3. Attach permissions to the role (or attach to the user if your relation is direct)
        // Adjust 'permissions()' to match your Role or User relationship name
        $role->permissions()->syncWithoutDetaching([
            $viewUsersPermission->id,
            $editUsersPermission->id,
        ]);

        // 4. Create the admin user
        $admin = User::factory()->create([
            'role_id' => $role->id,
        ]);

        // 5. Reload relationships so in-memory checks pass
        return $admin->load(['role', 'role.permissions']);
    }
    public function test_non_admin_cannot_list_users(): void
    {
        $user = $this->makeUser();

        $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/v1/users')
            ->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeaders($admin))
            ->postJson('/api/v1/users', [
                'f_name'                => 'New',
                'l_name'                => 'User',
                'email'                 => 'newuser@test.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role_id'               => $admin->role_id,
            ])->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin  = $this->makeAdmin();
        $target = User::factory()->create(['role_id' => $admin->role_id]);

        $this->withHeaders($this->authHeaders($admin))
            ->deleteJson("/api/v1/users/{$target->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_non_admin_cannot_delete_user(): void
    {
        $editor = $this->makeEditor();
        $target = User::factory()->create(['role_id' => $editor->role_id]);

        $this->withHeaders($this->authHeaders($editor))
            ->deleteJson("/api/v1/users/{$target->id}")
            ->assertStatus(403);
    }
}
