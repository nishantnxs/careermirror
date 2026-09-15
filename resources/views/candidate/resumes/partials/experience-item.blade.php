<div class="resume-item mb-3" data-row>
    <div class="row g-2">
        <div class="col-md-4">
            <label>Job title</label>
            <input type="text" name="content[experience][{{ $index }}][title]" class="form-control" value="{{ $row['title'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <label>Company</label>
            <input type="text" name="content[experience][{{ $index }}][company]" class="form-control" value="{{ $row['company'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <label>Period</label>
            <input type="text" name="content[experience][{{ $index }}][period]" class="form-control"
                   value="{{ $row['period'] ?? '' }}" placeholder="Jan 2024 – Present">
        </div>
        <div class="col-12">
            <label>Description</label>
            <textarea name="content[experience][{{ $index }}][description]" class="form-control resume-description" rows="3">{{ $row['description'] ?? '' }}</textarea>
        </div>
    </div>
    <button type="button" class="resume-remove-btn" data-remove-row>
        <i class="fa-regular fa-trash-can"></i>Remove
    </button>
</div>
