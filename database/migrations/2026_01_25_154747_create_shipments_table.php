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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('courier_name')->default('Xpressbees');
            $table->string('waybill_number')->nullable()->index(); // AWB
            $table->string('tracking_url')->nullable();
            $table->string('label_url')->nullable();
            $table->string('reference_id')->nullable(); // Courier's Order ID
            $table->string('status')->default('Pending'); 
            $table->json('courier_response')->nullable(); // Store full API response for debug
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
