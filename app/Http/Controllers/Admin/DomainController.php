<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityAction;
use App\Enums\DomainStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DomainRequest;
use App\Http\Requests\Admin\WebsiteSettingRequest;
use App\Models\Domain;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Services\DomainService;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function __construct(
        public DomainService $domains,
        public SettingService $settings,
        public ActivityLogger $activity,
    ) {}

    public function index(Request $request): View
    {
        $domains = Domain::query()
            ->search($request->string('search')->trim()->value())
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.domains.index', [
            'domains' => $domains,
            'statuses' => DomainStatus::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.domains.create', [
            'domain' => new Domain([
                'status' => DomainStatus::Active,
                'is_default' => false,
                'url' => 'https://',
            ]),
            'statuses' => DomainStatus::options(),
        ]);
    }

    public function store(DomainRequest $request): RedirectResponse
    {
        $data = $request->domainData();

        if ($data['is_default']) {
            Domain::query()->where('is_default', true)->update(['is_default' => false]);
        } elseif (! Domain::query()->where('is_default', true)->exists()) {
            $data['is_default'] = true;
        }

        $domain = Domain::create($data);
        $this->storeUploads($request, $domain);
        $this->seedSettingsFromDefault($domain);

        if ($request->boolean('grant_all_employers') && $domain->isActive()) {
            $domain->grantToAllEmployers();
        }

        $this->activity->record(
            ActivityAction::DomainCreated,
            $domain,
            null,
            $domain->only(['name', 'host', 'url', 'status', 'is_default']),
            "Domain \"{$domain->host}\" created.",
        );

        return redirect()
            ->route('admin.domains.settings.edit', $domain)
            ->with('success', "Domain \"{$domain->host}\" created. Configure its website settings next.");
    }

    public function edit(Domain $domain): View
    {
        return view('admin.domains.edit', [
            'domain' => $domain,
            'statuses' => DomainStatus::options(),
        ]);
    }

    public function update(DomainRequest $request, Domain $domain): RedirectResponse
    {
        $before = $domain->only(['name', 'host', 'url', 'website_name', 'status', 'is_default']);
        $data = $request->domainData();

        if ($data['is_default']) {
            Domain::query()->where('is_default', true)->whereKeyNot($domain->id)->update(['is_default' => false]);
        } elseif ($domain->is_default && ! $data['is_default']) {
            return back()->with('error', 'Assign another default domain before unsetting this one.');
        }

        $domain->update($data);
        $this->storeUploads($request, $domain);
        $domain = $domain->fresh();

        if ($request->boolean('grant_all_employers') && $domain->isActive()) {
            $domain->grantToAllEmployers();
        }

        [$old, $new] = $this->activity->diff($before, $domain->only(['name', 'host', 'url', 'website_name', 'status', 'is_default']));

        if ($old !== [] || $new !== []) {
            $this->activity->record(
                ActivityAction::DomainUpdated,
                $domain,
                $old,
                $new,
                "Domain \"{$domain->host}\" updated.",
            );
        }

        return redirect()
            ->route('admin.domains.index')
            ->with('success', "Domain \"{$domain->host}\" updated successfully.");
    }

    public function toggleStatus(Domain $domain): RedirectResponse
    {
        if ($domain->is_default && $domain->isActive()) {
            return back()->with('error', 'The default domain cannot be deactivated.');
        }

        $previous = $domain->status;
        $domain->update([
            'status' => $domain->isActive() ? DomainStatus::Inactive : DomainStatus::Active,
        ]);

        $this->activity->record(
            ActivityAction::DomainStatusChanged,
            $domain,
            ['status' => $previous],
            ['status' => $domain->status],
            sprintf('Domain "%s" is now %s.', $domain->host, $domain->status->label()),
        );

        return back()->with('success', sprintf(
            'Domain "%s" is now %s.',
            $domain->host,
            $domain->status->label(),
        ));
    }

    public function destroy(Domain $domain): RedirectResponse
    {
        if ($domain->is_default) {
            return back()->with('error', 'The default domain cannot be deleted.');
        }

        $host = $domain->host;
        $snapshot = $domain->only(['name', 'host', 'url', 'status']);

        $this->activity->record(
            ActivityAction::DomainDeleted,
            $domain,
            $snapshot,
            null,
            "Domain \"{$host}\" deleted.",
        );

        $domain->delete();

        return redirect()
            ->route('admin.domains.index')
            ->with('success', "Domain \"{$host}\" deleted.");
    }

    public function editSettings(Domain $domain): View
    {
        $this->domains->setAdminContext($domain->id);

        return view('admin.domains.settings', [
            'domain' => $domain,
            'groups' => $this->settings->forDomain($domain)->groups(),
            'settings' => $this->settings->forDomain($domain),
        ]);
    }

    public function updateSettings(WebsiteSettingRequest $request, Domain $domain): RedirectResponse
    {
        $this->domains->setAdminContext($domain->id);
        $service = $this->settings->forDomain($domain);

        $files = [];
        $data = $request->validated();

        foreach ($service->fields() as $key => $definition) {
            if (($definition['type'] ?? null) === 'image') {
                $files[$key] = $request->file($key);
                $data['remove_'.$key] = $request->boolean('remove_'.$key);
            }
        }

        $changes = $service->save($data, $files);

        if ($changes['old'] !== [] || $changes['new'] !== []) {
            $this->activity->record(
                ActivityAction::SettingsUpdated,
                $domain,
                $changes['old'],
                $changes['new'],
                "Website settings updated for {$domain->host}.",
                ['tab' => $request->input('active_tab', 'general')],
            );
        }

        return redirect()
            ->route('admin.domains.settings.edit', [
                'domain' => $domain,
                'tab' => $request->input('active_tab', 'general'),
            ])
            ->with('success', "Settings for \"{$domain->host}\" updated successfully.");
    }

    public function switchContext(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain_id' => ['nullable', 'integer', 'exists:domains,id'],
        ]);

        $this->domains->setAdminContext(
            isset($validated['domain_id']) ? (int) $validated['domain_id'] : null
        );

        return back()->with('success', 'Admin domain context updated.');
    }

    protected function storeUploads(DomainRequest $request, Domain $domain): void
    {
        $updates = [];

        if ($request->boolean('remove_logo') && $domain->logo_path) {
            Storage::disk('public')->delete($domain->logo_path);
            $updates['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($domain->logo_path) {
                Storage::disk('public')->delete($domain->logo_path);
            }
            $updates['logo_path'] = $request->file('logo')->store('domains/'.$domain->id, 'public');
        }

        if ($request->boolean('remove_favicon') && $domain->favicon_path) {
            Storage::disk('public')->delete($domain->favicon_path);
            $updates['favicon_path'] = null;
        }

        if ($request->hasFile('favicon')) {
            if ($domain->favicon_path) {
                Storage::disk('public')->delete($domain->favicon_path);
            }
            $updates['favicon_path'] = $request->file('favicon')->store('domains/'.$domain->id, 'public');
        }

        if ($updates !== []) {
            $domain->update($updates);
        }
    }

    protected function seedSettingsFromDefault(Domain $domain): void
    {
        $sourceId = Domain::query()->where('is_default', true)->whereKeyNot($domain->id)->value('id')
            ?? Domain::query()->whereKeyNot($domain->id)->value('id');

        if ($sourceId === null) {
            return;
        }

        Setting::query()
            ->where('domain_id', $sourceId)
            ->get()
            ->each(function (Setting $setting) use ($domain): void {
                Setting::query()->updateOrCreate(
                    ['domain_id' => $domain->id, 'key' => $setting->key],
                    [
                        'value' => $setting->key === 'site_name'
                            ? ($domain->website_name ?: $setting->value)
                            : $setting->value,
                        'group' => $setting->group,
                        'type' => $setting->type,
                    ],
                );
            });

        Setting::flushCache($domain->id);
    }
}
