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
        Schema::create('cloth_measurements', function (Blueprint $table) {
            $table->id();
        $table->foreignId('cloth_id')->constrained('clothes')->onDelete('cascade');
        $table->float('chest_cm')->nullable();
        $table->float('waist_cm')->nullable();
        $table->float('length_cm')->nullable();
        $table->float('shoulder_cm')->nullable();
        $table->float('sleeve_length_cm')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloth_measurements');
    }
};
