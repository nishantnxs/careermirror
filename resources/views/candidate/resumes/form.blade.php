@php
    $candidate = $candidate ?? auth('candidate')->user();
    $content = old('content', $resume->content ?? \App\Models\CandidateResume::emptyContent());

    $experienceBlank = ['title' => '', 'company' => '', 'period' => '', 'description' => ''];
    $educationBlank = ['institution' => '', 'degree' => '', 'period' => ''];
    $certificationBlank = ['name' => '', 'issuer' => '', 'year' => ''];
    $languageBlank = ['name' => '', 'proficiency' => ''];
    $projectBlank = ['name' => '', 'url' => '', 'description' => ''];

    $formatPeriod = function (array $row): string {
        if (! empty($row['period'])) {
            return (string) $row['period'];
        }

        $start = trim((string) ($row['start_date'] ?? ''));
        $end = ! empty($row['current']) ? 'Present' : trim((string) ($row['end_date'] ?? ''));

        return collect([$start, $end])->filter()->implode(' – ');
    };

    $normalizeRows = function (array $rows, callable $mapper): array {
        $items = collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map($mapper)
            ->values()
            ->all();

        return $items !== [] ? $items : [];
    };

    $experience = $normalizeRows(
        old('content.experience', $content['experience'] ?? []),
        fn (array $row) => [
            'title' => $row['title'] ?? '',
            'company' => $row['company'] ?? '',
            'period' => $formatPeriod($row),
            'description' => $row['description'] ?? '',
        ]
    );
    $education = $normalizeRows(
        old('content.education', $content['education'] ?? []),
        fn (array $row) => [
            'institution' => $row['institution'] ?? '',
            'degree' => $row['degree'] ?? '',
            'period' => $formatPeriod($row),
        ]
    );
    $certifications = $normalizeRows(
        old('content.certifications', $content['certifications'] ?? []),
        fn (array $row) => [
            'name' => $row['name'] ?? '',
            'issuer' => $row['issuer'] ?? '',
            'year' => $row['year'] ?? '',
        ]
    );
    $languages = $normalizeRows(
        old('content.languages', $content['languages'] ?? []),
        fn (array $row) => [
            'name' => $row['name'] ?? '',
            'proficiency' => $row['proficiency'] ?? '',
        ]
    );
    $projects = $normalizeRows(
        old('content.projects', $content['projects'] ?? []),
        fn (array $row) => [
            'name' => $row['name'] ?? '',
            'url' => $row['url'] ?? '',
            'description' => $row['description'] ?? '',
        ]
    );

    $skillsValue = old('content.skills', is_array($content['skills'] ?? null) ? implode(', ', $content['skills']) : ($content['skills'] ?? ''));
    $achievementsValue = old('content.achievements', is_array($content['achievements'] ?? null) ? implode("\n", $content['achievements']) : ($content['achievements'] ?? ''));
@endphp

@if ($errors->any())
    <div class="alert alert-danger py-2 small mb-4">{{ $errors->first() }}</div>
@endif

<div class="resume-box mb-4">
    <h3 class="resume-box-title mb-4">Basics</h3>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="name">Full name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $candidate->name) }}" required autocomplete="name">
        </div>
        <div class="col-md-6">
            <label for="headline">Headline</label>
            <input type="text" name="headline" id="headline" class="form-control @error('headline') is-invalid @enderror"
                   value="{{ old('headline', $candidate->headline) }}" autocomplete="organization-title">
        </div>
        <div class="col-md-6">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $candidate->email) }}" required autocomplete="email">
        </div>
        <div class="col-md-6">
            <label for="phone">Phone</label>
            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $candidate->phone) }}" autocomplete="tel">
        </div>
        <div class="col-md-6">
            <label for="location">Location</label>
            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror"
                   value="{{ old('location', $candidate->location) }}" autocomplete="address-level2">
        </div>
        <div class="col-md-6">
            <label for="skills">Skills (comma separated)</label>
            <input type="text" name="content[skills]" id="skills" class="form-control"
                   value="{{ $skillsValue }}" placeholder="HTML, CSS, JavaScript">
        </div>
        <div class="col-12">
            <label for="summary">Professional summary</label>
            <textarea name="content[summary]" id="summary" class="form-control" rows="4">{{ old('content.summary', $content['summary'] ?? '') }}</textarea>
        </div>
        <div class="col-md-8">
            <label for="title">Resume title</label>
            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                   value="{{ old('title', $resume->title ?: 'My resume') }}" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check mb-2">
                <input type="hidden" name="is_default" value="0">
                <input type="checkbox" class="form-check-input" name="is_default" id="is_default" value="1"
                       @checked(old('is_default', $resume->is_default ?? true))>
                <label class="form-check-label" for="is_default">Set as default resume</label>
            </div>
        </div>
    </div>
