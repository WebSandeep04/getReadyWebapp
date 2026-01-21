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
        // MySQL doesn't support direct ENUM modification, so we use raw SQL
        DB::statement("ALTER TABLE clothes MODIFY COLUMN gender ENUM('Boy', 'Girl', 'Men', 'Women') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        // First, we need to map existing data if needed
        // For safety, we'll keep the new values but you can revert if needed
        DB::statement("ALTER TABLE clothes MODIFY COLUMN gender ENUM('Male', 'Female', 'Unisex') NOT NULL");
    }
};
