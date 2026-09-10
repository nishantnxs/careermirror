@extends('layouts.auth')

@section('title', 'Candidate Sign up')

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
                <h2 class="h5 fw-semibold mb-1">Create an account</h2>
                <p class="text-secondary small mb-4">Sign up to apply for roles and manage your profile.</p>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('candidate.register.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label required">Full name</label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required autofocus autocomplete="name">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label required">Email address</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autocomplete="email">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" autocomplete="tel">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required autocomplete="new-password">
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label required">Confirm password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control" required autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">
                        Create account
                    </button>
                </form>

                <p class="text-center small text-secondary mt-4 mb-0">
                    Already have an account?
                    <a href="{{ route('candidate.login') }}" class="text-brand fw-semibold">Sign in</a>
                </p>
            </div>
        </div>
    </div>
@endsection
