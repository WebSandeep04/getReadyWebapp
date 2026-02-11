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
            $table->string('return_reason')->nullable()->after('status');
            $table->text('return_details')->nullable()->after('return_reason');
            $table->text('return_images')->nullable()->after('return_details'); // Storing as JSON string
            $table->text('admin_rejection_reason')->nullable()->after('return_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['return_reason', 'return_details', 'return_images', 'admin_rejection_reason']);
        });
    }
};
