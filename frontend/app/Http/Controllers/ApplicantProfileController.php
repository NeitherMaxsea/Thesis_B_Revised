<?php

namespace App\Http\Controllers;

use App\Events\ProfileUpdated;
use App\Models\User;
use App\Services\RealtimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicantProfileController extends Controller
{
    private const DASMA_ADDRESSES = [
        'Burol Main', 'Burol I', 'Burol II', 'Burol III', 'Datu Esmael (Bago-A-Ingud)',
        'Emmanuel Bergado I', 'Emmanuel Bergado II', 'Fatima I', 'Fatima II', 'Fatima III',
        'Barangay H-2 (Santa Veronica)', 'Langkaan I (Humayao)', 'Langkaan II', 'Luzviminda I',
        'Luzviminda II', 'Paliparan I', 'Paliparan II', 'Paliparan III', 'Sabang', 'Salawag',
        'Saint Peter I', 'Saint Peter II', 'Salitran I', 'Salitran II', 'Salitran III', 'Salitran IV',
        'Sampaloc I (Pala-Pala)', 'Sampaloc II (Bucal/Malinta)', 'Sampaloc III (Piela)',
        'Sampaloc IV (Talisayan/Bautista)', 'Sampaloc V (New Era)', 'San Agustin I', 'San Agustin II',
        'San Agustin III', 'San Andres I', 'San Antonio De Padua I', 'San Antonio De Padua II',
        'San Dionisio', 'San Esteban', 'San Francisco I', 'San Francisco II', 'San Isidro Labrador I',
        'San Jose', 'San Juan', 'San Lorenzo Ruiz I', 'San Luis I', 'San Manuel I', 'San Mateo',
        'San Miguel I', 'San Nicolas I', 'San Roque', 'San Simon', 'Santa Cristina I', 'Santa Cruz I',
        'Santa Fe', 'Santa Lucia', 'Santa Maria', 'Santo Cristo', 'Santo Nino I', 'Victoria Reyes',
        'Zone I-B', 'Zone I', 'Zone II', 'Zone III', 'Zone IV',
    ];

    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $this->ensureApprovedApplicant($user);

        return view('dashboard.profile', [
            'user' => $user,
            'addresses' => self::DASMA_ADDRESSES,
            'disabilities' => config('applicant.disabilities'),
            'disabilityCategories' => config('applicant.disability_categories'),
        ]);
    }

    public function update(Request $request, RealtimeService $realtime): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->ensureApprovedApplicant($user);

        $disabilityCategories = config('applicant.disability_categories', []);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120', 'not_regex:/[0-9]/'],
            'last_name' => ['required', 'string', 'max:120', 'not_regex:/[0-9]/'],
            'disability' => ['required', Rule::in(array_keys($disabilityCategories))],
            'disability_category' => ['required', Rule::in($disabilityCategories[$request->input('disability')] ?? [])],
            'contact_number' => ['required', 'regex:/^\d{10}$/'],
            'street_address' => ['required', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'contact_number.regex' => 'Contact number must contain exactly 10 digits.',
            'profile_photo.max' => 'Profile photo must not be larger than 2 MB.',
        ]);

        $profilePhoto = $request->file('profile_photo');
        unset($validated['profile_photo']);
        $user->fill($validated);
        $user->name = trim("{$validated['first_name']} {$validated['last_name']}");

        if ($profilePhoto) {
            $previousPhotoPath = $user->profile_photo_path;
            $user->profile_photo_path = $profilePhoto->store('profile-photos', 'public');

            if ($previousPhotoPath) {
                Storage::disk('public')->delete($previousPhotoPath);
            }
        }

        $user->save();

        $realtime->broadcast(new ProfileUpdated($user));

        $payload = [
            'profile' => [
                'name' => $user->first_name,
                'full_name' => $user->name,
                'disability' => $user->disability_display,
                'general_disability_category' => $user->disability,
                'disability_category' => $user->disability_category,
                'email' => $user->email,
                'contact_number' => $user->contact_number,
                'street_address' => $user->street_address,
                'city' => $user->city,
                'photo_url' => $user->profile_photo_path
                    ? Storage::disk('public')->url($user->profile_photo_path)
                    : null,
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return redirect()
            ->route('applicant.profile')
            ->with('status', 'Your profile has been updated.');
    }

    private function ensureApprovedApplicant(User $user): void
    {
        abort_unless(
            $user->account_type === 'pwd_applicant' && $user->applicant_review_status === 'approved',
            403
        );
    }
}
