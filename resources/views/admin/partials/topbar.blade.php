@php($admin = auth('admin')->user())

<header class="admin-topbar">
    <button class="btn btn-light btn-sm d-lg-none" type="button" id="sidebarToggle" aria-label="Toggle navigation">
        <i class="bi bi-list"></i>
    </button>

    <div class="ms-auto d-flex align-items-center gap-3">
        <a href="{{ url('/') }}" target="_blank" class="text-secondary small d-none d-sm-inline">
            <i class="bi bi-box-arrow-up-right me-1"></i> View website
        </a>

        <div class="dropdown">
            <button class="btn btn-link p-0 d-flex align-items-center gap-2 text-decoration-none dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false">
                @if ($admin?->avatar_url)
                    <img src="{{ $admin->avatar_url }}" class="rounded-circle" width="36" height="36" alt="">
                @else
                    <span class="avatar-initials">{{ $admin?->initials }}</span>
                @endif
                <span class="text-dark small fw-semibold d-none d-sm-inline">{{ $admin?->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold small">{{ $admin?->name }}</div>
                    <div class="text-secondary" style="font-size:.78rem">{{ $admin?->email }}</div>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.settings.edit') }}">
                        <i class="bi bi-gear me-2"></i> Website Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

@push('scripts')
<script>
    (function () {
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('adminSidebar');
        let backdrop = null;

        function close() {
            sidebar.classList.remove('show');
            backdrop?.remove();
            backdrop = null;
        }

        toggle?.addEventListener('click', function () {
            if (sidebar.classList.contains('show')) {
                close();
                return;
            }
            sidebar.classList.add('show');
            backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            backdrop.addEventListener('click', close);
            document.body.appendChild(backdrop);
        });
    })();
</script>
@endpush
