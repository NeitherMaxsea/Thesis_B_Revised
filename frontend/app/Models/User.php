<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        // APPLICANT PROFILE: these fields are collected only from pwd_applicant registrations.
        'first_name',
        'last_name',
        'suffix',
        'gender',
        'age',
        'birthdate',
        'disability',
        'disability_category',
        'contact_number',
        'street_address',
        'city',
        'latitude',
        'longitude',
        'pwd_id_path',
        'company_name',
        'employer_contact_name',
        'employer_contact_number',
        'employer_document_status',
        // ADMIN REVIEW: this status and note control an applicant's access to their dashboard.
        'applicant_review_status',
        'applicant_review_notes',
        'applicant_reviewed_at',
        'profile_photo_path',
        'email',
        'account_type',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate' => 'date',
        'applicant_reviewed_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function conversationsStarted(): HasMany
    {
        return $this->hasMany(Conversation::class, 'first_user_id');
    }

    public function conversationsReceived(): HasMany
    {
        return $this->hasMany(Conversation::class, 'second_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function employerDocuments(): HasMany
    {
        return $this->hasMany(EmployerDocument::class);
    }

    public function getDisabilityDisplayAttribute(): ?string
    {
        if (blank($this->disability)) {
            return null;
        }

        return filled($this->disability_category)
            ? "{$this->disability} — {$this->disability_category}"
            : $this->disability;
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? Storage::disk('public')->url($this->profile_photo_path)
            : null;
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'applicant_id');
    }
}
