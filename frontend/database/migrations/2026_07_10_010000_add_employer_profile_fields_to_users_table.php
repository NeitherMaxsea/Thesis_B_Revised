<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('account_type');
            $table->string('employer_contact_name')->nullable()->after('company_name');
            $table->string('employer_contact_number', 10)->nullable()->after('employer_contact_name');
            $table->string('employer_document_status')->default('valid')->after('employer_contact_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'employer_contact_name', 'employer_contact_number', 'employer_document_status']);
        });
    }
};
