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
        Schema::table('users', function (Blueprint $table) {
            $table->string('gst_legal_name')->nullable();
            $table->string('gst_trade_name')->nullable();
            $table->string('gst_constitution_of_business')->nullable();
            $table->string('gst_status')->nullable();
            $table->string('gst_registration_date')->nullable();
            $table->text('gst_principal_address')->nullable();
            $table->json('gst_nature_of_business')->nullable();
            $table->json('gst_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gst_legal_name',
                'gst_trade_name',
                'gst_constitution_of_business',
                'gst_status',
                'gst_registration_date',
                'gst_principal_address',
                'gst_nature_of_business',
                'gst_details'
            ]);
        });
    }
};
