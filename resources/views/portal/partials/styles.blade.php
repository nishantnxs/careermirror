@php($brand = setting('primary_color', '#4f46e5'))
<style>
    :root {
        --brand: {{ $brand }};
        --portal-bg: #f4f6fb;
        --portal-border: #e9edf5;
        --portal-muted: #64748b;
        --portal-text: #0f172a;
        --portal-card: #ffffff;
        --portal-radius: .85rem;
    }

    body.portal-body {
        margin: 0;
        background: var(--portal-bg);
        color: var(--portal-text);
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        font-size: .925rem;
        line-height: 1.5;
        min-height: 100vh;
    }

    .portal-shell {
        width: min(1100px, calc(100% - 2rem));
        margin: 0 auto;
        padding: 1.25rem 0 2.5rem;
    }

    .portal-topbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        margin-bottom: 1rem;
        background: var(--portal-card);
        border: 1px solid var(--portal-border);
        border-radius: var(--portal-radius);
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
    }

    .portal-brand {
        display: inline-flex;
        align-items: center;
        gap: .7rem;
        color: var(--portal-text) !important;
        font-weight: 700;
        font-size: 1.05rem;
        text-decoration: none;
    }

    .portal-brand-mark {
        width: 36px;
        height: 36px;
        border-radius: .7rem;
        background: var(--brand);
        color: #fff;
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: .85rem;
    }

    .portal-account {
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .portal-account-label {
        color: var(--portal-muted);
        font-size: .85rem;
    }

    .portal-btn,
    .btn-brand {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        border: 1px solid transparent;
        border-radius: .55rem;
        padding: .45rem .85rem;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        background: var(--brand);
        color: #fff !important;
        text-decoration: none;
    }

    .portal-btn:hover,
    .btn-brand:hover,
    .btn-brand:focus {
        filter: brightness(.92);
        color: #fff !important;
        background: var(--brand);
        border-color: var(--brand);
    }

    .portal-btn-outline {
        background: #fff;
        border-color: #d7dde8;
        color: var(--portal-text) !important;
    }

    .portal-btn-outline:hover {
        background: #f8fafc;
        filter: none;
        color: var(--portal-text) !important;
    }

    .portal-btn-sm {
        padding: .35rem .7rem;
        font-size: .8rem;
    }

    .portal-nav {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin: 0 0 1.25rem;
        padding: .4rem;
        background: var(--portal-card);
        border: 1px solid var(--portal-border);
        border-radius: 999px;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
    }

    .portal-nav a {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .55rem 1rem;
        border-radius: 999px;
        color: var(--portal-muted) !important;
        font-weight: 600;
        font-size: .875rem;
        text-decoration: none;
    }

    .portal-nav a:hover {
        background: #f1f5f9;
        color: var(--portal-text) !important;
    }

    .portal-nav a.active {
        background: var(--brand);
        color: #fff !important;
    }

    .portal-card,
    .portal-body .card {
        background: var(--portal-card);
        border: 1px solid var(--portal-border);
        border-radius: var(--portal-radius);
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
    }

    .portal-body .card-header {
        background: #fff;
        border-bottom: 1px solid #eef1f6;
        font-weight: 600;
        padding: .9rem 1.1rem;
    }

    .text-brand { color: var(--brand) !important; }

    .portal-welcome {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 1.25rem;
        padding: 1.35rem 1.4rem;
        background:
            linear-gradient(135deg, rgba(79, 70, 229, .08), rgba(79, 70, 229, .02)),
            var(--portal-card);
        border: 1px solid var(--portal-border);
        border-radius: var(--portal-radius);
    }

    .portal-welcome h1 {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0 0 .35rem;
    }

    .portal-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .45rem .75rem;
        border-radius: 999px;
        background: rgba(79, 70, 229, .1);
        color: var(--brand);
        font-size: .8rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .portal-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .portal-action-card {
        display: block;
        height: 100%;
        padding: 1.2rem;
        background: var(--portal-card);
        border: 1px solid var(--portal-border);
        border-radius: var(--portal-radius);
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        color: inherit !important;
        text-decoration: none;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    .portal-action-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        transform: translateY(-2px);
        color: inherit !important;
    }

    .portal-action-icon {
        width: 42px;
        height: 42px;
        border-radius: .75rem;
        display: grid;
        place-items: center;
        background: rgba(79, 70, 229, .1);
        color: var(--brand);
        font-size: 1.2rem;
        margin-bottom: .85rem;
    }

    .portal-action-card h2 {
        font-size: .98rem;
        font-weight: 700;
        margin: 0 0 .35rem;
        color: var(--portal-text);
    }

    .portal-action-card p {
        margin: 0;
        color: var(--portal-muted);
        font-size: .85rem;
    }

    .portal-body .btn-light {
        background: #fff;
        border: 1px solid #d7dde8;
    }

    @media (max-width: 767.98px) {
        .portal-actions { grid-template-columns: 1fr; }
        .portal-shell { width: min(100% - 1.25rem, 1100px); }
        .portal-nav { border-radius: var(--portal-radius); }
    }
</style>
