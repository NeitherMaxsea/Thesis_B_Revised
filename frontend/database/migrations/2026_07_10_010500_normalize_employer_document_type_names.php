<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('employer_documents')
            ->where('document_type', 'DOH Certificate')
            ->update(['document_type' => 'DOLE Certificate']);

        DB::table('employer_documents')
            ->whereIn('document_type', ["Mayor's Permit", 'Mayor Permit'])
            ->update(['document_type' => 'DTI Certificate']);
    }

    public function down(): void
    {
        // Data was normalized in place; reversing safely cannot distinguish
        // legacy records from records created with the current names.
    }
};
