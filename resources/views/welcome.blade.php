<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ setting('site_name', config('app.name')) }}</title>

    @include('partials.favicon')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @include('admin.partials.styles')
</head>
<body class="auth-body">
<div class="auth-shell">
    <div class="container" style="max-width:760px">
        <div class="text-center text-white mb-4">
            <h1 class="h3 fw-bold mb-2">{{ setting('site_name', config('app.name')) }}</h1>
            <p class="opacity-75 mb-0">Choose how you want to continue.</p>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card auth-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="mb-3">
                            <span class="badge bg-primary-subtle text-primary-emphasis">Candidate</span>
                        </div>
                        <h2 class="h5 fw-semibold">Looking for work</h2>
                        <p class="text-secondary small flex-grow-1">Create a profile and apply for roles that match your skills.</p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('candidate.register') }}" class="btn btn-brand">Sign up</a>
                            <a href="{{ route('candidate.login') }}" class="btn btn-outline-secondary">Sign in</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card auth-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="mb-3">
                            <span class="badge bg-success-subtle text-success-emphasis">Employer</span>
                        </div>
                        <h2 class="h5 fw-semibold">Hiring talent</h2>
                        <p class="text-secondary small flex-grow-1">Post openings and review candidates from a separate employer account.</p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('employer.register') }}" class="btn btn-brand">Sign up</a>
                            <a href="{{ route('employer.login') }}" class="btn btn-outline-secondary">Sign in</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
