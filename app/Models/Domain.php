<?php

namespace App\Models;

use App\Enums\DomainStatus;
use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Domain extends Model
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'url',
        'website_name',
        'logo_path',
        'favicon_path',
        'status',
        'is_default',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected function casts(): array
    {
        return [
            'status' => DomainStatus::class,
            'is_default' => 'boolean',
        ];
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'domain_plan')->withTimestamps();
    }

    public function jobPostings(): BelongsToMany
    {
        return $this->belongsToMany(JobPosting::class, 'domain_job_posting')->withTimestamps();
    }

    public function employers(): BelongsToMany
    {
        return $this->belongsToMany(Employer::class, 'domain_employer')->withTimestamps();
    }

    public function grantToAllEmployers(): void
    {
        $employerIds = Employer::query()->pluck('id')->all();

        if ($employerIds !== []) {
            $this->employers()->syncWithoutDetaching($employerIds);
        }
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', DomainStatus::Active);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $query) use ($term) {
            $query->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('host', 'like', "%{$term}%")
                    ->orWhere('website_name', 'like', "%{$term}%")
                    ->orWhere('url', 'like', "%{$term}%");
            });
        });
    }

    public function isActive(): bool
    {
        return $this->status === DomainStatus::Active;
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon_path ? Storage::disk('public')->url($this->favicon_path) : null;
    }

    public function displayName(): string
    {
        return $this->website_name ?: $this->name;
    }

    /**
     * Normalize a host / URL input into a bare hostname.
     */
    public static function normalizeHost(string $value): string
    {
        $value = trim(mb_strtolower($value));

        if (str_contains($value, '://')) {
            $host = parse_url($value, PHP_URL_HOST);

            return $host ? mb_strtolower($host) : $value;
        }

        $value = preg_replace('#^www\.#', '', $value) ?? $value;

        return explode('/', explode(':', $value)[0])[0];
    }
}
