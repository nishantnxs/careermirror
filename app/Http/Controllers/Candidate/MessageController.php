<?php

namespace App\Http\Controllers\Candidate;

use App\Enums\MessageSenderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\StoreMessageRequest;
use App\Models\Conversation;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(public MessagingService $messaging) {}

    public function index(Request $request): View
    {
        $candidate = $request->user('candidate');

        $conversations = $candidate->conversations()
            ->with(['employer', 'jobPosting', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('candidate.messages.index', [
            'conversations' => $conversations,
            'unreadCount' => $this->messaging->unreadCountForCandidate($candidate),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => $this->messaging->unreadCountForCandidate($request->user('candidate')),
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorizeConversation($request, $conversation);

        $this->messaging->markReadByCandidate($conversation);

        $conversation->load(['employer', 'jobPosting', 'messages']);

        $messages = $conversation->messages()->orderBy('created_at')->orderBy('id')->get();

        return view('candidate.messages.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'lastMessageId' => (int) ($messages->last()?->id ?? 0),
        ]);
    }

    public function updates(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        $messages = $this->messaging->messagesAfter(
            $conversation,
            $request->integer('after_id'),
            MessageSenderType::Candidate,
        );

        $this->messaging->markReadByCandidate($conversation);

        return response()->json([
            'messages' => $messages,
            'unread_count' => $this->messaging->unreadCountForCandidate($request->user('candidate')),
        ]);
    }

    public function store(StoreMessageRequest $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        $message = $this->messaging->replyAsCandidate(
            $conversation,
            $request->user('candidate'),
            $request->string('body')->toString(),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->messaging->presentMessage($message, MessageSenderType::Candidate),
                'unread_count' => $this->messaging->unreadCountForCandidate($request->user('candidate')),
            ]);
        }

        return redirect()
            ->route('candidate.messages.show', $conversation)
            ->with('success', 'Message sent.');
    }

    private function authorizeConversation(Request $request, Conversation $conversation): void
    {
        abort_unless($conversation->candidate_id === $request->user('candidate')->id, 404);
    }
}
