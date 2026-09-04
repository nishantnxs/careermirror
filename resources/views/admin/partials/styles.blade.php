@php($brand = setting('primary_color', '#4f46e5'))
<style>
    :root {
        --brand: {{ $brand }};
        --sidebar-w: 260px;
    }

    body {
        background: #f4f6fb;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        font-size: .925rem;
    }

    .btn-brand, .btn-brand:hover, .btn-brand:focus {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
    }

    .btn-brand:hover { filter: brightness(.92); }

    .text-brand { color: var(--brand) !important; }

    a { text-decoration: none; }

    /* ---------- Shell ---------- */
    .admin-wrapper { display: flex; min-height: 100vh; }

    .admin-sidebar {
        width: var(--sidebar-w);
        flex: 0 0 var(--sidebar-w);
        background: #111827;
        color: #cbd5e1;
        position: fixed;
        inset: 0 auto 0 0;
        display: flex;
        flex-direction: column;
        z-index: 1040;
        transition: transform .25s ease;
    }

    .admin-sidebar .brand {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: 1.15rem 1.25rem;
        border-bottom: 1px solid rgba(255, 255, 255, .07);
        color: #fff;
        font-weight: 600;
    }

    .admin-sidebar .brand img { max-height: 34px; max-width: 150px; }

    .admin-sidebar .brand-mark {
        width: 34px; height: 34px;
        border-radius: 9px;
        background: var(--brand);
        display: grid; place-items: center;
        font-weight: 700; color: #fff;
    }

    .sidebar-nav { padding: 1rem .75rem; overflow-y: auto; flex: 1; }

    .sidebar-nav .nav-label {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .09em;
        color: #64748b;
        padding: .75rem .75rem .35rem;
    }

    .sidebar-nav .nav-link {
        display: flex;
        align-items: center;
        gap: .7rem;
        color: #cbd5e1;
        border-radius: .55rem;
        padding: .6rem .75rem;
        margin-bottom: .15rem;
        font-weight: 500;
    }

    .sidebar-nav .nav-link i { font-size: 1.05rem; }
    .sidebar-nav .nav-link:hover { background: rgba(255, 255, 255, .07); color: #fff; }
    .sidebar-nav .nav-link.active { background: var(--brand); color: #fff; }

    .admin-main {
        flex: 1 1 auto;
        margin-left: var(--sidebar-w);
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .admin-topbar {
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: .7rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        position: sticky;
        top: 0;
        z-index: 1030;
    }

    .admin-content { padding: 1.75rem 1.5rem; flex: 1; }
    .admin-footer { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background: #fff; }

    /* ---------- Cards ---------- */
    .card {
        border: 1px solid #e9edf5;
        border-radius: .8rem;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
    }

    .card-header {
        background: #fff;
        border-bottom: 1px solid #eef1f6;
        font-weight: 600;
        padding: .9rem 1.1rem;
    }

    .stat-card .stat-icon {
        width: 46px; height: 46px;
        border-radius: .7rem;
        display: grid; place-items: center;
        font-size: 1.3rem;
    }

    .table > :not(caption) > * > * { padding: .8rem .9rem; }
    .table thead th {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        background: #f8fafc;
        font-weight: 600;
    }

    .form-label { font-weight: 500; font-size: .85rem; margin-bottom: .3rem; }
    .required::after { content: " *"; color: #dc2626; }

    .avatar-initials {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: var(--brand);
        color: #fff;
        display: grid; place-items: center;
        font-weight: 600; font-size: .8rem;
    }

    .settings-preview {
        max-height: 70px;
        border: 1px solid #e5e7eb;
        border-radius: .5rem;
        padding: .25rem;
        background: #fff;
    }

    /* ---------- Auth ---------- */
    .auth-body {
        min-height: 100vh;
        background: linear-gradient(135deg, #111827 0%, #1f2937 55%, var(--brand) 160%);
    }

    .auth-shell {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 2rem 1rem;
    }

    .auth-card {
        width: 100%;
        max-width: 420px;
        border-radius: 1rem;
        border: none;
        box-shadow: 0 20px 45px rgba(0, 0, 0, .28);
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 991.98px) {
        .admin-sidebar { transform: translateX(-100%); }
        .admin-sidebar.show { transform: translateX(0); }
        .admin-main { margin-left: 0; }
        .sidebar-backdrop {
            position: fixed; inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 1035;
        }
    }
</style>
