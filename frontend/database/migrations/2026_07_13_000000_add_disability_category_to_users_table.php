<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('disability_category')->nullable()->after('disability');
        });

        $legacyCategories = [
            'Physical Disability' => [
                'Physical Disability',
                'Lower Limb Amputation/Deformity and Wheelchair Users',
                'Upper Limb Amputation/Deformity',
                'Dwarfism',
                'Epilepsy',
                'Cancer Survivors and Rare Disease Individuals',
                'Chronic Kidney Disease / Dialysis Patient',
                'Diabetes with Complications',
                'Severe Heart Disease',
            ],
            'Hearing Disability' => ['Deaf and Hard of Hearing Individuals'],
            'Visual Disability' => ['Visually Impaired'],
            'Speech and Language Disability' => ['Speech Impairment'],
            'Psychosocial Disability' => ['Mental and Psychosocial Disability Individuals'],
            'Intellectual Disability' => ['Intellectual Disability'],
            'Learning Disability' => ['Learning Disability', 'Learning Disability (Dyslexic)'],
        ];

        foreach ($legacyCategories as $generalCategory => $specificCategories) {
            $users = DB::table('users')
                ->where('account_type', 'pwd_applicant')
                ->whereIn('disability', $specificCategories)
                ->get(['id', 'disability']);

            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'disability' => $generalCategory,
                    'disability_category' => $user->disability,
                ]);
            }
        }
    }

    public function down(): void
    {
        $users = DB::table('users')
            ->whereNotNull('disability_category')
            ->get(['id', 'disability_category']);

        foreach ($users as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'disability' => $user->disability_category,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('disability_category');
        });
    }
};
