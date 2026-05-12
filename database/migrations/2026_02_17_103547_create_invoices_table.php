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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->enum('type', ['rent_sale', 'platform_fee_seller', 'platform_fee_buyer']);
            $table->decimal('amount', 10, 2);
            $table->decimal('tax_amount', 10, 2);
            $table->string('pdf_path');
            $table->foreignId('issued_by_id')->nullable()->constrained('users')->onDelete('set null'); // Nullable for Platform
            $table->foreignId('issued_to_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
