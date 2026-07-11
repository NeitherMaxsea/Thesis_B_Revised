<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_user_id',
        'second_user_id',
        'context_key',
        'job_id',
        'job_application_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function firstParticipant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'first_user_id');
    }

    public function secondParticipant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'second_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    /**
     * Kept as a semantic alias for the existing conversation-list view.
     * Application conversations have exactly one job application.
     */
    public function latestJobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function scopeForParticipant(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $query) use ($userId) {
            $query->where('first_user_id', $userId)
                ->orWhere('second_user_id', $userId);
        });
    }

    public function hasParticipant(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return (int) $this->first_user_id === (int) $userId
            || (int) $this->second_user_id === (int) $userId;
    }

    public function otherParticipant(User|int $user): ?User
    {
        $userId = $user instanceof User ? $user->id : $user;

        if ((int) $this->first_user_id === (int) $userId) {
            return $this->secondParticipant;
        }

        if ((int) $this->second_user_id === (int) $userId) {
            return $this->firstParticipant;
        }

        return null;
    }
}
