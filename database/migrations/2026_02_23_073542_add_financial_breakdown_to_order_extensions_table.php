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
        Schema::table('order_extensions', function (Blueprint $table) {
            $table->decimal('base_rent_amount', 10, 2)->default(0)->after('additional_amount');
            $table->decimal('buyer_commission', 10, 2)->default(0)->after('base_rent_amount');
            $table->decimal('seller_commission', 10, 2)->default(0)->after('buyer_commission');
            $table->decimal('rent_gst', 10, 2)->default(0)->after('seller_commission');
            $table->decimal('buyer_commission_gst', 10, 2)->default(0)->after('rent_gst');
            $table->decimal('seller_commission_gst', 10, 2)->default(0)->after('buyer_commission_gst');
            $table->decimal('seller_net_amount', 10, 2)->default(0)->after('seller_commission_gst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_extensions', function (Blueprint $table) {
            $table->dropColumn([
                'base_rent_amount',
                'buyer_commission',
                'seller_commission',
                'rent_gst',
                'buyer_commission_gst',
                'seller_commission_gst',
                'seller_net_amount'
            ]);
        });
    }
};
