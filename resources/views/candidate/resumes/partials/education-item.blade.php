<div class="resume-item mb-3" data-row>
    <div class="row g-2">
        <div class="col-md-4">
            <label>School</label>
            <input type="text" name="content[education][{{ $index }}][institution]" class="form-control" value="{{ $row['institution'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <label>Credential</label>
            <input type="text" name="content[education][{{ $index }}][degree]" class="form-control" value="{{ $row['degree'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <label>Period</label>
            <input type="text" name="content[education][{{ $index }}][period]" class="form-control"
                   value="{{ $row['period'] ?? '' }}" placeholder="2020 – 2022">
        </div>
    </div>
    <button type="button" class="resume-remove-btn" data-remove-row>
        <i class="fa-regular fa-trash-can"></i>Remove
    </button>
</div>
