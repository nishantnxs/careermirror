<nav class="portal-nav" aria-label="Candidate navigation">
    <a class="{{ ($active ?? '') === 'dashboard' ? 'active' : '' }}"
       href="{{ route('candidate.dashboard') }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="{{ ($active ?? '') === 'jobs' ? 'active' : '' }}"
       href="{{ route('candidate.jobs.index') }}">
        <i class="bi bi-search"></i> Jobs
    </a>
    <a class="{{ ($active ?? '') === 'applications' ? 'active' : '' }}"
       href="{{ route('candidate.applications.index') }}">
        <i class="bi bi-send"></i> Applied
    </a>
    <a class="{{ ($active ?? '') === 'saved' ? 'active' : '' }}"
       href="{{ route('candidate.saved-jobs.index') }}">
        <i class="bi bi-bookmark"></i> Saved
    </a>
    <a class="{{ ($active ?? '') === 'messages' ? 'active' : '' }}"
       href="{{ route('candidate.messages.index') }}">
        <i class="bi bi-chat-dots"></i> Messages
    </a>
    <a class="{{ ($active ?? '') === 'profile' ? 'active' : '' }}"
       href="{{ route('candidate.profile.edit') }}">
        <i class="bi bi-person"></i> Profile
    </a>
    <a class="{{ ($active ?? '') === 'resumes' ? 'active' : '' }}"
       href="{{ route('candidate.resumes.index') }}">
        <i class="bi bi-file-earmark-text"></i> Resumes
    </a>
    <a class="{{ ($active ?? '') === 'settings' ? 'active' : '' }}"
       href="{{ route('candidate.settings.edit') }}">
        <i class="bi bi-gear"></i> Settings
    </a>
</nav>
