<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label required">Name</label>
        <input type="text" name="name" id="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $category->name) }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="slug" class="form-label">Slug</label>
        <input type="text" name="slug" id="slug"
               class="form-control @error('slug') is-invalid @enderror"
               value="{{ old('slug', $category->slug) }}"
               placeholder="Leave blank to generate from the name">
        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="sort_order" class="form-label">Sort order</label>
        <input type="number" min="0" name="sort_order" id="sort_order"
               class="form-control @error('sort_order') is-invalid @enderror"
               value="{{ old('sort_order', $category->sort_order ?? 0) }}">
        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   class="form-check-input" @checked(old('is_active', $category->is_active ?? true))>
            <label for="is_active" class="form-check-label">Active in employer listing</label>
        </div>
    </div>
</div>
