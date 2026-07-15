<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobApplication extends Model
{
    use HasFactory;

    /**
     * The ordered stages shown to both people in an application conversation.
     *
     * `applied` is retained as the first stored status so existing
     * applications continue to appear as "Employer review pending".
     *
     * @var array<string, array{number:int,title:string,short_label:string,description:string}>
     */
    public const HIRING_STAGES = [
        'applied' => [
            'number' => 1,
            'title' => 'Employer review pending',
            'short_label' => 'Employer review',
            'description' => 'Your application is awaiting an employer review.',
        ],
        'application_review' => [
            'number' => 2,
            'title' => 'Application review',
            'short_label' => 'Application review',
            'description' => 'The employer is reviewing your application.',
        ],
        'interview_schedule' => [
            'number' => 3,
            'title' => 'Interview schedule',
            'short_label' => 'Interview',
            'description' => 'The employer will coordinate an interview schedule with you.',
        ],
        'pre_employment_requirements' => [
            'number' => 4,
            'title' => 'Pre-employment requirements',
            'short_label' => 'Requirements',
            'description' => 'The employer may request documents or other requirements.',
        ],
        'employment_offer' => [
            'number' => 5,
            'title' => 'Employment offer',
            'short_label' => 'Job offer',
            'description' => 'The employer is ready to make a hiring decision.',
        ],
    ];

    protected $fillable = [
        'job_id',
        'applicant_id',
        'status',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    /** @return array<string, array{number:int,title:string,short_label:string,description:string}> */
    public static function hiringStages(): array
    {
        return self::HIRING_STAGES;
    }

    public function nextHiringStage(): ?string
    {
        $stages = array_keys(self::HIRING_STAGES);
        $currentIndex = array_search($this->hiringStagePayload()['status'], $stages, true);

        if ($currentIndex === false) {
            return null;
        }

        return $stages[$currentIndex + 1] ?? null;
    }

    /**
     * @return array{status:string,number:int,title:string,short_label:string,description:string}
     */
    public function hiringStagePayload(): array
    {
        $status = array_key_exists($this->status, self::HIRING_STAGES)
            ? $this->status
            : 'applied';

        return [
            'status' => $status,
            ...self::HIRING_STAGES[$status],
        ];
    }
}
