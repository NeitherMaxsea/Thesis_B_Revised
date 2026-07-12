<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_settings', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('archived_at');
            $table->index(['user_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('conversation_settings', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'deleted_at']);
            $table->dropColumn('deleted_at');
        });
    }
};
