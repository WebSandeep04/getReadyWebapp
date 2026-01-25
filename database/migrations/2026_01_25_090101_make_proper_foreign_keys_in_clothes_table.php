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
            // First, change columns to unsignedBigInteger
            // We use nullable() to maintain consistency with existing schema where applicable
            $table->unsignedBigInteger('category')->nullable()->change();
            $table->unsignedBigInteger('fabric')->nullable()->change();
            $table->unsignedBigInteger('color')->nullable()->change();
            $table->unsignedBigInteger('bottom_type')->nullable()->change();
            $table->unsignedBigInteger('size')->nullable()->change(); // Was enum, now FK
            $table->unsignedBigInteger('fit_type')->nullable()->change();

            // Add foreign key constraints
            $table->foreign('category')->references('id')->on('category')->onDelete('set null');
            $table->foreign('fabric')->references('id')->on('fabric_types')->onDelete('set null');
            $table->foreign('color')->references('id')->on('colors')->onDelete('set null');
            $table->foreign('bottom_type')->references('id')->on('bottom_types')->onDelete('set null');
            $table->foreign('size')->references('id')->on('sizes')->onDelete('set null');
            $table->foreign('fit_type')->references('id')->on('body_type_fits')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            $table->dropForeign(['category']);
            $table->dropForeign(['fabric']);
            $table->dropForeign(['color']);
            $table->dropForeign(['bottom_type']);
            $table->dropForeign(['size']);
            $table->dropForeign(['fit_type']);
            
            // Revert types if needed, but usually we just leave them as bigInt if rolling back to strings is complex
            // For completeness, let's try to revert to strings (approximate)
            $table->string('category')->nullable()->change();
            $table->string('fabric')->nullable()->change();
            $table->string('color')->nullable()->change();
            $table->string('bottom_type')->nullable()->change();
            $table->string('size', 10)->nullable()->change();
            $table->string('fit_type')->nullable()->change();
        });
    }
};
