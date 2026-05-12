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
            $table->boolean('security_absorbed_into_purchase')->default(false)->after('is_security_returned');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->timestamp('converted_to_purchase_at')->nullable()->after('purchase_type');
            $table->decimal('conversion_amount', 10, 2)->nullable()->after('converted_to_purchase_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('security_absorbed_into_purchase');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['converted_to_purchase_at', 'conversion_amount']);
        });
    }
};
