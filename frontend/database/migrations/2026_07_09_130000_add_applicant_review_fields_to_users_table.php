<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'applicant_review_status')) {
                $table->string('applicant_review_status', 24)->default('pending')->after('pwd_id_path');
            }

            if (! Schema::hasColumn('users', 'applicant_review_notes')) {
                $table->text('applicant_review_notes')->nullable()->after('applicant_review_status');
            }

            if (! Schema::hasColumn('users', 'applicant_reviewed_at')) {
                $table->timestamp('applicant_reviewed_at')->nullable()->after('applicant_review_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['applicant_reviewed_at', 'applicant_review_notes', 'applicant_review_status'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
