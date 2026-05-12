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
            $table->boolean('is_purchased')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            $table->boolean('is_purchased')->default(0)->change();
        });
    }
};
