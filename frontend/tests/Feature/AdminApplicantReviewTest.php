<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminApplicantReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_approve_a_pwd_applicant(): void
    {
        $admin = User::factory()->create([
            'account_type' => 'admin',
        ]);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'declined',
            'applicant_review_notes' => 'The previous upload was unreadable.',
            'applicant_reviewed_at' => null,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.applicants.approve', $applicant));

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', "{$applicant->name} has been approved.");

        $applicant->refresh();

        $this->assertSame('approved', $applicant->applicant_review_status);
        $this->assertNull($applicant->applicant_review_notes);
        $this->assertNotNull($applicant->applicant_reviewed_at);
    }

    public function test_authenticated_admin_can_reject_a_pwd_applicant_with_a_reason(): void
    {
        $admin = User::factory()->create([
            'account_type' => 'admin',
        ]);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'pending',
            'applicant_review_notes' => null,
            'applicant_reviewed_at' => null,
        ]);
        $reason = 'The uploaded PWD ID is unreadable.';

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.applicants.decline', $applicant), [
                'applicant_review_notes' => $reason,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', "{$applicant->name} has been declined.");

        $applicant->refresh();

        $this->assertSame('declined', $applicant->applicant_review_status);
        $this->assertSame($reason, $applicant->applicant_review_notes);
        $this->assertNotNull($applicant->applicant_reviewed_at);
    }

    public function test_non_admin_cannot_approve_another_applicant(): void
    {
        $nonAdmin = User::factory()->create([
            'account_type' => 'employer',
        ]);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'pending',
            'applicant_review_notes' => null,
            'applicant_reviewed_at' => null,
        ]);

        $response = $this
            ->actingAs($nonAdmin)
            ->post(route('admin.applicants.approve', $applicant));

        $response->assertForbidden();

        $applicant->refresh();

        $this->assertSame('pending', $applicant->applicant_review_status);
        $this->assertNull($applicant->applicant_review_notes);
        $this->assertNull($applicant->applicant_reviewed_at);
    }

    public function test_rejection_without_a_reason_returns_a_validation_error_and_leaves_applicant_pending(): void
    {
        $admin = User::factory()->create([
            'account_type' => 'admin',
        ]);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'pending',
            'applicant_review_notes' => null,
            'applicant_reviewed_at' => null,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->post(route('admin.applicants.decline', $applicant), [
                'applicant_review_notes' => '',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasErrors('applicant_review_notes');

        $applicant->refresh();

        $this->assertSame('pending', $applicant->applicant_review_status);
        $this->assertNull($applicant->applicant_review_notes);
        $this->assertNull($applicant->applicant_reviewed_at);
    }

    public function test_authenticated_admin_can_update_an_applicant_from_the_detail_drawer(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'applicant_review_status' => 'approved',
        ]);

        $response = $this
            ->actingAs($admin)
            ->patchJson(route('admin.applicants.update', $applicant), [
                'name' => 'Updated Applicant',
                'email' => 'updated.applicant@example.com',
                'contact_number' => '09171234567',
                'disability' => 'Visual Disability',
                'age' => 25,
                'birthdate' => '2001-06-15',
                'street_address' => 'Burol Main',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('applicant.name', 'Updated Applicant')
            ->assertJsonPath('applicant.contact', '09171234567');

        $applicant->refresh();

        $this->assertSame('Updated Applicant', $applicant->name);
        $this->assertSame('updated.applicant@example.com', $applicant->email);
        $this->assertSame('Visual Disability', $applicant->disability);
        $this->assertSame(25, $applicant->age);
    }

    public function test_only_an_admin_can_download_a_private_pwd_id(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('pwd-verifications/private-id.pdf', 'private-id');

        $admin = User::factory()->create(['account_type' => 'admin']);
        $applicant = User::factory()->create([
            'account_type' => 'pwd_applicant',
            'pwd_id_path' => 'pwd-verifications/private-id.pdf',
        ]);

        $this->actingAs($applicant)
            ->get(route('admin.applicants.pwd-id', $applicant))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.applicants.pwd-id', $applicant))
            ->assertOk()
            ->assertDownload('pwd-id-'.$applicant->id.'.pdf');
    }
}
