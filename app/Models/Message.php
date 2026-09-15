<?php

namespace App\Models;

use App\Enums\MessageSenderType;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'body',
        'read_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sender_type' => MessageSenderType::class,
            'read_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isFromCandidate(): bool
    {
        return $this->sender_type === MessageSenderType::Candidate;
    }

    public function isFromEmployer(): bool
    {
        return $this->sender_type === MessageSenderType::Employer;
    }

    public function senderName(): string
    {
        if ($this->isFromCandidate()) {
            return $this->conversation?->candidate?->name ?? 'Candidate';
        }

        return $this->conversation?->employer?->company_name
            ?? $this->conversation?->employer?->name
            ?? 'Employer';
    }
}
