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
        Schema::create('clothes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('category');
            $table->enum('gender', ['Male', 'Female', 'Unisex']);
            $table->string('brand')->nullable();
            $table->string('fabric')->nullable();
            $table->string('color')->nullable();
            $table->string('bottom_type')->nullable();
            $table->string('chest_bust')->nullable();
            $table->string('waist')->nullable();
            $table->string('length')->nullable();
            $table->string('shoulder')->nullable();
            $table->string('sleeve_length')->nullable();
            $table->enum('size', ['XS', 'S', 'M', 'L', 'XL', 'Free Size']);
            $table->string('fit_type')->nullable();
            $table->enum('condition', ['Brand New', 'Like New', 'Good Condition', 'Worn but Usable']);
            $table->text('defects')->nullable();
            $table->boolean('is_cleaned')->default(false);
            $table->decimal('rent_price', 10, 2);
            $table->decimal('security_deposit', 10, 2);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_approved')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clothes');
    }
};
