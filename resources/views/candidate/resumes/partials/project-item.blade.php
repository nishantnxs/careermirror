<div class="resume-item mb-3" data-row>
    <div class="row g-2">
        <div class="col-md-6">
            <label>Project name</label>
            <input type="text" name="content[projects][{{ $index }}][name]" class="form-control" value="{{ $row['name'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label>URL</label>
            <input type="url" name="content[projects][{{ $index }}][url]" class="form-control"
                   value="{{ $row['url'] ?? '' }}" placeholder="https://">
        </div>
        <div class="col-12">
            <label>Description</label>
            <textarea name="content[projects][{{ $index }}][description]" class="form-control" rows="2">{{ $row['description'] ?? '' }}</textarea>
        </div>
    </div>
    <button type="button" class="resume-remove-btn" data-remove-row>
        <i class="fa-regular fa-trash-can"></i>Remove
    </button>
</div>
