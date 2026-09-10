@extends('admin.layouts.app')

@section('title', $candidate->name)
@section('heading', $candidate->name)
@section('subheading', 'Candidate profile and related activity.')

@section('actions')
    <a href="{{ route('admin.candidates.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back to candidates
    </a>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header">Profile</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Name</dt>
                        <dd class="col-sm-8">{{ $candidate->name }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Email</dt>
                        <dd class="col-sm-8">{{ $candidate->email }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Phone</dt>
                        <dd class="col-sm-8">{{ $candidate->phone ?: '—' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Registered</dt>
                        <dd class="col-sm-8">{{ $candidate->created_at->format('d M Y, h:i A') }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Last login</dt>
                        <dd class="col-sm-8 mb-0">
                            {{ $candidate->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Resumes</div>
                <div class="card-body text-secondary small">
                    No resumes yet. Uploaded and builder resumes will appear here after the resume module is built.
                </div>
            </div>

            <div class="card">
                <div class="card-header">Jobs applied for</div>
                <div class="card-body text-secondary small">
                    No applications yet. Applied jobs and application status will appear here after the applications module is built.
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">Account status</div>
                <div class="card-body">
                    <div class="mb-3">
                        Current status:
                        <span class="badge {{ $candidate->status->badgeClass() }}">{{ $candidate->status->label() }}</span>
                    </div>

                    <form method="POST" action="{{ route('admin.candidates.update-status', $candidate) }}" class="row g-2">
                        @csrf
                        @method('PATCH')
                        <div class="col-12">
                            <label for="status" class="form-label">Change status</label>
                            <select name="status" id="status" class="form-select" required>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected($candidate->status->value === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Inactive deactivates the account. Suspended blocks access immediately.
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-brand w-100">Update status</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Activity</div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-secondary">Account created</span>
                        <span>{{ $candidate->created_at->diffForHumans() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-secondary">Last login</span>
                        <span>{{ $candidate->last_login_at?->diffForHumans() ?? 'Never' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-secondary">Profile updated</span>
                        <span>{{ $candidate->updated_at->diffForHumans() }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
