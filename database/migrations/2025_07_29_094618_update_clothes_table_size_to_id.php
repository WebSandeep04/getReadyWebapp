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
            // Drop the enum size column
            $table->dropColumn('size');
        });

        Schema::table('clothes', function (Blueprint $table) {
            // Add new integer size column
            $table->unsignedBigInteger('size')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            // Drop the integer size column
            $table->dropColumn('size');
        });

        Schema::table('clothes', function (Blueprint $table) {
            // Add back the enum size column
            $table->enum('size', ['XS', 'S', 'M', 'L', 'XL', 'Free Size']);
        });
    }
};
