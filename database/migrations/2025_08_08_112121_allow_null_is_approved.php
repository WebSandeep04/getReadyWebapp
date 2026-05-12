<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            // Allow NULL values in is_approved column
            $table->boolean('is_approved')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            // Revert back to NOT NULL with default 0
            $table->boolean('is_approved')->nullable(false)->default(0)->change();
        });
    }
};
