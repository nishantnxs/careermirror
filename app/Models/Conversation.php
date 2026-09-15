<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'candidate_id',
        'job_posting_id',
        'job_application_id',
        'subject',
        'last_message_at',
        'employer_last_read_at',
        'candidate_last_read_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'employer_last_read_at' => 'datetime',
            'candidate_last_read_at' => 'datetime',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadCountForCandidate(): int
    {
        $since = $this->candidate_last_read_at;

        return $this->messages()
            ->where('sender_type', 'employer')
            ->when($since, fn ($query) => $query->where('created_at', '>', $since))
            ->count();
    }

    public function unreadCountForEmployer(): int
    {
        $since = $this->employer_last_read_at;

        return $this->messages()
            ->where('sender_type', 'candidate')
            ->when($since, fn ($query) => $query->where('created_at', '>', $since))
            ->count();
    }

    public function displaySubject(): string
    {
        if (filled($this->subject)) {
            return $this->subject;
        }

        if ($this->jobPosting) {
            return 'Re: '.$this->jobPosting->title;
        }

        return 'Conversation';
    }
}
