@extends('layouts.marketing')

@section('title', $conversation->displaySubject())

@section('content')
<section class="employer-panel">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h1 class="panel-title">{{ $conversation->candidate?->name ?? 'Candidate' }}</h1>
                        <p class="panel-subtitle">
                            {{ $conversation->displaySubject() }}
                            @if ($conversation->candidate?->email)
                                · {{ $conversation->candidate->email }}
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('employer.messages.index') }}" class="btn btn-outline-secondary">Inbox</a>
                </div>

                <div class="panel-card mb-4">
                    <div class="panel-card-body"
                         data-message-thread
                         data-updates-url="{{ route('employer.messages.updates', $conversation) }}"
                         data-last-id="{{ $lastMessageId }}"
                         style="max-height:420px; overflow:auto">
                        @forelse ($messages as $message)
                            @php($mine = $message->isFromEmployer())
                            <div class="mb-3 message-row {{ $mine ? 'text-end' : '' }}" data-message-id="{{ $message->id }}">
                                <div class="d-inline-block text-start p-3 rounded-3 {{ $mine ? 'message-mine' : 'message-theirs' }}"
                                     style="max-width: min(520px, 90%)">
                                    <div class="small mb-1 {{ $mine ? 'opacity-75' : 'text-secondary' }}">
                                        {{ $mine ? 'You' : $message->senderName() }}
                                        · {{ $message->created_at->format('d M Y, h:i A') }}
                                    </div>
                                    <div style="white-space: pre-wrap">{{ $message->body }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="panel-subtitle text-center mb-0" data-empty-thread>No messages in this conversation.</p>
                        @endforelse
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-card-body">
                        <form method="POST"
                              action="{{ route('employer.messages.reply', $conversation) }}"
                              data-message-form
                              data-send-url="{{ route('employer.messages.reply', $conversation) }}">
                            @csrf
                            <label for="body" class="form-label">Reply</label>
                            <textarea name="body" id="body" rows="4" class="form-control @error('body') is-invalid @enderror"
                                      required placeholder="Write your reply...">{{ old('body') }}</textarea>
                            @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="text-danger small mt-2" data-message-error hidden></div>
                            <button type="submit" class="btn btn-primary mt-3">Send reply</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
