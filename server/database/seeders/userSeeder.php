<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class userSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::whereName('Admin')->first();
        $editorRole = Role::whereName('Editor')->first();
        $userRole = Role::whereName('User')->first();

        // Create admin user
        User::factory(1)->create(['role_id' => $adminRole->id]);

        // Create editor users
        User::factory(3)->create(['role_id' => $editorRole->id]);

        // Create regular users
        User::factory(6)->create(['role_id' => $userRole->id]);
    }
}
