<?php

namespace App\Notifications\Messaging;

use App\Enums\MessageSenderType;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public Message $message)
    {
        $this->message->loadMissing(['conversation.candidate', 'conversation.employer', 'conversation.jobPosting']);
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $conversation = $this->message->conversation;
        $url = $this->inboxUrl($notifiable, $conversation->id);
        $from = $this->message->senderName();

        return (new MailMessage)
            ->subject('New message from '.$from)
            ->greeting('Hello '.$this->recipientName($notifiable).',')
            ->line($from.' sent you a new message'.($conversation->displaySubject() !== 'Conversation' ? ' about "'.$conversation->displaySubject().'"' : '').'.')
            ->line('"'.Str::limit($this->message->body, 160).'"')
            ->action('Open conversation', $url)
            ->line('You can reply from your CareerMirror inbox.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $conversation = $this->message->conversation;

        return [
            'conversation_id' => $conversation->id,
            'message_id' => $this->message->id,
            'sender_type' => $this->message->sender_type->value,
            'sender_name' => $this->message->senderName(),
            'subject' => $conversation->displaySubject(),
            'preview' => Str::limit($this->message->body, 120),
            'url' => $this->inboxUrl($notifiable, $conversation->id),
        ];
    }

    private function inboxUrl(object $notifiable, int $conversationId): string
    {
        if ($this->message->sender_type === MessageSenderType::Employer) {
            return url(route('candidate.messages.show', $conversationId, false));
        }

        return url(route('employer.messages.show', $conversationId, false));
    }

    private function recipientName(object $notifiable): string
    {
        return $notifiable->name
            ?? $notifiable->company_name
            ?? 'there';
    }
}
