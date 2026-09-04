@php
    $logo = \App\Models\Setting::url('site_logo_dark') ?? \App\Models\Setting::url('site_logo');
    $siteName = setting('site_name', config('app.name'));
@endphp

<aside class="admin-sidebar" id="adminSidebar">
    <a href="{{ route('admin.dashboard') }}" class="brand">
        @if ($logo)
            <img src="{{ $logo }}" alt="{{ $siteName }}">
        @else
            <span class="brand-mark">{{ mb_strtoupper(mb_substr($siteName, 0, 2)) }}</span>
            <span class="text-truncate">{{ $siteName }}</span>
        @endif
    </a>

    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link @if (request()->routeIs('admin.dashboard')) active @endif">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-label">Modules</div>
        <a href="{{ route('admin.plans.index') }}"
           class="nav-link @if (request()->routeIs('admin.plans.*')) active @endif">
            <i class="bi bi-card-checklist"></i> Plans
        </a>
        <a href="{{ route('admin.settings.edit') }}"
           class="nav-link @if (request()->routeIs('admin.settings.*')) active @endif">
            <i class="bi bi-gear"></i> Website Settings
        </a>
    </nav>

    <div class="p-3 border-top border-secondary border-opacity-25">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light w-100">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </form>
    </div>
</aside>
