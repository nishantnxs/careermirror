<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WebsiteSettingRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WebsiteSettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'groups' => $this->settings->groups(),
            'settings' => $this->settings,
        ]);
    }

    public function update(WebsiteSettingRequest $request): RedirectResponse
    {
        $files = [];
        $data = $request->validated();

        foreach ($this->settings->fields() as $key => $definition) {
            if (($definition['type'] ?? null) === 'image') {
                $files[$key] = $request->file($key);
                $data['remove_'.$key] = $request->boolean('remove_'.$key);
            }
        }

        $this->settings->save($data, $files);

        return redirect()
            ->route('admin.settings.edit', ['tab' => $request->input('active_tab', 'general')])
            ->with('success', 'Website settings updated successfully.');
    }
}
