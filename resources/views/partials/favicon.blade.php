{{-- A favicon uploaded through Website Settings overrides the bundled default. --}}
@php($uploaded = \App\Models\Setting::url('site_favicon'))

@if ($uploaded)
    <link rel="icon" href="{{ $uploaded }}">
    <link rel="apple-touch-icon" href="{{ $uploaded }}">
@else
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
@endif