</div>

<div class="resume-box mb-4" data-repeatable="experience">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="resume-box-title mb-0">Experience</h3>
        <button type="button" class="resume-add-btn" data-add-row="experience"><span>+</span> Add role</button>
    </div>
    <div class="resume-items" data-items="experience">
        @forelse ($experience as $index => $row)
            @include('candidate.resumes.partials.experience-item', ['index' => $index, 'row' => $row])
        @empty
            @include('candidate.resumes.partials.experience-item', ['index' => 0, 'row' => $experienceBlank])
        @endforelse
    </div>
</div>

<div class="resume-box mb-4" data-repeatable="education">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="resume-box-title mb-0">Education</h3>
        <button type="button" class="resume-add-btn" data-add-row="education"><span>+</span> Add education</button>
    </div>
    <div class="resume-items" data-items="education">
        @forelse ($education as $index => $row)
            @include('candidate.resumes.partials.education-item', ['index' => $index, 'row' => $row])
        @empty
            @include('candidate.resumes.partials.education-item', ['index' => 0, 'row' => $educationBlank])
        @endforelse
    </div>
</div>

<div class="resume-box mb-4" data-repeatable="certifications">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="resume-box-title mb-0">Certifications</h3>
        <button type="button" class="resume-add-btn" data-add-row="certifications"><span>+</span> Add certification</button>
    </div>
    <div class="resume-items" data-items="certifications">
        @forelse ($certifications as $index => $row)
            @include('candidate.resumes.partials.certification-item', ['index' => $index, 'row' => $row])
        @empty
            @include('candidate.resumes.partials.certification-item', ['index' => 0, 'row' => $certificationBlank])
        @endforelse
    </div>
</div>

<div class="resume-box mb-4" data-repeatable="languages">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="resume-box-title mb-0">Languages</h3>
        <button type="button" class="resume-add-btn" data-add-row="languages"><span>+</span> Add language</button>
    </div>
    <div class="resume-items" data-items="languages">
        @forelse ($languages as $index => $row)
            @include('candidate.resumes.partials.language-item', ['index' => $index, 'row' => $row])
        @empty
            @include('candidate.resumes.partials.language-item', ['index' => 0, 'row' => $languageBlank])
        @endforelse
    </div>
</div>

<div class="resume-box mb-4" data-repeatable="projects">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="resume-box-title mb-0">Projects</h3>
        <button type="button" class="resume-add-btn" data-add-row="projects"><span>+</span> Add project</button>
    </div>
    <div class="resume-items" data-items="projects">
        @forelse ($projects as $index => $row)
            @include('candidate.resumes.partials.project-item', ['index' => $index, 'row' => $row])
        @empty
            @include('candidate.resumes.partials.project-item', ['index' => 0, 'row' => $projectBlank])
        @endforelse
    </div>
</div>

<div class="resume-box mb-4">
    <h3 class="resume-box-title mb-4">Achievements</h3>
    <label for="achievements">One achievement per line</label>
    <textarea name="content[achievements]" id="achievements" class="form-control" rows="4"
              placeholder="Shipped feature X">{{ $achievementsValue }}</textarea>
</div>

<div class="resume-box mb-4">
    <h3 class="resume-box-title mb-4">Other relevant information</h3>
    <label for="other">Anything else employers should know</label>
    <textarea name="content[other]" id="other" class="form-control" rows="3">{{ old('content.other', $content['other'] ?? '') }}</textarea>
</div>

<template id="tpl-experience">
    @include('candidate.resumes.partials.experience-item', ['index' => '__INDEX__', 'row' => $experienceBlank])
</template>
<template id="tpl-education">
    @include('candidate.resumes.partials.education-item', ['index' => '__INDEX__', 'row' => $educationBlank])
</template>
<template id="tpl-certifications">
    @include('candidate.resumes.partials.certification-item', ['index' => '__INDEX__', 'row' => $certificationBlank])
</template>
<template id="tpl-languages">
    @include('candidate.resumes.partials.language-item', ['index' => '__INDEX__', 'row' => $languageBlank])
</template>
<template id="tpl-projects">
    @include('candidate.resumes.partials.project-item', ['index' => '__INDEX__', 'row' => $projectBlank])
</template>
