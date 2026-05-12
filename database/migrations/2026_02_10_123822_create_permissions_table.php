<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'user.index'
            $table->string('label'); // e.g., 'View Users'
            $table->string('module'); // e.g., 'Setup'
            $table->timestamps();
        });

        // Seed initial permissions based on sidebar
        $permissions = [
            // Setup
            ['name' => 'user.index', 'label' => 'View Users', 'module' => 'Setup'],
            ['name' => 'categories.index', 'label' => 'View Categories', 'module' => 'Setup'],
            ['name' => 'brands.index', 'label' => 'View Brands', 'module' => 'Setup'],
            ['name' => 'fabric_types.index', 'label' => 'View Fabric Types', 'module' => 'Setup'],
            ['name' => 'colors.index', 'label' => 'View Colors', 'module' => 'Setup'],
            ['name' => 'bottom_types.index', 'label' => 'View Bottom Types', 'module' => 'Setup'],
            ['name' => 'sizes.index', 'label' => 'View Sizes', 'module' => 'Setup'],
            ['name' => 'body_type_fits.index', 'label' => 'View Body Type Fits', 'module' => 'Setup'],
            ['name' => 'garment_conditions.index', 'label' => 'View Outfit Conditions', 'module' => 'Setup'],
            ['name' => 'roles.index', 'label' => 'View Roles', 'module' => 'Setup'],
            ['name' => 'admin_panel_users.index', 'label' => 'View Admin Users', 'module' => 'Setup'],
            ['name' => 'admin.frontend', 'label' => 'View Frontend Settings', 'module' => 'Setup'],
            
            // Approval
            ['name' => 'admin.cloth-approval', 'label' => 'Cloth Approval', 'module' => 'Approval'],
            
            // Operations
            ['name' => 'admin.orders', 'label' => 'View Orders', 'module' => 'Operations'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
