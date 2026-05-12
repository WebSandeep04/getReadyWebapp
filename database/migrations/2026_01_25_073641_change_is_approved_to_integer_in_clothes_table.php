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
            // Change is_approved to tinyInteger to support -1 as Rejected
            $table->tinyInteger('is_approved')->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            $table->boolean('is_approved')->default(0)->change();
        });
    }
};
