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
        Schema::create('delivery_logs', function (Blueprint $table) {
            $table->id();
        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
        $table->enum('pickup_status', ['Pending', 'Picked', 'Failed'])->default('Pending');
        $table->enum('delivery_status', ['Pending', 'Delivered', 'Failed'])->default('Pending');
        $table->enum('return_status', ['Pending', 'Returned', 'Late', 'Damaged'])->default('Pending');
        $table->string('delivery_partner')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_logs');
    }
};
