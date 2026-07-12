<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('message_reactions')
            ->select('message_id', 'user_id')
            ->groupBy('message_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function (object $duplicate): void {
                $reactionIds = DB::table('message_reactions')
                    ->where('message_id', $duplicate->message_id)
                    ->where('user_id', $duplicate->user_id)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->pluck('id');

                $reactionIds->shift();

                if ($reactionIds->isNotEmpty()) {
                    DB::table('message_reactions')->whereIn('id', $reactionIds)->delete();
                }
            });

        // MySQL may be using the existing composite unique key to support the
        // message foreign key, so create a dedicated index before removing it.
        Schema::table('message_reactions', function (Blueprint $table) {
            $table->index('message_id');
        });

        Schema::table('message_reactions', function (Blueprint $table) {
            $table->dropUnique(['message_id', 'user_id', 'emoji']);
            $table->unique(['message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('message_reactions', function (Blueprint $table) {
            $table->dropUnique(['message_id', 'user_id']);
            $table->unique(['message_id', 'user_id', 'emoji']);
        });
    }
};
