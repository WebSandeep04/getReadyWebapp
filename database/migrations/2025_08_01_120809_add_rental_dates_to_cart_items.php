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
            $table->date('rental_start_date')->nullable()->after('quantity');
            $table->date('rental_end_date')->nullable()->after('rental_start_date');
            $table->decimal('total_rental_cost', 10, 2)->nullable()->after('rental_end_date');
            $table->integer('rental_days')->nullable()->after('total_rental_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['rental_start_date', 'rental_end_date', 'total_rental_cost', 'rental_days']);
        });
    }
};
