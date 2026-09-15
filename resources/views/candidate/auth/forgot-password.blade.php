@extends('layouts.auth-split')

@section('title', 'Forgot Password')

@section('content')
@php
    $siteName = setting('site_name', config('app.name'));
    $logo = \App\Models\Setting::url('site_logo') ?? \App\Models\Setting::url('site_logo_dark');
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
            <h2 class="login-title">Forgot password</h2>
            <p class="login-subtitle">Enter your email and we will send a reset link.</p>

            @if (session('success'))
                <div class="alert alert-success py-2 small mb-3">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger py-2 small mb-3">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('candidate.password.email') }}" novalidate>
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="signin-btn">Send reset link</button>
            </form>

            <div class="create-account">
                Remember your password?
                <a href="{{ route('candidate.login') }}">Back to sign in</a>
            </div>
        </div>
    </div>
</section>
@endsection
