@extends('layouts.marketing')

@section('title', 'Upload Resume')

@section('content')
<section class="candidate-panel">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="panel-title">Upload resume</h1>
                <p class="panel-subtitle">Upload an existing PDF, DOC, or DOCX file.</p>
            </div>
            <a href="{{ route('candidate.resumes.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">
                <div class="panel-card">
                    <div class="panel-card-body">
                        <form method="POST" action="{{ route('candidate.resumes.upload.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label for="title" class="form-label required">Title</label>
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', 'Uploaded Resume') }}" required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="resume_file" class="form-label required">Resume file</label>
                                <input type="file" name="resume_file" id="resume_file" accept=".pdf,.doc,.docx,application/pdf"
                                       class="form-control @error('resume_file') is-invalid @enderror" required>
                                @error('resume_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">PDF, DOC or DOCX up to 5MB.</div>
                            </div>

                            <div class="form-check mb-4">
                                <input type="hidden" name="is_default" value="0">
                                <input type="checkbox" class="form-check-input" name="is_default" id="is_default" value="1"
                                       @checked(old('is_default', true))>
                                <label class="form-check-label" for="is_default">Set as default resume</label>
                            </div>

                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
