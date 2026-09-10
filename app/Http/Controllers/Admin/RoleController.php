<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityAction;
use App\Enums\AdminPermissionModule;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(public ActivityLogger $activity) {}

    public function index(): View
    {
        $roles = AdminRole::query()
            ->withCount(['permissions', 'admins'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'role' => new AdminRole(['is_active' => true]),
            'permissionsByModule' => $this->permissionsByModule(),
            'selectedPermissionIds' => [],
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = AdminRole::create($request->roleData());
        $permissionIds = $request->permissionIds();
        $role->permissions()->sync($permissionIds);

        $this->activity->record(
            ActivityAction::RoleCreated,
            $role,
            null,
            [
                ...$role->only(['name', 'slug', 'description', 'is_active']),
                'permission_ids' => $permissionIds,
            ],
            "Role \"{$role->name}\" created.",
        );

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" created successfully.");
    }

    public function edit(AdminRole $role): View
    {
        $role->load('permissions');

        return view('admin.roles.edit', [
            'role' => $role,
            'permissionsByModule' => $this->permissionsByModule(),
            'selectedPermissionIds' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(RoleRequest $request, AdminRole $role): RedirectResponse
    {
        $before = [
            ...$role->only(['name', 'slug', 'description', 'is_active']),
            'permission_ids' => $role->permissions()->pluck('admin_permissions.id')->sort()->values()->all(),
        ];

        $role->update($request->roleData());
        $permissionIds = $request->permissionIds();
        $role->permissions()->sync($permissionIds);
        $role = $role->fresh();

        $after = [
            ...$role->only(['name', 'slug', 'description', 'is_active']),
            'permission_ids' => collect($permissionIds)->sort()->values()->all(),
        ];

        [$old, $new] = $this->activity->diff($before, $after);

        if ($old !== [] || $new !== []) {
            $this->activity->record(
                ActivityAction::RoleUpdated,
                $role,
                $old,
                $new,
                "Role \"{$role->name}\" updated.",
            );
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" updated successfully.");
    }

    public function destroy(AdminRole $role): RedirectResponse
    {
        if ($role->admins()->exists()) {
            return back()->with('error', 'Cannot delete a role that is assigned to moderators.');
        }

        $name = $role->name;
        $snapshot = $role->only(['name', 'slug', 'description', 'is_active']);

        $this->activity->record(
            ActivityAction::RoleDeleted,
            $role,
            $snapshot,
            null,
            "Role \"{$name}\" deleted.",
        );

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role \"{$name}\" deleted.");
    }

    /**
     * @return array<string, Collection<int, AdminPermission>>
     */
    protected function permissionsByModule(): array
    {
        $permissions = AdminPermission::query()
            ->orderBy('module')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $ordered = [];

        foreach (AdminPermissionModule::cases() as $module) {
            if ($permissions->has($module->value)) {
                $ordered[$module->value] = $permissions->get($module->value);
            }
        }

        foreach ($permissions as $module => $items) {
            if (! array_key_exists($module, $ordered)) {
                $ordered[$module] = $items;
            }
        }

        return $ordered;
    }
}
