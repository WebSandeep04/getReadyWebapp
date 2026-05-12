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
            $table->tinyInteger('is_approved')->nullable()->default(null)->change();
        });
        
        // Also update any existing 0s to null so they appear in "Pending"
        \DB::table('clothes')->where('is_approved', 0)->update(['is_approved' => null]);
    }

    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            $table->tinyInteger('is_approved')->nullable()->default(0)->change();
        });
    }
};
