<?php

namespace App\Http\Controllers\Employer;

use App\Enums\MessageSenderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\StartConversationRequest;
use App\Http\Requests\Employer\StoreMessageRequest;
use App\Models\Candidate;
use App\Models\Conversation;
use App\Models\JobApplication;
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
        $employer = $request->user('employer');

        $conversations = $employer->conversations()
            ->with(['candidate', 'jobPosting', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('employer.messages.index', [
            'conversations' => $conversations,
            'unreadCount' => $this->messaging->unreadCountForEmployer($employer),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => $this->messaging->unreadCountForEmployer($request->user('employer')),
        ]);
    }

    public function create(Request $request): View
    {
        $employer = $request->user('employer');

        $applications = JobApplication::query()
            ->with(['candidate', 'jobPosting'])
            ->whereHas('jobPosting', fn ($query) => $query->where('employer_id', $employer->id))
            ->latest('applied_at')
            ->get()
            ->unique('candidate_id')
            ->values();

        return view('employer.messages.create', [
            'applications' => $applications,
            'selectedCandidateId' => old('candidate_id', $request->integer('candidate_id') ?: null),
            'selectedApplicationId' => old('job_application_id', $request->integer('job_application_id') ?: null),
        ]);
    }

    public function store(StartConversationRequest $request): RedirectResponse
    {
        $employer = $request->user('employer');
        $candidate = Candidate::query()->findOrFail($request->integer('candidate_id'));
        $application = $request->filled('job_application_id')
            ? JobApplication::query()->find($request->integer('job_application_id'))
            : null;

        $conversation = $this->messaging->startFromEmployer(
            $employer,
            $candidate,
            $request->string('body')->toString(),
            $application?->jobPosting,
            $application,
            $request->filled('subject') ? $request->string('subject')->trim()->value() : null,
        );

        return redirect()
            ->route('employer.messages.show', $conversation)
            ->with('success', 'Message sent.');
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorizeConversation($request, $conversation);

        $this->messaging->markReadByEmployer($conversation);

        $conversation->load(['candidate', 'jobPosting', 'messages']);

        $messages = $conversation->messages()->orderBy('created_at')->orderBy('id')->get();

        return view('employer.messages.show', [
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
            MessageSenderType::Employer,
        );

        $this->messaging->markReadByEmployer($conversation);

        return response()->json([
            'messages' => $messages,
            'unread_count' => $this->messaging->unreadCountForEmployer($request->user('employer')),
        ]);
    }

    public function reply(StoreMessageRequest $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        $message = $this->messaging->replyAsEmployer(
            $conversation,
            $request->user('employer'),
            $request->string('body')->toString(),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->messaging->presentMessage($message, MessageSenderType::Employer),
                'unread_count' => $this->messaging->unreadCountForEmployer($request->user('employer')),
            ]);
        }

        return redirect()
            ->route('employer.messages.show', $conversation)
            ->with('success', 'Message sent.');
    }

    private function authorizeConversation(Request $request, Conversation $conversation): void
    {
        abort_unless($conversation->employer_id === $request->user('employer')->id, 404);
    }
}
