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
        Schema::create('order_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->date('old_rental_to');
            $table->date('new_rental_to');
            $table->integer('extra_days');
            $table->decimal('additional_amount', 10, 2);
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->enum('status', ['pending', 'paid', 'cancelled', 'expired'])->default('pending');
            $table->boolean('is_admin_override')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_extensions');
    }
};
