@extends('layouts.marketing')

@section('title', 'Messages')

@section('content')
<section class="employer-panel">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="panel-title">Messages</h1>
                <p class="panel-subtitle">
                    Conversations with candidates
                    @if ($unreadCount > 0)
                        · {{ $unreadCount }} unread
                    @endif
                </p>
            </div>
            <a href="{{ route('employer.messages.create') }}" class="btn btn-primary">New message</a>
        </div>

        <div class="panel-card">
            @forelse ($conversations as $conversation)
                @php($unread = $conversation->unreadCountForEmployer())
                <a href="{{ route('employer.messages.show', $conversation) }}"
                   class="application-card d-flex align-items-start justify-content-between text-decoration-none {{ $loop->last ? '' : 'mb-0' }}"
                   style="border-radius: 0; box-shadow: none; border-left: 0; border-right: 0; {{ $loop->first ? 'border-top: 0;' : '' }} {{ $loop->last ? 'border-bottom: 0;' : '' }} {{ $unread > 0 ? 'background:#f7fcfc;' : '' }}">
                    <div class="application-info">
                        <h3 class="application-title mb-1">
                            {{ $conversation->candidate?->name ?? 'Candidate' }}
                            @if ($unread > 0)
                                <span class="status-badge ms-1">{{ $unread }}</span>
                            @endif
                        </h3>
                        <p class="application-meta mb-1">{{ $conversation->displaySubject() }}</p>
                        <p class="application-meta mb-0">
                            {{ \Illuminate\Support\Str::limit($conversation->latestMessage?->body ?? 'No messages yet', 100) }}
                        </p>
                    </div>
                    <div class="application-meta text-nowrap">
                        {{ $conversation->last_message_at?->diffForHumans() }}
                    </div>
                </a>
            @empty
                <div class="panel-card-body text-center">
                    <p class="panel-subtitle mb-3">No conversations yet.</p>
                    <a href="{{ route('employer.messages.create') }}" class="btn btn-primary">Message a candidate</a>
                </div>
            @endforelse
        </div>

        @if ($conversations->hasPages())
            <div class="mt-4">{{ $conversations->links() }}</div>
        @endif
    </div>
</section>
@endsection
