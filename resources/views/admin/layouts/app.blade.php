<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &middot; {{ setting('site_name', config('app.name')) }} Admin</title>

    @include('partials.favicon')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @include('admin.partials.styles')
</head>
<body>
<div class="admin-wrapper">
    @include('admin.partials.sidebar')

    <div class="admin-main">
        @include('admin.partials.topbar')

        <main class="admin-content">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h1 class="h4 mb-1 fw-semibold">@yield('heading', View::getSection('title', 'Dashboard'))</h1>
                    @hasSection('subheading')
                        <p class="text-secondary small mb-0">@yield('subheading')</p>
                    @endif
                </div>
                <div class="d-flex gap-2">@yield('actions')</div>
            </div>

            @include('admin.partials.alerts')

            @yield('content')
        </main>

        <footer class="admin-footer">
            <span class="small text-secondary">
                {{ setting('footer_copyright', '© '.date('Y').' '.setting('site_name', config('app.name'))) }}
            </span>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
