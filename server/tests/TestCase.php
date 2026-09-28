<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;



    /**
     *  Set up the test environment.  
    */
        protected function setUp(): void
            {
                parent::setUp();

                Artisan::call('passport:keys', ['--force' => true, '--no-interaction' => true]);

                $client = app(ClientRepository::class)->createPersonalAccessClient(
                    null, 'Test Client', 'http://localhost'
                );
                
                config(['passport.personal_access_client.id' => $client->id]);
                config(['passport.personal_access_client.secret' => $client->secret]);

                // Guarded against unique constraint duplicates entirely
                Role::firstOrCreate(['name' => 'admin']);
                Role::firstOrCreate(['name' => 'editor']);
                Role::firstOrCreate(['name' => 'user']);
            }

    protected function authHeaders(User $user): array
        {
            $token = $user->createToken('test-token')->accessToken;

            return [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ];
        }
        protected function makeAdmin(): User
        {
            // 1. Ensure the admin role exists
            $role = Role::firstOrCreate(['name' => 'admin']);

            // 2. Create the user assigned to that role
            $user = User::factory()->create([
                'role_id' => $role->id,
            ]);

            // 3. Eager load the role relationship
            return $user->load('role');
        }
        protected function makeEditor(): User
        {
            $role = Role::firstOrCreate(['name' => 'editor']);
            return User::factory()->create(['role_id' => $role->id])->load('role');
        }

        protected function makeUser(): User
        {
            $role = Role::firstOrCreate(['name' => 'user']);
            return User::factory()->create(['role_id' => $role->id])->fresh(['role']);
        }
    }
