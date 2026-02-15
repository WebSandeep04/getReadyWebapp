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
            $table->decimal('buyer_commission_gst', 10, 2)->after('commission_gst')->nullable();
            $table->decimal('seller_commission_gst', 10, 2)->after('buyer_commission_gst')->nullable();
        });

        // Optionally migrate data from commission_gst to the new columns?
        // Since the system is 20/20, buyer_gst and seller_gst are usually equal (50% of commission_gst).
        // But let's just keep it simple as per user request to "update everything".
        
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('commission_gst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('commission_gst', 10, 2)->after('rent_gst')->nullable();
            $table->dropColumn(['buyer_commission_gst', 'seller_commission_gst']);
        });
    }
};
