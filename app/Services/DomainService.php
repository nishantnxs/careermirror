<?php

namespace App\Services;

use App\Enums\DomainStatus;
use App\Models\Domain;
use App\Support\CurrentDomain;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DomainService
{
    public const ADMIN_SESSION_KEY = 'admin_context_domain_id';

    public function __construct(public CurrentDomain $current) {}

    public function resolveFromRequest(Request $request): ?Domain
    {
        $host = Domain::normalizeHost($request->getHost());

        $domain = Domain::query()
            ->where(function ($query) use ($host) {
                $query->where('host', $host)
                    ->orWhere('host', 'www.'.$host);
            })
            ->first();

        if ($domain === null) {
            $domain = Domain::query()->where('is_default', true)->first()
                ?? Domain::query()->orderBy('id')->first();
        }

        return $domain;
    }

    public function bind(?Domain $domain): void
    {
        $this->current->set($domain);
    }

    public function current(): ?Domain
    {
        return $this->current->get();
    }

    public function adminContextDomainId(): ?int
    {
        $id = Session::get(self::ADMIN_SESSION_KEY);

        return $id !== null ? (int) $id : null;
    }

    public function setAdminContext(?int $domainId): void
    {
        if ($domainId === null) {
            Session::forget(self::ADMIN_SESSION_KEY);

            return;
        }

        Session::put(self::ADMIN_SESSION_KEY, $domainId);
    }

    public function adminContextDomain(): ?Domain
    {
        $id = $this->adminContextDomainId();

        if ($id === null) {
            return null;
        }

        return Domain::query()->find($id);
    }

    /** @return Collection<int, Domain> */
    public function activeDomains()
    {
        return Domain::query()->active()->orderBy('name')->get();
    }

    /** @return Collection<int, Domain> */
    public function allDomains()
    {
        return Domain::query()->orderByDesc('is_default')->orderBy('name')->get();
    }

    public function markDefault(Domain $domain): void
    {
        Domain::query()->where('is_default', true)->update(['is_default' => false]);
        $domain->update(['is_default' => true, 'status' => DomainStatus::Active]);
    }
}
