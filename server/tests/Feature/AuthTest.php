<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    

    // ─── Register ─────────────────────────────────────────────────────────

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'f_name'                => 'Ahmed',
            'l_name'                => 'Hamdy',
            'email'                 => 'ahmed@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'full_name', 'email'],
            ]);

        $this->assertDatabaseHas('users', [
            'email'   => 'ahmed@test.com',
            'role_id' => 3,
        ]);
    }

    public function test_register_fails_with_missing_fields(): void
    {
        $this->postJson('/api/v1/register', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['f_name', 'l_name', 'email', 'password']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        // Role is already created in TestCase, so we can just grab it
        $role = Role::where('name', 'user')->first();
        User::factory()->create(['email' => 'ahmed@test.com', 'role_id' => $role->id]);

        $this->postJson('/api/v1/register', [
            'f_name'                => 'Ahmed',
            'l_name'                => 'Hamdy',
            'email'                 => 'ahmed@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_with_mismatched_password(): void
    {
        $this->postJson('/api/v1/register', [
            'f_name'                => 'Ahmed',
            'l_name'                => 'Hamdy',
            'email'                 => 'ahmed@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'different',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ─── Login ────────────────────────────────────────────────────────────

    public function test_user_can_login_with_valid_credentials(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->postJson('/api/v1/login', [
            'email'    => $admin->email,
            'password' => 'password', // Assuming factory sets 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'full_name', 'email', 'role'],
            ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $admin = $this->makeAdmin();

        $this->postJson('/api/v1/login', [
            'email'    => $admin->email,
            'password' => 'wrongpassword',
        ])->assertStatus(401)
            ->assertJsonFragment(['error' => 'Invalid credentials']);
    }

    public function test_login_fails_with_missing_fields(): void
    {
        $this->postJson('/api/v1/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_fails_with_invalid_email_format(): void
    {
        $this->postJson('/api/v1/login', [
            'email'    => 'not-an-email',
            'password' => 'password123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // ─── Logout ───────────────────────────────────────────────────────────

    public function test_user_can_logout(): void
    {
        // let user login first to get a token
        $admin = $this->makeAdmin();

        $loginResponse = $this->postJson('/api/v1/login', [
            'email'    => $admin->email,
            'password' => 'password',
        ]);

        // then logout
        $this->withToken($loginResponse->json('token'))
            ->postJson('/api/v1/logout')
            ->assertSuccessful();
    }

    // ────────────────────────────────────────────────────────────────────────
    // ┼─ TESTS FOR UNAUTHENTICATED ROUTES ────────────────────────────────────
    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/v1/logout')
            ->assertUnauthorized();   // or assertStatus(401)
    }
}