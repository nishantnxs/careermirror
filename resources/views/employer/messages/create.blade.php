@extends('layouts.marketing')

@section('title', 'New message')

@section('content')
<section class="employer-panel">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h1 class="panel-title">New message</h1>
                        <p class="panel-subtitle">Start a conversation with a candidate who applied to your jobs.</p>
                    </div>
                    <a href="{{ route('employer.messages.index') }}" class="btn btn-outline-secondary">Inbox</a>
                </div>

                <div class="panel-card">
                    <div class="panel-card-body">
                        @if ($applications->isEmpty())
                            <p class="panel-subtitle mb-0">
                                No applicants yet. Once candidates apply to your jobs, you can message them here.
                            </p>
                        @else
                            <form method="POST" action="{{ route('employer.messages.store') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="job_application_id" class="form-label">Candidate / application</label>
                                    <select name="job_application_id" id="job_application_id"
                                            class="form-select @error('candidate_id') is-invalid @enderror @error('job_application_id') is-invalid @enderror"
                                            required>
                                        <option value="">Select applicant</option>
                                        @foreach ($applications as $application)
                                            <option value="{{ $application->id }}"
                                                    data-candidate-id="{{ $application->candidate_id }}"
                                                    @selected((string) $selectedApplicationId === (string) $application->id
                                                        || (string) $selectedCandidateId === (string) $application->candidate_id)>
                                                {{ $application->candidate?->name }}
                                                — {{ $application->jobPosting?->title }}
                                                ({{ $application->status->label() }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="candidate_id" id="candidate_id" value="{{ $selectedCandidateId }}">
                                    @error('candidate_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    @error('job_application_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror"
                                           value="{{ old('subject') }}" placeholder="Optional">
                                    @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="body" class="form-label">Message</label>
                                    <textarea name="body" id="body" rows="6" class="form-control @error('body') is-invalid @enderror"
                                              required>{{ old('body') }}</textarea>
                                    @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">Send message</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        const select = document.getElementById('job_application_id');
        const candidateInput = document.getElementById('candidate_id');
        if (!select || !candidateInput) return;

        const sync = () => {
            const option = select.options[select.selectedIndex];
            candidateInput.value = option?.dataset?.candidateId || '';
        };

        select.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
