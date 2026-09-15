<?php

namespace App\Services;

use App\Enums\ResumeSource;
use App\Models\Candidate;
use App\Models\CandidateResume;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CandidateResumeService
{
    /**
     * @param  array<string, mixed>  $content
     */
    public function createBuilt(Candidate $candidate, string $title, array $content, bool $makeDefault = false): CandidateResume
    {
        return DB::transaction(function () use ($candidate, $title, $content, $makeDefault) {
            $resume = $candidate->resumes()->create([
                'title' => $title,
                'source' => ResumeSource::Builder,
                'content' => array_replace(CandidateResume::emptyContent(), $content),
                'is_default' => false,
            ]);

            if ($makeDefault || $candidate->resumes()->count() === 1) {
                $this->makeDefault($resume);
            }

            return $resume->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $content
     */
    public function updateBuilt(CandidateResume $resume, string $title, array $content): CandidateResume
    {
        $resume->update([
            'title' => $title,
            'content' => array_replace(CandidateResume::emptyContent(), $content),
        ]);

        return $resume->fresh();
    }

    public function storeUpload(
        Candidate $candidate,
        string $title,
        UploadedFile $file,
        bool $makeDefault = false,
    ): CandidateResume {
        return DB::transaction(function () use ($candidate, $title, $file, $makeDefault) {
            $path = $file->store('resumes/'.$candidate->id, 'public');

            $resume = $candidate->resumes()->create([
                'title' => $title,
                'source' => ResumeSource::Upload,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize() ?: null,
                'content' => null,
                'is_default' => false,
            ]);

            if ($makeDefault || $candidate->resumes()->count() === 1) {
                $this->makeDefault($resume);
            }

            return $resume->fresh();
        });
    }

    public function makeDefault(CandidateResume $resume): void
    {
        DB::transaction(function () use ($resume) {
            CandidateResume::query()
                ->where('candidate_id', $resume->candidate_id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $resume->update(['is_default' => true]);
        });
    }

    public function delete(CandidateResume $resume): void
    {
        DB::transaction(function () use ($resume) {
            $candidateId = $resume->candidate_id;
            $wasDefault = $resume->is_default;

            $resume->delete();

            if ($wasDefault) {
                $next = CandidateResume::query()
                    ->where('candidate_id', $candidateId)
                    ->latest('id')
                    ->first();

                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }
        });
    }

    public function replaceUploadFile(CandidateResume $resume, UploadedFile $file): CandidateResume
    {
        if ($resume->file_path) {
            Storage::disk('public')->delete($resume->file_path);
        }

        $path = $file->store('resumes/'.$resume->candidate_id, 'public');

        $resume->update([
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize() ?: null,
        ]);

        return $resume->fresh();
    }

    /**
     * Normalize builder form arrays into clean content.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function normalizeBuilderContent(array $input): array
    {
        $skills = $input['skills'] ?? [];
        if (is_string($skills)) {
            $skills = preg_split('/[\n,]+/', $skills) ?: [];
        }

        $achievements = $input['achievements'] ?? [];
        if (is_string($achievements)) {
            $achievements = preg_split("/\r\n|\n|\r/", $achievements) ?: [];
        }

        return [
            'summary' => trim((string) ($input['summary'] ?? '')),
            'experience' => $this->cleanRows(
                $input['experience'] ?? [],
                ['title', 'company', 'location', 'period', 'start_date', 'end_date', 'description'],
                ['current']
            ),
            'education' => $this->cleanRows(
                $input['education'] ?? [],
                ['degree', 'institution', 'location', 'period', 'start_date', 'end_date', 'description']
            ),
            'skills' => collect($skills)
                ->map(fn ($skill) => trim((string) $skill))
                ->filter()
                ->values()
                ->all(),
            'certifications' => $this->cleanRows($input['certifications'] ?? [], ['name', 'issuer', 'year']),
            'languages' => $this->cleanRows($input['languages'] ?? [], ['name', 'proficiency']),
            'projects' => $this->cleanRows($input['projects'] ?? [], ['name', 'url', 'description']),
            'achievements' => collect($achievements)
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->values()
                ->all(),
            'other' => trim((string) ($input['other'] ?? '')),
        ];
    }

    /**
     * @param  list<string>  $fields
     * @param  list<string>  $booleanFields
     * @return list<array<string, mixed>>
     */
    private function cleanRows(mixed $rows, array $fields, array $booleanFields = []): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row) use ($fields, $booleanFields) {
                $cleaned = [];

                foreach ($fields as $field) {
                    $cleaned[$field] = trim((string) ($row[$field] ?? ''));
                }

                foreach ($booleanFields as $field) {
                    $cleaned[$field] = filter_var($row[$field] ?? false, FILTER_VALIDATE_BOOLEAN);
                }

                return $cleaned;
            })
            ->filter(function (array $row) {
                return collect($row)
                    ->except(['current'])
                    ->contains(fn ($value) => filled($value));
            })
            ->values()
            ->all();
    }
}
