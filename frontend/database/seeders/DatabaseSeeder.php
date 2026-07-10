<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if (app()->environment('production') && (! filled($adminEmail) || ! filled($adminPassword))) {
            throw new \RuntimeException('Set unique ADMIN_EMAIL and ADMIN_PASSWORD values before running the production seeder.');
        }

        User::updateOrCreate(
            ['email' => $adminEmail ?: 'admin@example.com'],
            [
                'name' => 'System Admin',
                'account_type' => 'admin',
                'applicant_review_status' => 'approved',
                'email_verified_at' => now(),
                'password' => Hash::make($adminPassword ?: 'Admin12345!'),
            ]
        );
    }
}
