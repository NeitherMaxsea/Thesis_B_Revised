<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('context_key', 100)->default('direct')->after('second_user_id');
            $table->foreignId('job_id')->nullable()->after('context_key')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('job_application_id')->nullable()->after('job_id')->constrained('job_applications')->cascadeOnDelete();
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique('conversations_first_user_id_second_user_id_unique');
        });

        $applications = DB::table('job_applications')
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->orderBy('job_applications.id')
            ->get([
                'job_applications.id',
                'job_applications.job_id',
                'job_applications.applicant_id',
                'job_applications.created_at',
                'job_applications.updated_at',
                'jobs.user_id as employer_id',
            ]);

        foreach ($applications as $application) {
            $firstUserId = min((int) $application->applicant_id, (int) $application->employer_id);
            $secondUserId = max((int) $application->applicant_id, (int) $application->employer_id);
            $contextKey = "job_application:{$application->id}";

            // Reuse the legacy participant-pair thread for the pair's first
            // application so every existing message remains in place.
            $conversation = DB::table('conversations')
                ->where('first_user_id', $firstUserId)
                ->where('second_user_id', $secondUserId)
                ->where('context_key', 'direct')
                ->whereNull('job_application_id')
                ->oldest('id')
                ->first();

            if ($conversation) {
                DB::table('conversations')
                    ->where('id', $conversation->id)
                    ->update([
                        'context_key' => $contextKey,
                        'job_id' => $application->job_id,
                        'job_application_id' => $application->id,
                        'updated_at' => $application->updated_at ?? now(),
                    ]);

                continue;
            }

            // A pair may have applied to several jobs. Each application needs
            // its own context so messages can never be loaded for the wrong job.
            DB::table('conversations')->insert([
                'first_user_id' => $firstUserId,
                'second_user_id' => $secondUserId,
                'context_key' => $contextKey,
                'job_id' => $application->job_id,
                'job_application_id' => $application->id,
                'last_message_at' => null,
                'created_at' => $application->created_at ?? now(),
                'updated_at' => $application->updated_at ?? now(),
            ]);
        }

        Schema::table('conversations', function (Blueprint $table) {
            $table->unique('job_application_id');
            $table->unique(
                ['first_user_id', 'second_user_id', 'context_key'],
                'conversations_participants_context_unique'
            );
        });
    }

    public function down(): void
    {
        // The old schema allowed only one thread per participant pair. Merge
        // contextual threads before restoring that constraint during rollback.
        $duplicatePairs = DB::table('conversations')
            ->select('first_user_id', 'second_user_id')
            ->groupBy('first_user_id', 'second_user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicatePairs as $pair) {
            $conversationIds = DB::table('conversations')
                ->where('first_user_id', $pair->first_user_id)
                ->where('second_user_id', $pair->second_user_id)
                ->oldest('id')
                ->pluck('id');

            $keeperId = $conversationIds->shift();

            if ($keeperId === null || $conversationIds->isEmpty()) {
                continue;
            }

            DB::table('messages')
                ->whereIn('conversation_id', $conversationIds)
                ->update(['conversation_id' => $keeperId]);

            DB::table('conversations')->whereIn('id', $conversationIds)->delete();

            DB::table('conversations')
                ->where('id', $keeperId)
                ->update([
                    'last_message_at' => DB::table('messages')
                        ->where('conversation_id', $keeperId)
                        ->max('created_at'),
                    'updated_at' => now(),
                ]);
        }

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique('conversations_participants_context_unique');
            $table->dropUnique(['job_application_id']);
            $table->dropForeign(['job_application_id']);
            $table->dropForeign(['job_id']);
            $table->dropColumn(['context_key', 'job_id', 'job_application_id']);
            $table->unique(['first_user_id', 'second_user_id']);
        });
    }
};
