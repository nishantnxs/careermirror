<div class="resume-item mb-3" data-row>
    <div class="row g-2">
        <div class="col-md-6">
            <label>Language</label>
            <input type="text" name="content[languages][{{ $index }}][name]" class="form-control" value="{{ $row['name'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label>Proficiency</label>
            <input type="text" name="content[languages][{{ $index }}][proficiency]" class="form-control"
                   value="{{ $row['proficiency'] ?? '' }}" placeholder="Fluent">
        </div>
    </div>
    <button type="button" class="resume-remove-btn" data-remove-row>
        <i class="fa-regular fa-trash-can"></i>Remove
    </button>
</div>
