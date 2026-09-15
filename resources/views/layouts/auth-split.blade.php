<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Create account') — {{ setting('site_name', config('app.name')) }}</title>

    @include('partials.favicon')

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('design/css/style.css') }}">
    <style>
        @media (max-width: 991.98px) {
            .login-page { flex-direction: column; }
            .left-panel, .right-panel { width: 100%; min-height: auto; }
            .left-panel { padding: 40px 24px 48px; }
            .left-panel .brand { position: static; margin-bottom: 28px; }
            .copyright { position: static; margin-top: 28px; }
            .right-panel { padding: 24px 0 40px; }
        }
        .google-btn:disabled {
            opacity: .65;
            cursor: not-allowed;
        }
    </style>
    @stack('styles')
</head>
<body>
@yield('content')
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
@stack('scripts')
</body>
</html>
