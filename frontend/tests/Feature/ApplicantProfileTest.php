<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_registration_stores_general_and_specific_disability_categories(): void
    {
        Storage::fake('local');

        $response = $this->post(route('register.store'), [
            'account_type' => 'pwd_applicant',
            'first_name' => 'Ana',
            'last_name' => 'Cruz',
            'gender' => 'female',
            'age' => 25,
            'birthdate' => '2000-06-15',
            'disability' => 'Visual Disability',
            'disability_category' => 'Visually Impaired',
            'contact_number' => '9123456789',
            'street_address' => 'Salitran I',
            'city' => 'Dasmarinas',
            'pwd_id' => UploadedFile::fake()->create('pwd-id.pdf', 100, 'application/pdf'),
            'final_confirmation' => '1',
            'email' => 'ana.cruz@example.com',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ]);

        $response->assertRedirect(route('register.verification'));
        $this->assertDatabaseHas('users', [
            'email' => 'ana.cruz@example.com',
            'disability' => 'Visual Disability',
            'disability_category' => 'Visually Impaired',
        ]);
    }

    public function test_approved_applicant_can_update_a_ten_digit_contact_profile(): void
    {
        config(['broadcasting.default' => 'null']);

        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
            'first_name' => 'Ana',
            'last_name' => 'Cruz',
            'disability' => 'Physical Disability',
            'disability_category' => 'Physical Disability',
            'street_address' => 'Salitran I',
            'city' => 'Dasmarinas',
        ]);

        $response = $this
            ->actingAs($applicant)
            ->patchJson(route('applicant.profile.update'), [
                'first_name' => 'Ana',
                'last_name' => 'Cruz',
                'disability' => 'Visual Disability',
                'disability_category' => 'Visually Impaired',
                'contact_number' => '9123456789',
                'street_address' => 'Salitran I',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('profile.disability', 'Visual Disability — Visually Impaired')
            ->assertJsonPath('profile.general_disability_category', 'Visual Disability')
            ->assertJsonPath('profile.disability_category', 'Visually Impaired')
            ->assertJsonPath('profile.contact_number', '9123456789');

        $this->assertDatabaseHas('users', [
            'id' => $applicant->id,
            'contact_number' => '9123456789',
            'disability' => 'Visual Disability',
            'disability_category' => 'Visually Impaired',
        ]);
    }

    public function test_approved_applicant_can_upload_a_profile_photo(): void
    {
        Storage::fake('public');
        config(['broadcasting.default' => 'null']);

        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);

        $response = $this
            ->actingAs($applicant)
            ->patchJson(route('applicant.profile.update'), [
                'first_name' => 'Mika',
                'last_name' => 'Sina',
                'disability' => 'Physical Disability',
                'disability_category' => 'Physical Disability',
                'contact_number' => '9123456789',
                'street_address' => 'Paliparan I',
                'profile_photo' => UploadedFile::fake()->createWithContent(
                    'profile.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScLhWQAAAABJRU5ErkJggg=='),
                ),
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('profile.full_name', 'Mika Sina');

        $profilePhotoPath = $applicant->fresh()->profile_photo_path;

        $this->assertNotNull($profilePhotoPath);
        Storage::disk('public')->assertExists($profilePhotoPath);
        $this->assertStringContainsString('/storage/profile-photos/', $response->json('profile.photo_url'));
    }

    public function test_contact_number_must_contain_exactly_ten_digits(): void
    {
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);

        $this
            ->actingAs($applicant)
            ->patchJson(route('applicant.profile.update'), [
                'first_name' => 'Ana',
                'last_name' => 'Cruz',
                'disability' => 'Physical Disability',
                'disability_category' => 'Physical Disability',
                'contact_number' => '91234hello',
                'street_address' => 'Salitran I',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('contact_number');
    }
}
