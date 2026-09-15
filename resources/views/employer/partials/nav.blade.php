<nav class="portal-nav" aria-label="Employer navigation">
    <a class="{{ ($active ?? '') === 'dashboard' ? 'active' : '' }}"
       href="{{ route('employer.dashboard') }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="{{ ($active ?? '') === 'plans' ? 'active' : '' }}"
       href="{{ route('employer.plans.index') }}">
        <i class="bi bi-tags"></i> Plans
    </a>
    <a class="{{ ($active ?? '') === 'jobs' ? 'active' : '' }}"
       href="{{ route('employer.jobs.index') }}">
        <i class="bi bi-briefcase"></i> Jobs
    </a>
    <a class="{{ ($active ?? '') === 'applicants' ? 'active' : '' }}"
       href="{{ route('employer.applicants.index') }}">
        <i class="bi bi-people"></i> Applicants
    </a>
    <a class="{{ ($active ?? '') === 'messages' ? 'active' : '' }}"
       href="{{ route('employer.messages.index') }}">
        <i class="bi bi-chat-dots"></i> Messages
    </a>
    <a class="{{ ($active ?? '') === 'orders' ? 'active' : '' }}"
       href="{{ route('employer.orders.index') }}">
        <i class="bi bi-receipt"></i> Orders
    </a>
</nav>
