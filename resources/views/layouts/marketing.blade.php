<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Browse jobs') — {{ $siteName ?? setting('site_name', config('app.name')) }}</title>

    @include('partials.favicon')

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('design/css/style.css') }}">
    @stack('styles')
</head>
<body>
@php
    $siteName = $siteName ?? setting('site_name', config('app.name'));
    $logo = \App\Models\Setting::url('site_logo') ?? \App\Models\Setting::url('site_logo_dark');
    $candidate = auth('candidate')->user();
    $employer = auth('employer')->user();
    $messaging = app(\App\Services\MessagingService::class);
    $headerUnreadCount = 0;
    $messagesUnreadUrl = null;
    if ($candidate) {
        $headerUnreadCount = $messaging->unreadCountForCandidate($candidate);
        $messagesUnreadUrl = route('candidate.messages.unread');
    } elseif ($employer) {
        $headerUnreadCount = $messaging->unreadCountForEmployer($employer);
        $messagesUnreadUrl = route('employer.messages.unread');
    }
@endphp

<header class="header py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-2 col-6">
                <div class="logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ $logo ?: asset('design/images/careermirror-logo.png') }}" alt="{{ $siteName }}">
                    </a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="navigation">
                    <ul class="d-flex gap-4 mb-0">
                            <li class="d-inline-block"><a href="{{ route('jobs.index') }}">Browse jobs</a></li>
                        @if ($employer)
                            <li class="d-inline-block"><a href="{{ route('employer.plans.index') }}">Plans</a></li>
                            <li class="d-inline-block"><a href="{{ route('employer.jobs.index') }}">My jobs</a></li>
                            <li class="d-inline-block"><a href="{{ route('employer.applicants.index') }}">Applicants</a></li>
                            <li class="d-inline-block"><a href="{{ route('employer.dashboard') }}">Dashboard</a></li>
                        @elseif ($candidate)
                            <li class="d-inline-block"><a href="{{ route('employer.plans.index') }}">For employers</a></li>
                            <li class="d-inline-block"><a href="{{ route('candidate.applications.index') }}">My applications</a></li>
                            <li class="d-inline-block"><a href="{{ route('candidate.dashboard') }}">Dashboard</a></li>
                        @else
                            <li class="d-inline-block"><a href="{{ route('employer.plans.index') }}">For employers</a></li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="header-button text-end">
                    @if ($candidate)
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <div class="chat-icon" data-unread-url="{{ $messagesUnreadUrl }}">
                                <a href="{{ route('candidate.messages.index') }}" aria-label="Messages">
                                    <i class="fa-regular fa-message"></i>
                                    <span class="chat-unread-badge{{ $headerUnreadCount > 0 ? ' is-visible' : '' }}"
                                          data-unread-badge
                                          @if ($headerUnreadCount <= 0) hidden @endif>
                                        {{ $headerUnreadCount > 99 ? '99+' : $headerUnreadCount }}
                                    </span>
                                </a>
                            </div>
                            <div class="user-dropdown">
                                <button type="button" class="dropdown-button" id="userMenuToggle">
                                    <span class="rounded-full text-xs">{{ strtoupper(mb_substr($candidate->name, 0, 2)) }}</span>
                                    {{ $candidate->name }}
                                </button>
                                <div class="dropdown-nav" id="userMenu">
                                    <p>Signed in as candidate</p>
                                    <ul>
                                        <li><a href="{{ route('candidate.dashboard') }}">Dashboard</a></li>
                                        <li><a href="{{ route('candidate.profile.edit') }}">Profile</a></li>
                                        <li><a href="{{ route('candidate.resumes.index') }}">My resume</a></li>
                                        <li><a href="{{ route('candidate.applications.index') }}">My applications</a></li>
                                        <li><a href="{{ route('candidate.saved-jobs.index') }}">Saved jobs</a></li>
                                        <li><a href="{{ route('candidate.messages.index') }}">Message</a></li>
                                        <li><a href="{{ route('candidate.settings.edit') }}">Settings</a></li>
                                        <li>
                                            <form method="POST" action="{{ route('candidate.logout') }}">
                                                @csrf
                                                <button type="submit" class="dropdown-signout">
                                                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign out
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @elseif ($employer)
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <div class="chat-icon" data-unread-url="{{ $messagesUnreadUrl }}">
                                <a href="{{ route('employer.messages.index') }}" aria-label="Messages">
                                    <i class="fa-regular fa-message"></i>
                                    <span class="chat-unread-badge{{ $headerUnreadCount > 0 ? ' is-visible' : '' }}"
                                          data-unread-badge
                                          @if ($headerUnreadCount <= 0) hidden @endif>
                                        {{ $headerUnreadCount > 99 ? '99+' : $headerUnreadCount }}
                                    </span>
                                </a>
                            </div>
                            <div class="user-dropdown">
                                <button type="button" class="dropdown-button" id="userMenuToggle">
                                    <span class="rounded-full text-xs">{{ strtoupper(mb_substr($employer->name, 0, 2)) }}</span>
                                    {{ $employer->name }}
                                </button>
                                <div class="dropdown-nav" id="userMenu">
                                    <p>Signed in as employer</p>
                                    <ul>
                                        <li><a href="{{ route('employer.dashboard') }}">Dashboard</a></li>
                                        <li><a href="{{ route('employer.jobs.index') }}">My jobs</a></li>
                                        <li><a href="{{ route('employer.applicants.index') }}">Applicants</a></li>
                                        <li><a href="{{ route('employer.plans.index') }}">Plans</a></li>
                                        <li><a href="{{ route('employer.orders.index') }}">Payment history</a></li>
                                        <li><a href="{{ route('employer.messages.index') }}">Message</a></li>
                                        <li>
                                            <form method="POST" action="{{ route('employer.logout') }}">
                                                @csrf
                                                <button type="submit" class="dropdown-signout">
                                                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign out
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('candidate.login') }}">Sign in</a>
                        <a href="{{ route('candidate.register') }}" class="button">Get started</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>

