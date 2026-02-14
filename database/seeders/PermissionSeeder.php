<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to truncate safely
        Schema::disableForeignKeyConstraints();
        DB::table('permissions')->truncate();
        Schema::enableForeignKeyConstraints();

        $permissions = [
            // Setup
            ['name' => 'admin.dashboard', 'label' => 'Dashboard', 'module' => 'Setup'],
            ['name' => 'user.index', 'label' => 'Users', 'module' => 'Setup'],
            ['name' => 'categories.index', 'label' => 'Categories', 'module' => 'Setup'],
            ['name' => 'brands.index', 'label' => 'Brands', 'module' => 'Setup'],
            ['name' => 'fabric_types.index', 'label' => 'Fabric Types', 'module' => 'Setup'],
            ['name' => 'colors.index', 'label' => 'Colors', 'module' => 'Setup'],
            ['name' => 'bottom_types.index', 'label' => 'Bottom Types', 'module' => 'Setup'],
            ['name' => 'sizes.index', 'label' => 'Sizes', 'module' => 'Setup'],
            ['name' => 'body_type_fits.index', 'label' => 'Body Type Fits', 'module' => 'Setup'],
            ['name' => 'garment_conditions.index', 'label' => 'Outfit Conditions', 'module' => 'Setup'],
            ['name' => 'role_master.index', 'label' => 'Role Master', 'module' => 'Setup'],
            ['name' => 'admin_panel_users.index', 'label' => 'Admin Users', 'module' => 'Setup'],
            ['name' => 'states.index', 'label' => 'States', 'module' => 'Setup'],
            ['name' => 'cities.index', 'label' => 'Cities', 'module' => 'Setup'],
            ['name' => 'state-list', 'label' => 'State List', 'module' => 'Setup'],
            ['name' => 'state-create', 'label' => 'State Create', 'module' => 'Setup'],
            ['name' => 'state-edit', 'label' => 'State Edit', 'module' => 'Setup'],
            ['name' => 'state-delete', 'label' => 'State Delete', 'module' => 'Setup'],
            ['name' => 'city-list', 'label' => 'City List', 'module' => 'Setup'],
            ['name' => 'city-create', 'label' => 'City Create', 'module' => 'Setup'],
            ['name' => 'city-edit', 'label' => 'City Edit', 'module' => 'Setup'],
            ['name' => 'city-delete', 'label' => 'City Delete', 'module' => 'Setup'],
            ['name' => 'admin.frontend', 'label' => 'Frontend Settings', 'module' => 'Setup'],
            
            // Approval
            ['name' => 'admin.cloth-approval', 'label' => 'Clothes Approval', 'module' => 'Approval'],
            
            // Operations
            ['name' => 'admin.orders', 'label' => 'Orders', 'module' => 'Operations'],
            ['name' => 'admin.security', 'label' => 'Security Deposits', 'module' => 'Operations'],
            ['name' => 'admin.payments', 'label' => 'Payments', 'module' => 'Operations'],
            
            // Reports
            ['name' => 'admin.reports.financial', 'label' => 'Financial Report', 'module' => 'Reports'],
            ['name' => 'admin.reports.calendar', 'label' => 'Alert Calendar', 'module' => 'Reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
