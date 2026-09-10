<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Services\DomainService;
use Illuminate\Http\RedirectResponse;

class WebsiteSettingController extends Controller
{
    public function __construct(public DomainService $domains) {}

    public function edit(): RedirectResponse
    {
        $domain = $this->domains->adminContextDomain()
            ?? Domain::query()->where('is_default', true)->first()
            ?? Domain::query()->orderBy('id')->first();

        if ($domain === null) {
            return redirect()
                ->route('admin.domains.create')
                ->with('error', 'Create a domain before managing website settings.');
        }

        return redirect()->route('admin.domains.settings.edit', $domain);
    }

    public function update(): RedirectResponse
    {
        return $this->edit();
    }
}
