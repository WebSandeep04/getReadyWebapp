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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('buyer_name')->nullable()->after('buyer_id');
            $table->string('buyer_phone')->nullable()->after('buyer_name');
            $table->string('delivery_city')->nullable()->after('delivery_address');
            $table->string('delivery_state')->nullable()->after('delivery_city');
            $table->string('delivery_pincode')->nullable()->after('delivery_state');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('cloth_id');
            $table->unsignedBigInteger('seller_id')->nullable()->after('quantity');
            $table->string('seller_name')->nullable()->after('seller_id');
            $table->string('seller_phone')->nullable()->after('seller_name');
            $table->text('seller_address')->nullable()->after('seller_phone');
            $table->string('seller_city')->nullable()->after('seller_address');
            $table->string('seller_state')->nullable()->after('seller_city');
            $table->string('seller_pincode')->nullable()->after('seller_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'buyer_name',
                'buyer_phone',
                'delivery_city',
                'delivery_state',
                'delivery_pincode',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'quantity',
                'seller_id',
                'seller_name',
                'seller_phone',
                'seller_address',
                'seller_city',
                'seller_state',
                'seller_pincode',
            ]);
        });
    }
};
