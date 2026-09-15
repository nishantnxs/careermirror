@extends('layouts.auth-split')

@section('title', 'Sign in')

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
            <h2 class="login-title">Welcome back</h2>
            <p class="login-subtitle">Sign in to continue to your dashboard.</p>

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
                        <div class="divider">OR SIGN IN WITH EMAIL</div>

                        <form method="POST" action="{{ route('candidate.login.store') }}" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="candidate_email" class="form-label">Email</label>
                                <input type="email" name="email" id="candidate_email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required autocomplete="username"
                                       @if ($activeTab === 'candidate') autofocus @endif>
                            </div>
                            <div class="mb-0">
                                <div class="password-row">
                                    <label for="candidate_password" class="form-label">Password</label>
                                    <a href="{{ route('candidate.password.request') }}" class="forgot-link">Forgot password?</a>
                                </div>
                                <input type="password" name="password" id="candidate_password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       required autocomplete="current-password">
                            </div>
                            <div class="form-check mt-3 mb-0">
                                <input type="checkbox" name="remember" id="candidate_remember" value="1"
                                       class="form-check-input" @checked(old('remember'))>
                                <label for="candidate_remember" class="form-check-label small">Keep me signed in</label>
                            </div>
                            <button type="submit" class="signin-btn">Sign in</button>
                        </form>

                        <div class="create-account">
                            New to CareerMirror?
                            <a href="{{ route('candidate.register') }}">Create an account</a>
                        </div>
                        <a href="{{ route('jobs.index') }}" class="browse-link">Browse jobs without an account</a>
                    </div>

                    <div class="tab-pannel {{ $activeTab === 'employer' ? 'active' : '' }}" data-panel="employer">
                        @include('auth.partials.google-button', ['url' => route('employer.auth.google')])
                        <div class="divider">OR SIGN IN WITH EMAIL</div>

                        <form method="POST" action="{{ route('employer.login.store') }}" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="employer_email" class="form-label">Email</label>
                                <input type="email" name="email" id="employer_email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required autocomplete="username"
                                       @if ($activeTab === 'employer') autofocus @endif>
                            </div>
                            <div class="mb-0">
                                <label for="employer_password" class="form-label">Password</label>
                                <input type="password" name="password" id="employer_password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       required autocomplete="current-password">
                            </div>
                            <div class="form-check mt-3 mb-0">
                                <input type="checkbox" name="remember" id="employer_remember" value="1"
                                       class="form-check-input" @checked(old('remember'))>
                                <label for="employer_remember" class="form-check-label small">Keep me signed in</label>
                            </div>
                            <button type="submit" class="signin-btn">Sign in</button>
                        </form>

                        <div class="create-account">
                            New to CareerMirror?
                            <a href="{{ route('employer.register') }}">Create an account</a>
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
                ? @json(route('employer.login'))
                : @json(route('candidate.login'));
            window.history.replaceState({}, '', url);
        });
    });
})();
</script>
@endpush
