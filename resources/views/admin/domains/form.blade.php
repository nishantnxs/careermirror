<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label required">Display name</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $domain->name) }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="website_name" class="form-label">Website name</label>
        <input type="text" name="website_name" id="website_name" class="form-control @error('website_name') is-invalid @enderror"
               value="{{ old('website_name', $domain->website_name) }}">
        @error('website_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="host" class="form-label required">Domain host</label>
        <input type="text" name="host" id="host" class="form-control @error('host') is-invalid @enderror"
               value="{{ old('host', $domain->host) }}" placeholder="jobs.example.com" required>
        @error('host') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label for="url" class="form-label required">Domain URL</label>
        <input type="url" name="url" id="url" class="form-control @error('url') is-invalid @enderror"
               value="{{ old('url', $domain->url) }}" placeholder="https://jobs.example.com" required>
        @error('url') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="status" class="form-label required">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $domain->status?->value) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check mb-2">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" class="form-check-input" name="is_default" id="is_default" value="1"
                   @checked(old('is_default', $domain->is_default))>
            <label class="form-check-label" for="is_default">Default domain (localhost fallback)</label>
        </div>
    </div>
    @unless ($domain->exists)
        <div class="col-12">
            <div class="form-check">
                <input type="hidden" name="grant_all_employers" value="0">
                <input type="checkbox" class="form-check-input" name="grant_all_employers" id="grant_all_employers" value="1"
                       @checked(old('grant_all_employers', true))>
                <label class="form-check-label" for="grant_all_employers">
                    Allow all existing employers to publish jobs on this domain
                </label>
            </div>
        </div>
    @endunless
    <div class="col-md-6">
        <label for="seo_title" class="form-label">SEO title</label>
        <input type="text" name="seo_title" id="seo_title" class="form-control"
               value="{{ old('seo_title', $domain->seo_title) }}">
    </div>
    <div class="col-md-6">
        <label for="seo_keywords" class="form-label">SEO keywords</label>
        <input type="text" name="seo_keywords" id="seo_keywords" class="form-control"
               value="{{ old('seo_keywords', $domain->seo_keywords) }}">
    </div>
    <div class="col-12">
        <label for="seo_description" class="form-label">SEO description</label>
        <textarea name="seo_description" id="seo_description" rows="3" class="form-control">{{ old('seo_description', $domain->seo_description) }}</textarea>
    </div>
    <div class="col-md-6">
        <label for="logo" class="form-label">Logo</label>
        <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror">
        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if ($domain->logo_path)
            <div class="form-check mt-2">
                <input type="checkbox" class="form-check-input" name="remove_logo" id="remove_logo" value="1">
                <label class="form-check-label" for="remove_logo">Remove current logo</label>
            </div>
        @endif
    </div>
    <div class="col-md-6">
        <label for="favicon" class="form-label">Favicon</label>
        <input type="file" name="favicon" id="favicon" class="form-control @error('favicon') is-invalid @enderror">
        @error('favicon') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if ($domain->favicon_path)
            <div class="form-check mt-2">
                <input type="checkbox" class="form-check-input" name="remove_favicon" id="remove_favicon" value="1">
                <label class="form-check-label" for="remove_favicon">Remove current favicon</label>
            </div>
        @endif
    </div>
</div>
