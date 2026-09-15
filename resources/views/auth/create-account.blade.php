@extends('layouts.auth-split')

@section('title', 'Create your account')

@section('content')
@php
    $siteName = setting('site_name', config('app.name'));
    $logo = \App\Models\Setting::url('site_logo') ?? \App\Models\Setting::url('site_logo_dark');
    $activeTab = $activeTab ?? 'candidate';
@endphp

<section class="login-page">
    <div class="left-panel">
        <a href="{{ route('home') }}" class="brand">
            <img src="{{ $logo ?: asset('design/images/careermirror-logo.png') }}" alt="{{ $siteName }}">
        </a>
        <div class="left-content">
            <h1>Hiring and job hunting, reflected clearly.</h1>
            <p class="intro">
                Post roles, review resumes, track every application and chat with the other side —
                all inside one Canadian job marketplace.
            </p>
            <ul class="feature-list">
                <li>Employer dashboards with live application pipelines</li>
                <li>Guided resume builder for candidates</li>
                <li>Direct messaging between employers and applicants</li>
            </ul>
        </div>
        <div class="copyright">{{ $siteName }}</div>
    </div>

    <div class="right-panel">
        <div class="login-wrapper">
            <h2 class="login-title">Create your account</h2>
            <p class="login-subtitle">It takes less than a minute to get started.</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small mb-3">{{ $errors->first() }}</div>
            @endif

            @if (session('success'))
                <div class="alert alert-success py-2 small mb-3">{{ session('success') }}</div>
            @endif

            <div class="account-tabs">
                <div class="tab-button">
                    <button type="button" class="{{ $activeTab === 'candidate' ? 'active' : '' }}" data-tab="candidate">
                        <i class="fa-solid fa-user"></i>I'm a candidate
                    </button>
                    <button type="button" class="{{ $activeTab === 'employer' ? 'active' : '' }}" data-tab="employer">
                        <i class="fa-solid fa-briefcase"></i>I'm hiring
                    </button>
                </div>

                <div class="tab-container">
                    <div class="tab-pannel {{ $activeTab === 'candidate' ? 'active' : '' }}" data-panel="candidate">
                        @include('auth.partials.google-button', ['url' => route('candidate.auth.google')])
                        <div class="divider">OR CREATE WITH EMAIL</div>

                        <form method="POST" action="{{ route('candidate.register.store') }}" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="candidate_name" class="form-label">Full name</label>
                                <input type="text" name="name" id="candidate_name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required autocomplete="name"
                                       @if ($activeTab === 'candidate') autofocus @endif>
                            </div>
                            <div class="mb-3">
                                <label for="candidate_email" class="form-label">Email</label>
                                <input type="email" name="email" id="candidate_email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required autocomplete="email">
                            </div>
                            <div class="mb-3">
                                <label for="candidate_phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="candidate_phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" autocomplete="tel">
                            </div>
                            <div class="mb-3">
                                <label for="candidate_password" class="form-label">Password</label>
                                <input type="password" name="password" id="candidate_password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       required autocomplete="new-password">
                            </div>
                            <div class="mb-0">
                                <label for="candidate_password_confirmation" class="form-label">Confirm password</label>
                                <input type="password" name="password_confirmation" id="candidate_password_confirmation"
                                       class="form-control" required autocomplete="new-password">
                            </div>
                            <button type="submit" class="signin-btn">Create account</button>
                        </form>

                        <div class="create-account">
                            Already have an account?
                            <a href="{{ route('candidate.login') }}">Sign in</a>
                        </div>
                        <a href="{{ route('jobs.index') }}" class="browse-link">Browse jobs without an account</a>
                    </div>

                    <div class="tab-pannel {{ $activeTab === 'employer' ? 'active' : '' }}" data-panel="employer">
                        @include('auth.partials.google-button', ['url' => route('employer.auth.google')])
                        <div class="divider">OR CREATE WITH EMAIL</div>

                        <form method="POST" action="{{ route('employer.register.store') }}" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="employer_name" class="form-label">Full name</label>
                                <input type="text" name="name" id="employer_name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required autocomplete="name"
                                       @if ($activeTab === 'employer') autofocus @endif>
                            </div>
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company name</label>
                                <input type="text" name="company_name" id="company_name"
                                       class="form-control @error('company_name') is-invalid @enderror"
                                       value="{{ old('company_name') }}" required autocomplete="organization">
                            </div>
                            <div class="mb-3">
                                <label for="employer_email" class="form-label">Email</label>
                                <input type="email" name="email" id="employer_email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required autocomplete="email">
                            </div>
                            <div class="mb-3">
                                <label for="employer_phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="employer_phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" autocomplete="tel">
                            </div>
                            <div class="mb-3">
                                <label for="employer_password" class="form-label">Password</label>
                                <input type="password" name="password" id="employer_password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       required autocomplete="new-password">
                            </div>
                            <div class="mb-0">
                                <label for="employer_password_confirmation" class="form-label">Confirm password</label>
                                <input type="password" name="password_confirmation" id="employer_password_confirmation"
                                       class="form-control" required autocomplete="new-password">
                            </div>
                            <button type="submit" class="signin-btn">Create account</button>
                        </form>

                        <div class="create-account">
                            Already have an account?
                            <a href="{{ route('employer.login') }}">Sign in</a>
                        </div>
                        <a href="{{ route('jobs.index') }}" class="browse-link">Browse jobs without an account</a>
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
    const buttons = document.querySelectorAll('.tab-button button[data-tab]');
    const panels = document.querySelectorAll('.tab-pannel[data-panel]');

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const tab = button.dataset.tab;
            buttons.forEach((item) => item.classList.toggle('active', item === button));
            panels.forEach((panel) => panel.classList.toggle('active', panel.dataset.panel === tab));

            const url = tab === 'employer'
                ? @json(route('employer.register'))
                : @json(route('candidate.register'));
            window.history.replaceState({}, '', url);
        });
    });
})();
</script>
@endpush
