@extends('layouts.portal')

@section('title', 'Candidate Dashboard')
@section('account_label', auth('candidate')->user()->name)
@section('logout_action', route('candidate.logout'))

@section('content')
    <div class="card">
        <div class="card-body p-4">
            <h1 class="h4 fw-semibold mb-1">Welcome, {{ auth('candidate')->user()->name }}</h1>
            <p class="text-secondary mb-0">You are signed in as a candidate. Job applications and profile tools will live here.</p>
        </div>
    </div>
@endsection
