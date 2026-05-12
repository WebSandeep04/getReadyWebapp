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
            $table->renameColumn('category', 'category_id');
            $table->renameColumn('fabric', 'fabric_id');
            $table->renameColumn('color', 'color_id');
            $table->renameColumn('bottom_type', 'bottom_type_id');
            $table->renameColumn('size', 'size_id');
            $table->renameColumn('fit_type', 'fit_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            $table->renameColumn('category_id', 'category');
            $table->renameColumn('fabric_id', 'fabric');
            $table->renameColumn('color_id', 'color');
            $table->renameColumn('bottom_type_id', 'bottom_type');
            $table->renameColumn('size_id', 'size');
            $table->renameColumn('fit_type_id', 'fit_type');
        });
    }
};