@include('admin.partials.alerts')

@yield('content')

<footer class="site-footer">
    <div class="footer-main py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="footer-about">
                        <a href="{{ route('home') }}" class="footer-logo">
                            <img src="{{ $logo ?: asset('design/images/careermirror-logo.png') }}" alt="{{ $siteName }}">
                        </a>
                        <p class="mt-3 mb-0">Canada's job marketplace connecting great employers with candidates who fit.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="footer-links">
                        <h6>Candidates</h6>
                        <ul class="list-unstyled mb-0">
                            <li><a href="{{ route('jobs.index') }}">Browse jobs</a></li>
                            <li><a href="{{ route('candidate.register') }}">Build a resume</a></li>
                            <li><a href="{{ route('candidate.login') }}">Track applications</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="footer-links">
                        <h6>Employers</h6>
                        <ul class="list-unstyled mb-0">
                            <li><a href="{{ route('employer.register') }}">Post a job</a></li>
                            <li><a href="{{ route('employer.plans.index') }}">Pricing</a></li>
                            <li><a href="{{ route('employer.login') }}">Employer dashboard</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom py-3">
        <div class="container">
            <div class="col-12 text-center">
                <p class="mb-0">&copy; {{ date('Y') }} {{ $siteName }} — All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script>
(function () {
    const toggle = document.getElementById('userMenuToggle');
    const menu = document.getElementById('userMenu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', function (event) {
        event.stopPropagation();
        menu.classList.toggle('show');
    });

    document.addEventListener('click', function () {
        menu.classList.remove('show');
    });

    menu.addEventListener('click', function (event) {
        event.stopPropagation();
    });
})();
</script>
@if ($candidate || $employer)
    <script src="{{ asset('design/js/messaging.js') }}?v={{ filemtime(public_path('design/js/messaging.js')) }}"></script>
@endif
@stack('scripts')
</body>
</html>
