<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = Permission::all();
        $admin = Role::whereName('Admin')->first();
        $editor = Role::whereName('Editor')->first();
        $user = Role::whereName('User')->first();

        // Admin has all permissions
        foreach ($permissions as $permission) {
            DB::table('role_permissions')->insert([
                'role_id' => $admin->id,
                'permission_id' => $permission->id,
            ]);
        }

        // Editor can edit users, products, orders but not roles
        $editorPermissions = $permissions->whereNotIn('name', ['view_roles', 'edit_roles']);
        foreach ($editorPermissions as $permission) {
            DB::table('role_permissions')->insert([
                'role_id' => $editor->id,
                'permission_id' => $permission->id,
            ]);
        }

        // User can only view everything
        $userPermissions = $permissions->filter(function ($p) {
            return strpos($p->name, 'view_') === 0;
        });
        foreach ($userPermissions as $permission) {
            DB::table('role_permissions')->insert([
                'role_id' => $user->id,
                'permission_id' => $permission->id,
            ]);
        }
    }
}
