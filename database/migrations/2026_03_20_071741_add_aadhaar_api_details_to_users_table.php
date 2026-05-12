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
            $table->string('aadhaar_masked_number')->nullable();
            $table->json('aadhaar_address')->nullable();
            $table->string('aadhaar_dob')->nullable();
            $table->string('aadhaar_care_of')->nullable();
            $table->text('aadhaar_xml_link')->nullable();
            $table->text('aadhaar_pdf_link')->nullable();
            $table->json('aadhaar_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'aadhaar_masked_number',
                'aadhaar_address',
                'aadhaar_dob',
                'aadhaar_care_of',
                'aadhaar_xml_link',
                'aadhaar_pdf_link',
                'aadhaar_details'
            ]);
        });
    }
};
