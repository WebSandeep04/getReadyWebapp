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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->enum('purchase_type', ['rent', 'buy'])->default('rent')->after('quantity');
            $table->decimal('total_purchase_cost', 10, 2)->nullable()->after('total_rental_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['purchase_type', 'total_purchase_cost']);
        });
    }
};
