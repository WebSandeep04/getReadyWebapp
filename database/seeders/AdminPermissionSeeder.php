<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminPanelUser;
use App\Models\Permission;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = AdminPanelUser::where('username', 'admin')->first();
        if ($admin) {
            $permissions = Permission::all();
            $admin->permissions()->sync($permissions->pluck('id'));
        }
    }
}
