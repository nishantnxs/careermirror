@extends('layouts.marketing')

@section('title', 'Edit resume')

@section('content')
<section class="resume-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">
                <div class="resume-heading mb-4 d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h1 class="mb-2">My resume</h1>
                        <p class="mb-0">Employers see this whenever you apply. Keep it current and specific.</p>
                    </div>
                    <a href="{{ route('candidate.resumes.preview', $resume) }}" class="btn btn-outline-secondary">Preview</a>
                </div>

                <form method="POST" action="{{ route('candidate.resumes.update', $resume) }}" id="resumeBuilderForm">
                    @csrf
                    @method('PUT')
                    @include('candidate.resumes.form')
                    <div class="resume-save-wrap d-flex flex-wrap gap-2 align-items-center">
                        <button type="submit" class="resume-save-btn">Save resume</button>
                        <a href="{{ route('candidate.resumes.preview', $resume) }}" class="btn btn-outline-secondary">Preview</a>
                        <a href="{{ route('candidate.resumes.index') }}" class="btn btn-outline-secondary">All resumes</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    @include('candidate.resumes.partials.builder-script')
@endpush
