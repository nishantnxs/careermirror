@extends('layouts.marketing')

@section('title', 'Messages')

@section('content')
<section class="candidate-panel">
    <div class="container">
        <div class="mb-4">
            <h1 class="panel-title">Messages</h1>
            <p class="panel-subtitle">
                Conversations with employers
                @if ($unreadCount > 0)
                    · {{ $unreadCount }} unread
                @endif
            </p>
        </div>

        <div class="panel-card">
            @forelse ($conversations as $conversation)
                @php($unread = $conversation->unreadCountForCandidate())
                <a href="{{ route('candidate.messages.show', $conversation) }}"
                   class="application-card d-flex align-items-start justify-content-between text-decoration-none {{ $loop->last ? '' : 'mb-0' }}"
                   style="border-radius: 0; box-shadow: none; border-left: 0; border-right: 0; {{ $loop->first ? 'border-top: 0;' : '' }} {{ $loop->last ? 'border-bottom: 0;' : '' }} {{ $unread > 0 ? 'background:#f7fcfc;' : '' }}">
                    <div class="application-info">
                        <h3 class="application-title mb-1">
                            {{ $conversation->employer?->company_name ?? 'Employer' }}
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
                    <p class="panel-subtitle mb-0">No messages yet. Employers can reach you here after you apply for jobs.</p>
                </div>
            @endforelse
        </div>

        @if ($conversations->hasPages())
            <div class="mt-4">{{ $conversations->links() }}</div>
        @endif
    </div>
</section>
@endsection
