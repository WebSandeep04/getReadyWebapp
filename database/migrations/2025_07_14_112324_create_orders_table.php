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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
        $table->foreignId('buyer_id')->constrained('users');
        $table->decimal('total_amount', 10, 2);
        $table->decimal('security_amount', 10, 2);
        $table->enum('status', ['Pending', 'Confirmed', 'Delivered', 'Returned', 'Cancelled'])->default('Pending');
        $table->text('delivery_address');
        $table->date('rental_from');
        $table->date('rental_to');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
