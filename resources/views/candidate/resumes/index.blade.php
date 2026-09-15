@extends('layouts.marketing')

@section('title', 'My Resumes')

@section('content')
<section class="candidate-panel">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="panel-title">My resumes</h1>
                <p class="panel-subtitle">Build a resume in CareerMirror or upload PDF/DOC files. Mark one as default for applications.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('candidate.resumes.create') }}" class="btn btn-primary">Resume builder</a>
                <a href="{{ route('candidate.resumes.upload') }}" class="btn btn-outline-secondary">Upload resume</a>
            </div>
        </div>

        <div class="panel-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Default</th>
                            <th>Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resumes as $resume)
                            <tr>
                                <td class="fw-semibold">{{ $resume->title }}</td>
                                <td>{{ $resume->source->label() }}</td>
                                <td>
                                    @if ($resume->is_default)
                                        <span class="badge text-bg-primary">Default</span>
                                    @else
                                        <form method="POST" action="{{ route('candidate.resumes.default', $resume) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-link px-0">Make default</button>
                                        </form>
                                    @endif
                                </td>
                                <td class="text-secondary">{{ $resume->updated_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('candidate.resumes.preview', $resume) }}" class="btn btn-sm btn-light">Preview</a>
                                    @if ($resume->isBuilt())
                                        <a href="{{ route('candidate.resumes.edit', $resume) }}" class="btn btn-sm btn-light">Edit</a>
                                    @else
                                        <a href="{{ route('candidate.resumes.download', $resume) }}" class="btn btn-sm btn-light">Download</a>
                                    @endif
                                    <form method="POST" action="{{ route('candidate.resumes.destroy', $resume) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this resume?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    No resumes yet. <a href="{{ route('candidate.resumes.create') }}">Build one</a>
                                    or <a href="{{ route('candidate.resumes.upload') }}">upload a file</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
