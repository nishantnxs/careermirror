<div class="resume-item mb-3" data-row>
    <div class="row g-2">
        <div class="col-md-5">
            <label>Name</label>
            <input type="text" name="content[certifications][{{ $index }}][name]" class="form-control" value="{{ $row['name'] ?? '' }}">
        </div>
        <div class="col-md-4">
            <label>Issuer</label>
            <input type="text" name="content[certifications][{{ $index }}][issuer]" class="form-control" value="{{ $row['issuer'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label>Year</label>
            <input type="text" name="content[certifications][{{ $index }}][year]" class="form-control" value="{{ $row['year'] ?? '' }}">
        </div>
    </div>
    <button type="button" class="resume-remove-btn" data-remove-row>
        <i class="fa-regular fa-trash-can"></i>Remove
    </button>
</div>
