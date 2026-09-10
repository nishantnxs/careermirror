@extends('layouts.auth')

@section('title', 'Candidate Login')

@section('content')
    @php
        $logo = \App\Models\Setting::url('site_logo_dark') ?? \App\Models\Setting::url('site_logo');
        $siteName = setting('site_name', config('app.name'));
    @endphp

    <div class="w-100" style="max-width:420px">
        <div class="text-center text-white mb-4">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $siteName }}" style="max-height:48px">
            @else
                <h1 class="h3 fw-bold mb-1">{{ $siteName }}</h1>
            @endif
            <p class="mb-0 opacity-75 small">Candidate</p>
        </div>

        <div class="card auth-card">
            <div class="card-body p-4 p-sm-5">
                <h2 class="h5 fw-semibold mb-1">Sign in</h2>
                <p class="text-secondary small mb-4">Enter your credentials to access your candidate account.</p>

                @if (session('success'))
                    <div class="alert alert-success py-2 small">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('candidate.login.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label required">Email address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required autofocus autocomplete="username"
                                   placeholder="you@example.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label required">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required autocomplete="current-password" placeholder="••••••••">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword"
                                    tabindex="-1" aria-label="Show password">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="remember" id="remember" value="1"
                               class="form-check-input" @checked(old('remember'))>
                        <label for="remember" class="form-check-label small">Keep me signed in</label>
                    </div>

                    <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
                    </button>
                </form>

                <p class="text-center small text-secondary mt-4 mb-0">
                    New here?
                    <a href="{{ route('candidate.register') }}" class="text-brand fw-semibold">Create a candidate account</a>
                </p>
            </div>
        </div>

        <p class="text-center text-white-50 small mt-4 mb-0">
            Hiring instead?
            <a href="{{ route('employer.login') }}" class="text-white">Employer sign in</a>
        </p>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('togglePassword')?.addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('bi-eye', !isPassword);
        icon.classList.toggle('bi-eye-slash', isPassword);
    });
</script>
@endpush
