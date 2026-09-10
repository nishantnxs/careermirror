<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &middot; {{ setting('site_name', config('app.name')) }}</title>

    @include('partials.favicon')

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    @include('portal.partials.styles')
    @stack('styles')
</head>
<body class="portal-body">
<div class="portal-shell">
    <header class="portal-topbar">
        <a href="@yield('home_href', url('/'))" class="portal-brand">
            <span class="portal-brand-mark">
                {{ strtoupper(substr(setting('site_name', config('app.name')), 0, 1)) }}
            </span>
            <span>{{ setting('site_name', config('app.name')) }}</span>
        </a>

        <div class="portal-account">
            <span class="portal-account-label">@yield('account_label')</span>
            <form method="POST" action="@yield('logout_action')" class="mb-0">
                @csrf
                <button type="submit" class="portal-btn portal-btn-outline portal-btn-sm">Sign out</button>
            </form>
        </div>
    </header>

    @include('admin.partials.alerts')

    @hasSection('nav')
        @yield('nav')
    @endif

    @yield('content')
</div>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
@stack('scripts')
</body>
</html>
