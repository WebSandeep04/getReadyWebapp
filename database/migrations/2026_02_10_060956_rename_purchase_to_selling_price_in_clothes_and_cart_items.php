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
            $table->renameColumn('purchase_value', 'selling_price');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->renameColumn('total_purchase_cost', 'total_selling_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            $table->renameColumn('selling_price', 'purchase_value');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->renameColumn('total_selling_price', 'total_purchase_cost');
        });
    }
};
