@extends('layouts.portal')

@section('title', 'Edit job')
@section('account_label', auth('employer')->user()->company_name)
@section('logout_action', route('employer.logout'))

@section('nav')
    @include('employer.partials.nav', ['active' => 'jobs'])
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Edit job</h1>
        <p class="text-secondary mb-0">{{ $job->title }}</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('employer.jobs.update', $job) }}">
                @csrf
                @method('PUT')
                @include('employer.jobs.form')
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-brand">Save changes</button>
                    <a href="{{ route('employer.jobs.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
