<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_applicant_can_update_a_ten_digit_contact_profile(): void
    {
        config(['broadcasting.default' => 'null']);

        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
            'first_name' => 'Ana',
            'last_name' => 'Cruz',
            'disability' => 'Physical Disability',
            'street_address' => 'Salitran I',
            'city' => 'Dasmarinas',
        ]);

        $response = $this
            ->actingAs($applicant)
            ->patchJson(route('applicant.profile.update'), [
                'first_name' => 'Ana',
                'last_name' => 'Cruz',
                'disability' => 'Visually Impaired',
                'contact_number' => '9123456789',
                'street_address' => 'Salitran I',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('profile.disability', 'Visually Impaired')
            ->assertJsonPath('profile.contact_number', '9123456789');

        $this->assertDatabaseHas('users', [
            'id' => $applicant->id,
            'contact_number' => '9123456789',
        ]);
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
                'contact_number' => '91234hello',
                'street_address' => 'Salitran I',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('contact_number');
    }
}
