<?php

namespace App\Services;

use App\Enums\MessageSenderType;
use App\Models\Candidate;
use App\Models\Conversation;
use App\Models\Employer;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Message;
use App\Notifications\Messaging\NewMessageNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MessagingService
{
    public function startFromEmployer(
        Employer $employer,
        Candidate $candidate,
        string $body,
        ?JobPosting $job = null,
        ?JobApplication $application = null,
        ?string $subject = null,
    ): Conversation {
        if ($application && ($application->candidate_id !== $candidate->id || $application->jobPosting?->employer_id !== $employer->id)) {
            throw ValidationException::withMessages([
                'candidate_id' => 'You can only message candidates who applied to your jobs.',
            ]);
        }

        if (! $application && ! $this->candidateAppliedToEmployer($employer, $candidate)) {
            throw ValidationException::withMessages([
                'candidate_id' => 'You can only message candidates who applied to your jobs.',
            ]);
        }

        return DB::transaction(function () use ($employer, $candidate, $body, $job, $application, $subject) {
            $conversation = Conversation::query()->firstOrCreate(
                [
                    'employer_id' => $employer->id,
                    'candidate_id' => $candidate->id,
                ],
                [
                    'job_posting_id' => $job?->id ?? $application?->job_posting_id,
                    'job_application_id' => $application?->id,
                    'subject' => $subject,
                    'employer_last_read_at' => now(),
                ],
            );

            if ($conversation->wasRecentlyCreated === false) {
                $conversation->fill(array_filter([
                    'job_posting_id' => $conversation->job_posting_id ?: ($job?->id ?? $application?->job_posting_id),
                    'job_application_id' => $conversation->job_application_id ?: $application?->id,
                    'subject' => $conversation->subject ?: $subject,
                ], fn ($value) => $value !== null))->save();
            }

            $this->postMessage($conversation, MessageSenderType::Employer, $employer->id, $body);

            return $conversation->fresh(['candidate', 'employer', 'jobPosting', 'latestMessage']);
        });
    }

    public function replyAsCandidate(Conversation $conversation, Candidate $candidate, string $body): Message
    {
        if ($conversation->candidate_id !== $candidate->id) {
            abort(404);
        }

        return $this->postMessage($conversation, MessageSenderType::Candidate, $candidate->id, $body);
    }

    public function replyAsEmployer(Conversation $conversation, Employer $employer, string $body): Message
    {
        if ($conversation->employer_id !== $employer->id) {
            abort(404);
        }

        return $this->postMessage($conversation, MessageSenderType::Employer, $employer->id, $body);
    }

    public function markReadByCandidate(Conversation $conversation): void
    {
        $conversation->update(['candidate_last_read_at' => now()]);

        $conversation->messages()
            ->where('sender_type', MessageSenderType::Employer)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markReadByEmployer(Conversation $conversation): void
    {
        $conversation->update(['employer_last_read_at' => now()]);

        $conversation->messages()
            ->where('sender_type', MessageSenderType::Candidate)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function unreadCountForCandidate(Candidate $candidate): int
    {
        return Message::query()
            ->where('sender_type', MessageSenderType::Employer)
            ->whereHas('conversation', function ($query) use ($candidate) {
                $query->where('candidate_id', $candidate->id)
                    ->where(function ($query) {
                        $query->whereNull('candidate_last_read_at')
                            ->orWhereColumn('messages.created_at', '>', 'conversations.candidate_last_read_at');
                    });
            })
            ->count();
    }

    public function unreadCountForEmployer(Employer $employer): int
    {
        return Message::query()
            ->where('sender_type', MessageSenderType::Candidate)
            ->whereHas('conversation', function ($query) use ($employer) {
                $query->where('employer_id', $employer->id)
                    ->where(function ($query) {
                        $query->whereNull('employer_last_read_at')
                            ->orWhereColumn('messages.created_at', '>', 'conversations.employer_last_read_at');
                    });
            })
            ->count();
    }

    /**
     * @return list<array{id: int, body: string, mine: bool, sender_label: string, created_at: string}>
     */
    public function messagesAfter(Conversation $conversation, int $afterId, MessageSenderType $viewer): array
    {
        return $conversation->messages()
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->get()
            ->map(fn (Message $message) => $this->presentMessage($message, $viewer))
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, body: string, mine: bool, sender_label: string, created_at: string}
     */
    public function presentMessage(Message $message, MessageSenderType $viewer): array
    {
        $mine = $message->sender_type === $viewer;

        return [
            'id' => $message->id,
            'body' => $message->body,
            'mine' => $mine,
            'sender_label' => $mine ? 'You' : $message->senderName(),
            'created_at' => $message->created_at?->format('d M Y, h:i A') ?? '',
        ];
    }

    private function postMessage(
        Conversation $conversation,
        MessageSenderType $senderType,
        int $senderId,
        string $body,
    ): Message {
        $body = trim($body);

        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => 'Message cannot be empty.',
            ]);
        }

        $message = $conversation->messages()->create([
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'body' => $body,
        ]);

        $conversation->forceFill([
            'last_message_at' => $message->created_at,
            $senderType === MessageSenderType::Employer ? 'employer_last_read_at' : 'candidate_last_read_at' => $message->created_at,
        ])->save();

        $conversation->loadMissing(['candidate', 'employer', 'jobPosting']);

        $recipient = $senderType === MessageSenderType::Employer
            ? $conversation->candidate
            : $conversation->employer;

        if ($recipient) {
            $recipient->notify(new NewMessageNotification($message));
        }

        return $message->fresh();
    }

    private function candidateAppliedToEmployer(Employer $employer, Candidate $candidate): bool
    {
        return JobApplication::query()
            ->where('candidate_id', $candidate->id)
            ->whereHas('jobPosting', fn ($query) => $query->where('employer_id', $employer->id))
            ->exists();
    }
}
