@extends('layouts.marketing')

@section('title', 'My Profile')

@section('content')
<section class="candidate-panel">
    <div class="container">
        <div class="mb-4">
            <h1 class="panel-title">My profile</h1>
            <p class="panel-subtitle">Personal details and career information employers will see.</p>
        </div>

        <form method="POST" action="{{ route('candidate.profile.update') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-lg-8">
                <div class="panel-card mb-3">
                    <div class="panel-card-header">Contact information</div>
                    <div class="panel-card-body row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label required">Full name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $candidate->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label required">Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $candidate->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $candidate->phone) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror"
                                   value="{{ old('location', $candidate->location) }}" placeholder="City, Country">
                            @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" name="website" id="website" class="form-control @error('website') is-invalid @enderror"
                                   value="{{ old('website', $candidate->website) }}" placeholder="https://">
                            @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="linkedin_url" class="form-label">LinkedIn</label>
                            <input type="url" name="linkedin_url" id="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror"
                                   value="{{ old('linkedin_url', $candidate->linkedin_url) }}" placeholder="https://linkedin.com/in/...">
                            @error('linkedin_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth', optional($candidate->date_of_birth)->format('Y-m-d')) }}">
                            @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="panel-card mb-3">
                    <div class="panel-card-header">Professional information</div>
                    <div class="panel-card-body row g-3">
                        <div class="col-12">
                            <label for="headline" class="form-label">Professional headline</label>
                            <input type="text" name="headline" id="headline" class="form-control @error('headline') is-invalid @enderror"
                                   value="{{ old('headline', $candidate->headline) }}" placeholder="e.g. Full-stack Laravel developer">
                            @error('headline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="summary" class="form-label">Career summary</label>
                            <textarea name="summary" id="summary" rows="5" class="form-control @error('summary') is-invalid @enderror">{{ old('summary', $candidate->summary) }}</textarea>
                            @error('summary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="current_title" class="form-label">Current title</label>
                            <input type="text" name="current_title" id="current_title" class="form-control @error('current_title') is-invalid @enderror"
                                   value="{{ old('current_title', $candidate->current_title) }}">
                            @error('current_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="years_of_experience" class="form-label">Years of experience</label>
                            <input type="number" min="0" max="60" name="years_of_experience" id="years_of_experience"
                                   class="form-control @error('years_of_experience') is-invalid @enderror"
                                   value="{{ old('years_of_experience', $candidate->years_of_experience) }}">
                            @error('years_of_experience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="preferred_job_type" class="form-label">Preferred job type</label>
                            <select name="preferred_job_type" id="preferred_job_type" class="form-select @error('preferred_job_type') is-invalid @enderror">
                                <option value="">Select</option>
                                @foreach ($jobTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('preferred_job_type', $candidate->preferred_job_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('preferred_job_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="currency" class="form-label required">Currency</label>
                            <select name="currency" id="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                @foreach (['INR', 'USD', 'EUR', 'GBP', 'AED'] as $code)
                                    <option value="{{ $code }}" @selected(old('currency', $candidate->currency ?: 'INR') === $code)>{{ $code }}</option>
                                @endforeach
                            </select>
                            @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="expected_salary_min" class="form-label">Expected salary min</label>
                            <input type="number" step="0.01" min="0" name="expected_salary_min" id="expected_salary_min"
                                   class="form-control @error('expected_salary_min') is-invalid @enderror"
                                   value="{{ old('expected_salary_min', $candidate->expected_salary_min) }}">
                            @error('expected_salary_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="expected_salary_max" class="form-label">Expected salary max</label>
                            <input type="number" step="0.01" min="0" name="expected_salary_max" id="expected_salary_max"
                                   class="form-control @error('expected_salary_max') is-invalid @enderror"
                                   value="{{ old('expected_salary_max', $candidate->expected_salary_max) }}">
                            @error('expected_salary_max') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel-card mb-3">
                    <div class="panel-card-header">Photo</div>
                    <div class="panel-card-body">
                        @if ($candidate->avatar_url)
                            <img src="{{ $candidate->avatar_url }}" alt="Avatar" class="img-fluid rounded mb-3" style="max-height:140px">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="remove_avatar" id="remove_avatar" value="1">
                                <label class="form-check-label" for="remove_avatar">Remove current photo</label>
                            </div>
                        @endif
                        <label for="avatar" class="form-label">Upload photo</label>
                        <input type="file" name="avatar" id="avatar" accept="image/*"
                               class="form-control @error('avatar') is-invalid @enderror">
                        @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">JPG, PNG or WebP up to 2MB.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Save profile</button>
            </div>
        </form>
    </div>
</section>
@endsection
