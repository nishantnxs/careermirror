<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Models\Concerns\HasAccountStatus;
use Database\Factories\EmployerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employer extends Authenticatable
{
    /** @use HasFactory<EmployerFactory> */
    use HasAccountStatus, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'google_id',
        'phone',
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
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(EmployerSubscription::class);
    }

    public function currentSubscription(): ?EmployerSubscription
    {
        return $this->subscriptions()->active()->latest('id')->first();
    }

    public function subscriptionAvailableForPosting(): ?EmployerSubscription
    {
        return $this->subscriptions()
            ->active()
            ->withRemainingCredits()
            ->latest('id')
            ->first();
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class, 'domain_employer')->withTimestamps();
    }

    public function grantActiveDomains(): void
    {
        $domainIds = Domain::query()->active()->pluck('id')->all();

        if ($domainIds !== []) {
            $this->domains()->syncWithoutDetaching($domainIds);
        }
    }

    /** @return list<int> */
    public function allowedDomainIds(): array
    {
        return $this->domains()
            ->active()
            ->pluck('domains.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return Collection<int, Domain>
     */
    public function publishingDomains(): Collection
    {
        return $this->domains()->active()->orderBy('host')->get();
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $query) use ($term) {
            $query->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('company_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        });
    }
}
