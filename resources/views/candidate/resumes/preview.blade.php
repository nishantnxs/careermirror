@extends('layouts.marketing')

@section('title', 'Resume preview')

@section('content')
@php
    $content = $resume->content ?? [];
@endphp

<section class="resume-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">
                <div class="resume-heading mb-4 d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h1 class="mb-2">{{ $resume->title }}</h1>
                        <p class="mb-0">
                            {{ $resume->source->label() }}
                            @if ($resume->is_default)
                                · Default resume
                            @endif
                        </p>
                    </div>
                    @php
                        $resumeBackUrl = $resumeBackUrl ?? route('candidate.resumes.index');
                        $resumeBackLabel = $resumeBackLabel ?? 'All resumes';
                        $resumeEditUrl = $resumeEditUrl ?? ($resume->isBuilt() ? route('candidate.resumes.edit', $resume) : null);
                        $resumeDownloadUrl = $resumeDownloadUrl ?? ($resume->isUploaded() ? route('candidate.resumes.download', $resume) : null);
                    @endphp
                    <div class="d-flex flex-wrap gap-2">
                        @if ($resumeEditUrl)
                            <a href="{{ $resumeEditUrl }}" class="resume-save-btn text-decoration-none">Edit</a>
                        @elseif ($resumeDownloadUrl)
                            <a href="{{ $resumeDownloadUrl }}" class="resume-save-btn text-decoration-none">Download file</a>
                        @endif
                        <a href="{{ $resumeBackUrl }}" class="btn btn-outline-secondary">{{ $resumeBackLabel }}</a>
                    </div>
                </div>

                <div class="resume-box">
                    @if ($resume->isUploaded())
                        <p class="mb-2"><strong>File:</strong> {{ $resume->original_filename }}</p>
                        <p class="text-secondary small mb-0">
                            Uploaded {{ $resume->created_at->format('d M Y, h:i A') }}
                            @if ($resume->file_size)
                                · {{ number_format($resume->file_size / 1024, 1) }} KB
                            @endif
                        </p>
                    @else
                        <div class="mb-4">
                            <h2 class="h4 mb-1">{{ $candidate->name }}</h2>
                            @if ($candidate->headline)
                                <p class="text-secondary mb-1">{{ $candidate->headline }}</p>
                            @endif
                            <p class="small text-secondary mb-0">
                                {{ collect([$candidate->email, $candidate->phone, $candidate->location])->filter()->implode(' · ') }}
                            </p>
                        </div>

                        @if (filled($content['summary'] ?? null))
                            <h3 class="resume-box-title mb-2">Professional summary</h3>
                            <p>{{ $content['summary'] }}</p>
                        @endif

                        @if (filled($content['skills'] ?? null))
                            <h3 class="resume-box-title mt-4 mb-2">Skills</h3>
                            <p>{{ implode(', ', $content['skills']) }}</p>
                        @endif

                        @if (filled($content['experience'] ?? null))
                            <h3 class="resume-box-title mt-4 mb-3">Experience</h3>
                            @foreach ($content['experience'] as $row)
                                @php
                                    $period = $row['period'] ?? collect([
                                        $row['start_date'] ?? null,
                                        ($row['current'] ?? false) ? 'Present' : ($row['end_date'] ?? null),
                                    ])->filter()->implode(' – ');
                                @endphp
                                <div class="mb-3">
                                    <div class="fw-semibold">
                                        {{ $row['title'] ?? '' }}
                                        @if (filled($row['company'] ?? null))
                                            · {{ $row['company'] }}
                                        @endif
                                    </div>
                                    <div class="small text-secondary">{{ collect([$row['location'] ?? null, $period])->filter()->implode(' · ') }}</div>
                                    @if (filled($row['description'] ?? null))
                                        <p class="mb-0 mt-1">{{ $row['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        @if (filled($content['education'] ?? null))
                            <h3 class="resume-box-title mt-4 mb-3">Education</h3>
                            @foreach ($content['education'] as $row)
                                @php
                                    $period = $row['period'] ?? collect([
                                        $row['start_date'] ?? null,
                                        $row['end_date'] ?? null,
                                    ])->filter()->implode(' – ');
                                @endphp
                                <div class="mb-3">
                                    <div class="fw-semibold">
                                        {{ $row['degree'] ?? '' }}
                                        @if (filled($row['institution'] ?? null))
                                            · {{ $row['institution'] }}
                                        @endif
                                    </div>
                                    <div class="small text-secondary">{{ collect([$row['location'] ?? null, $period])->filter()->implode(' · ') }}</div>
                                    @if (filled($row['description'] ?? null))
                                        <p class="mb-0 mt-1">{{ $row['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        @if (filled($content['certifications'] ?? null))
                            <h3 class="resume-box-title mt-4 mb-2">Certifications</h3>
                            <ul class="mb-0">
                                @foreach ($content['certifications'] as $row)
                                    <li>
                                        {{ $row['name'] ?? '' }}
                                        @if (filled($row['issuer'] ?? null))
                                            — {{ $row['issuer'] }}
                                        @endif
                                        @if (filled($row['year'] ?? null))
                                            ({{ $row['year'] }})
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if (filled($content['languages'] ?? null))
                            <h3 class="resume-box-title mt-4 mb-2">Languages</h3>
                            <ul class="mb-0">
                                @foreach ($content['languages'] as $row)
                                    <li>
                                        {{ $row['name'] ?? '' }}
                                        @if (filled($row['proficiency'] ?? null))
                                            — {{ $row['proficiency'] }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if (filled($content['projects'] ?? null))
                            <h3 class="resume-box-title mt-4 mb-3">Projects</h3>
                            @foreach ($content['projects'] as $row)
                                <div class="mb-3">
                                    <div class="fw-semibold">
                                        {{ $row['name'] ?? '' }}
                                        @if (filled($row['url'] ?? null))
                                            · <a href="{{ $row['url'] }}" target="_blank" rel="noopener">Link</a>
                                        @endif
                                    </div>
                                    @if (filled($row['description'] ?? null))
                                        <p class="mb-0 mt-1">{{ $row['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        @if (filled($content['achievements'] ?? null))
                            <h3 class="resume-box-title mt-4 mb-2">Achievements</h3>
                            <ul class="mb-0">
                                @foreach ($content['achievements'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if (filled($content['other'] ?? null))
                            <h3 class="resume-box-title mt-4 mb-2">Other relevant information</h3>
                            <p class="mb-0">{{ $content['other'] }}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
