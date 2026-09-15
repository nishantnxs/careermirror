@extends('layouts.marketing')

@section('title', 'Post a job')

@section('content')
<section class="employer-panel">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="mb-4">
                    <h1 class="panel-title">Post a job</h1>
                    <p class="panel-subtitle">
                        {{ $subscription->remainingJobs() }} credit(s) left on your current plan.
                    </p>
                </div>

                <div class="panel-card">
                    <div class="panel-card-body">
                        <form method="POST" action="{{ route('employer.jobs.store') }}">
                            @csrf
                            @include('employer.jobs.form')
                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">Publish job</button>
                                <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
