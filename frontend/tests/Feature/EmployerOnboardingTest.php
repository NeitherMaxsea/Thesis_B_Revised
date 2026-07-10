<?php

namespace Tests\Feature;

use App\Models\EmployerDocument;
use App\Models\User;
use App\Services\EmployerDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployerOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_registration_requires_and_stores_all_business_documents(): void
    {
        Storage::fake('local');
        $response = $this->post(route('register.store'), [
            'account_type' => 'employer',
            'company_name' => 'Inclusive Works Inc.',
            'employer_contact_name' => 'Maria Santos',
            'employer_contact_number' => '9123456789',
            'dole_certificate' => UploadedFile::fake()->create('dole.pdf', 100, 'application/pdf'),
            'bir_certificate' => UploadedFile::fake()->create('bir.pdf', 100, 'application/pdf'),
            'business_permit' => UploadedFile::fake()->create('business.pdf', 100, 'application/pdf'),
            'dti_certificate' => UploadedFile::fake()->create('dti.pdf', 100, 'application/pdf'),
            'email' => 'employer@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'final_confirmation' => '1',
        ]);

        $response->assertRedirect(route('register.verification'));

        $employer = User::where('email', 'employer@example.test')->firstOrFail();
        $this->assertSame('employer', $employer->account_type);
        $this->assertSame('Inclusive Works Inc.', $employer->name);
        $this->assertSame(4, EmployerDocument::where('user_id', $employer->id)->count());
        $this->assertSame(now()->addDays((int) config('employer_documents.validity_days'))->toDateString(), EmployerDocument::where('user_id', $employer->id)->firstOrFail()->expires_at->toDateString());
    }

    public function test_expired_business_document_pauses_job_posting(): void
    {
        $employer = User::factory()->create([
            'account_type' => 'employer',
            'employer_document_status' => 'valid',
        ]);

        EmployerDocument::create([
            'user_id' => $employer->id,
            'document_type' => 'DOLE Certificate',
            'file_path' => 'employer-documents/dole.pdf',
            'expires_at' => now()->subDay(),
            'status' => 'valid',
        ]);

        app(EmployerDocumentService::class)->refreshStatus($employer);

        $this->actingAs($employer)
            ->get(route('employer.dashboard'))
            ->assertOk()
            ->assertSee('Job posting is paused.');
    }

    public function test_valid_employer_can_publish_a_job_post(): void
    {
        config(['broadcasting.default' => 'null']);

        $employer = User::factory()->create([
            'account_type' => 'employer',
            'employer_document_status' => 'valid',
        ]);

        foreach (['DOLE Certificate', 'BIR Certificate', 'Business Permit', 'DTI Certificate'] as $documentType) {
            EmployerDocument::create([
                'user_id' => $employer->id,
                'document_type' => $documentType,
                'file_path' => 'employer-documents/test.pdf',
                'expires_at' => now()->addYear(),
                'status' => 'valid',
            ]);
        }

        $this
            ->actingAs($employer)
            ->postJson(route('employer.jobs.store'), [
                'title' => 'Accessible Customer Support',
                'location' => 'Dasmarinas, Cavite',
                'employment_type' => 'full_time',
                'vacancies' => 2,
                'salary_min' => 15000,
                'salary_max' => 18000,
                'description' => 'Support customers through accessible communication.',
                'accommodations' => 'Flexible breaks and accessible workstation.',
                'application_requirements' => 'Submit a current resume and reply to the secure message with your availability.',
            ])
            ->assertCreated()
            ->assertJsonPath('job.title', 'Accessible Customer Support');

        $this->assertDatabaseHas('jobs', [
            'user_id' => $employer->id,
            'title' => 'Accessible Customer Support',
            'application_requirements' => 'Submit a current resume and reply to the secure message with your availability.',
        ]);
    }

    public function test_employer_can_renew_an_expired_document_without_entering_an_expiry_date(): void
    {
        Storage::fake('local');
        $employer = User::factory()->create([
            'account_type' => 'employer',
            'employer_document_status' => 'valid',
        ]);

        foreach (EmployerDocumentService::REQUIRED_DOCUMENT_TYPES as $index => $documentType) {
            EmployerDocument::create([
                'user_id' => $employer->id,
                'document_type' => $documentType,
                'file_path' => 'employer-documents/old-'.$index.'.pdf',
                'expires_at' => $index === 0 ? now()->subDay() : now()->addYear(),
                'status' => 'valid',
            ]);
        }
        $expiredDocument = $employer->employerDocuments()->where('document_type', 'DOLE Certificate')->firstOrFail();

        $this->actingAs($employer)
            ->post(route('employer.documents.renew', $expiredDocument), [
                'document' => UploadedFile::fake()->create('renewed-dole.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('employer.dashboard'));

        $expiredDocument->refresh();
        $employer->refresh();

        $this->assertSame('valid', $expiredDocument->status);
        $this->assertSame(now()->addDays((int) config('employer_documents.validity_days'))->toDateString(), $expiredDocument->expires_at->toDateString());
        $this->assertSame('valid', $employer->employer_document_status);
    }
}
