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

        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => 'System Admin',
                'account_type' => 'admin',
                'applicant_review_status' => 'approved',
                'email_verified_at' => now(),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin12345!')),
            ]
        );
    }
}
