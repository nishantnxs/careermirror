@extends('layouts.marketing')

@section('title', 'Account Settings')

@section('content')
<section class="candidate-panel">
    <div class="container">
        <div class="mb-4">
            <h1 class="panel-title">Account settings</h1>
            <p class="panel-subtitle">Manage your password and account security.</p>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="panel-card">
                    <div class="panel-card-header">Change password</div>
                    <div class="panel-card-body">
                        <form method="POST" action="{{ route('candidate.settings.password') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label required">Current password</label>
                                <input type="password" name="current_password" id="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                                @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label required">New password</label>
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label required">Confirm new password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control" required autocomplete="new-password">
                            </div>

                            <button type="submit" class="btn btn-primary">Update password</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel-card">
                    <div class="panel-card-header">Account</div>
                    <div class="panel-card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-secondary fw-normal">Email</dt>
                            <dd class="col-sm-8">{{ $candidate->email }}</dd>
                            <dt class="col-sm-4 text-secondary fw-normal">Status</dt>
                            <dd class="col-sm-8">{{ $candidate->status->label() }}</dd>
                            <dt class="col-sm-4 text-secondary fw-normal">Member since</dt>
                            <dd class="col-sm-8 mb-0">{{ $candidate->created_at->format('d M Y') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
