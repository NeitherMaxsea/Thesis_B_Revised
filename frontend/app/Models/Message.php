<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    public const TYPE_TEXT = 'text';

    public const TYPE_REQUIREMENTS_CARD = 'requirements_card';

    public const TYPE_HIRING_ACTION = 'hiring_action';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'message_type',
        'metadata',
        'read_at',
        'attachment_path',
        'attachment_mime',
        'attachment_name',
        'attachment_size',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
