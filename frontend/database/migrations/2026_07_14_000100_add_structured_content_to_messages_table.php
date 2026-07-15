<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('message_type', 40)->default('text')->after('body');
            $table->json('metadata')->nullable()->after('message_type');
            $table->index('message_type');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['message_type']);
            $table->dropColumn(['message_type', 'metadata']);
        });
    }
};
