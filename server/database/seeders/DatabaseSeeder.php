<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Core independent tables first
            PermissionSeeder::class,      // <-- Creates all permissions
            RoleSeeder::class,            // <-- Creates all roles
            RolePermissionSeeder::class,  // <-- Assigns permissions to roles
            
            // 2. Data dependencies next
            userSeeder::class,            // <-- Assigns roles to users
            ProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}