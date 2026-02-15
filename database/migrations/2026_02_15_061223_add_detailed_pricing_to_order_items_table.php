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
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('base_rent', 10, 2)->after('cloth_id')->nullable();
            $table->decimal('buyer_commission', 10, 2)->after('base_rent')->nullable();
            $table->decimal('seller_commission', 10, 2)->after('buyer_commission')->nullable();
            $table->decimal('rent_gst', 10, 2)->after('seller_commission')->nullable();
            $table->decimal('commission_gst', 10, 2)->after('rent_gst')->nullable();
            $table->decimal('tcs_amount', 10, 2)->after('commission_gst')->nullable();
            $table->boolean('is_seller_gst')->default(false)->after('tcs_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'base_rent',
                'buyer_commission',
                'seller_commission',
                'rent_gst',
                'commission_gst',
                'tcs_amount',
                'is_seller_gst',
            ]);
        });
    }
};
