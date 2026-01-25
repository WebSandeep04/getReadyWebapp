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
            // Rename columns
            $table->renameColumn('brand', 'brand_id');
            $table->renameColumn('condition', 'condition_id');
        });

        Schema::table('clothes', function (Blueprint $table) {
            // Change types and add constraints
            $table->unsignedBigInteger('brand_id')->nullable()->change();
            $table->unsignedBigInteger('condition_id')->nullable()->change();

            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('condition_id')->references('id')->on('garment_conditions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['condition_id']);
            
            $table->renameColumn('brand_id', 'brand');
            $table->renameColumn('condition_id', 'condition');
        });

        Schema::table('clothes', function (Blueprint $table) {
            $table->string('brand')->nullable()->change();
            $table->string('condition')->nullable()->change();
        });
    }
};
