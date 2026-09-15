<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Models\Concerns\HasAccountStatus;
use App\Notifications\Candidate\ResetPasswordNotification;
use Database\Factories\CandidateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Candidate extends Authenticatable
{
    /** @use HasFactory<CandidateFactory> */
    use HasAccountStatus, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'phone',
        'headline',
        'summary',
        'location',
        'current_title',
        'years_of_experience',
        'website',
        'linkedin_url',
        'date_of_birth',
        'preferred_job_type',
        'expected_salary_min',
        'expected_salary_max',
        'currency',
        'avatar_path',
        'password',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => AccountStatus::class,
            'last_login_at' => 'datetime',
            'date_of_birth' => 'date',
            'expected_salary_min' => 'decimal:2',
            'expected_salary_max' => 'decimal:2',
            'years_of_experience' => 'integer',
        ];
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(CandidateResume::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function savedJobs(): HasMany
    {
        return $this->hasMany(SavedJob::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function defaultResume(): ?CandidateResume
    {
        return $this->resumes()->where('is_default', true)->first()
            ?? $this->resumes()->latest('id')->first();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
