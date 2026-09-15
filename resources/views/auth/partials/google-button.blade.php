@php
    $googleReady = filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
@endphp
@if ($googleReady)
    <a href="{{ $url }}" class="google-btn">
        <span class="google-icon">G</span>
        <span>Continue with Google</span>
    </a>
@else
    <button type="button" class="google-btn" disabled title="Google sign-in is not configured">
        <span class="google-icon">G</span>
        <span>Continue with Google</span>
    </button>
@endif
