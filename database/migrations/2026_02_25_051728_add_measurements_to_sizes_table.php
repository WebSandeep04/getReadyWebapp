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
        Schema::table('sizes', function (Blueprint $table) {
            $table->string('chest_bust')->nullable();
            $table->string('waist')->nullable();
            $table->string('length')->nullable();
            $table->string('shoulder')->nullable();
            $table->string('sleeve_length')->nullable();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sizes', function (Blueprint $table) {
            $table->dropColumn(['chest_bust', 'waist', 'length', 'shoulder', 'sleeve_length']);
        });
    }
};
