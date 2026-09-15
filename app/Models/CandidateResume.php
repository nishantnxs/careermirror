<?php

namespace App\Models;

use App\Enums\ResumeSource;
use Database\Factories\CandidateResumeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CandidateResume extends Model
{
    /** @use HasFactory<CandidateResumeFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'title',
        'source',
        'is_default',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'content',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'source' => ResumeSource::class,
            'is_default' => 'boolean',
            'content' => 'array',
            'file_size' => 'integer',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function isBuilt(): bool
    {
        return $this->source === ResumeSource::Builder;
    }

    public function isUploaded(): bool
    {
        return $this->source === ResumeSource::Upload;
    }

    public function applicationOptionLabel(): string
    {
        $label = $this->title;

        if ($this->is_default) {
            $label .= ' (default)';
        }

        return $label.' — '.$this->source->label();
    }

    public function fileUrl(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function emptyContent(): array
    {
        return [
            'summary' => '',
            'experience' => [],
            'education' => [],
            'skills' => [],
            'certifications' => [],
            'languages' => [],
            'projects' => [],
            'achievements' => [],
            'other' => '',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (CandidateResume $resume): void {
            if ($resume->file_path) {
                Storage::disk('public')->delete($resume->file_path);
            }
        });
    }
}
