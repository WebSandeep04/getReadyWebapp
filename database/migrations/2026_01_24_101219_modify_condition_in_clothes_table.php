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
            $table->string('condition')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            // Revert back to enum if needed, or leave as string since it's safer.
            // For now, we'll try to revert to the old enum values but "Excellent" etc might fail if present.
            // Ideally we wouldn't revert this strictly without data cleanup.
            // $table->enum('condition', ['Brand New', 'Like New', 'Good Condition', 'Worn but Usable'])->change();
        });
    }
};
