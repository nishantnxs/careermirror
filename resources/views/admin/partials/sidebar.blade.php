@php
    $logo = \App\Models\Setting::url('site_logo_dark') ?? \App\Models\Setting::url('site_logo');
    $siteName = setting('site_name', config('app.name'));
    $admin = auth('admin')->user();
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
        @admincan('dashboard.view')
            <div class="nav-label">Main</div>
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link @if (request()->routeIs('admin.dashboard')) active @endif">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        @endadmincan

        @if ($admin?->hasAnyPermission('employers.view', 'candidates.view'))
            <div class="nav-label">Users</div>
            @admincan('employers.view')
                <a href="{{ route('admin.employers.index') }}"
                   class="nav-link @if (request()->routeIs('admin.employers.*')) active @endif">
                    <i class="bi bi-building"></i> Employers
                </a>
            @endadmincan
            @admincan('candidates.view')
                <a href="{{ route('admin.candidates.index') }}"
                   class="nav-link @if (request()->routeIs('admin.candidates.*')) active @endif">
                    <i class="bi bi-people"></i> Candidates
                </a>
            @endadmincan
        @endif

        @if ($admin?->hasAnyPermission('plans.view', 'settings.manage', 'activity.view', 'categories.view', 'domains.view'))
            <div class="nav-label">Modules</div>
            @admincan('domains.view')
                <a href="{{ route('admin.domains.index') }}"
                   class="nav-link @if (request()->routeIs('admin.domains.*')) active @endif">
                    <i class="bi bi-globe2"></i> Domains
                </a>
            @endadmincan
            @admincan('plans.view')
                <a href="{{ route('admin.plans.index') }}"
                   class="nav-link @if (request()->routeIs('admin.plans.*')) active @endif">
                    <i class="bi bi-card-checklist"></i> Plans
                </a>
            @endadmincan
            @admincan('categories.view')
                <a href="{{ route('admin.categories.index') }}"
                   class="nav-link @if (request()->routeIs('admin.categories.*')) active @endif">
                    <i class="bi bi-tags"></i> Categories
                </a>
            @endadmincan
            @admincan('settings.manage')
                <a href="{{ route('admin.settings.edit') }}"
                   class="nav-link @if (request()->routeIs('admin.settings.*') || request()->routeIs('admin.domains.settings.*')) active @endif">
                    <i class="bi bi-gear"></i> Website Settings
                </a>
            @endadmincan
            @admincan('activity.view')
                <a href="{{ route('admin.activity.index') }}"
                   class="nav-link @if (request()->routeIs('admin.activity.*')) active @endif">
                    <i class="bi bi-journal-text"></i> Activity Log
                </a>
            @endadmincan
        @endif

        @if ($admin?->hasAnyPermission('roles.manage', 'moderators.manage'))
            <div class="nav-label">Access control</div>
            @admincan('roles.manage')
                <a href="{{ route('admin.roles.index') }}"
                   class="nav-link @if (request()->routeIs('admin.roles.*')) active @endif">
                    <i class="bi bi-shield-lock"></i> Roles
                </a>
            @endadmincan
            @admincan('moderators.manage')
                <a href="{{ route('admin.moderators.index') }}"
                   class="nav-link @if (request()->routeIs('admin.moderators.*')) active @endif">
                    <i class="bi bi-person-badge"></i> Moderators
                </a>
            @endadmincan
        @endif
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
