<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreModeratorRequest;
use App\Http\Requests\Admin\UpdateModeratorRequest;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModeratorController extends Controller
{
    public function __construct(public ActivityLogger $activity) {}

    public function index(Request $request): View
    {
        $moderators = Admin::query()
            ->with('role')
            ->where('is_super_admin', false)
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search')->trim()->value();
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->when($request->input('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.moderators.index', compact('moderators'));
    }

    public function create(): View
    {
        return view('admin.moderators.create', [
            'moderator' => new Admin(['is_active' => true]),
            'roles' => AdminRole::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreModeratorRequest $request): RedirectResponse
    {
        $moderator = Admin::create($request->moderatorData());

        $this->activity->record(
            ActivityAction::ModeratorCreated,
            $moderator,
            null,
            $moderator->only(['name', 'email', 'phone', 'is_active', 'admin_role_id']),
            "Moderator \"{$moderator->name}\" created.",
        );

        return redirect()
            ->route('admin.moderators.index')
            ->with('success', "Moderator \"{$moderator->name}\" created successfully.");
    }

    public function edit(Admin $moderator): View
    {
        abort_if($moderator->is_super_admin, 404);

        return view('admin.moderators.edit', [
            'moderator' => $moderator,
            'roles' => AdminRole::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateModeratorRequest $request, Admin $moderator): RedirectResponse
    {
        abort_if($moderator->is_super_admin, 404);

        $before = $moderator->only(['name', 'email', 'phone', 'is_active', 'admin_role_id']);
        $moderator->update($request->moderatorData());
        $moderator = $moderator->fresh();

        [$old, $new] = $this->activity->diff($before, $moderator->only(['name', 'email', 'phone', 'is_active', 'admin_role_id']));

        if ($old !== [] || $new !== []) {
            $this->activity->record(
                ActivityAction::ModeratorUpdated,
                $moderator,
                $old,
                $new,
                "Moderator \"{$moderator->name}\" updated.",
            );
        }

        return redirect()
            ->route('admin.moderators.index')
            ->with('success', "Moderator \"{$moderator->name}\" updated successfully.");
    }

    public function toggleStatus(Admin $moderator): RedirectResponse
    {
        abort_if($moderator->is_super_admin, 404);

        if ($moderator->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $previous = $moderator->is_active;
        $moderator->update(['is_active' => ! $moderator->is_active]);

        $this->activity->record(
            ActivityAction::ModeratorStatusChanged,
            $moderator,
            ['is_active' => $previous],
            ['is_active' => $moderator->is_active],
            sprintf('Moderator "%s" is now %s.', $moderator->name, $moderator->is_active ? 'active' : 'inactive'),
        );

        return back()->with('success', sprintf(
            'Moderator "%s" is now %s.',
            $moderator->name,
            $moderator->is_active ? 'active' : 'inactive',
        ));
    }

    public function destroy(Admin $moderator): RedirectResponse
    {
        abort_if($moderator->is_super_admin, 404);

        if ($moderator->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $moderator->name;
        $snapshot = $moderator->only(['name', 'email', 'phone', 'is_active', 'admin_role_id']);

        $this->activity->record(
            ActivityAction::ModeratorDeleted,
            $moderator,
            $snapshot,
            null,
            "Moderator \"{$name}\" deleted.",
        );

        $moderator->delete();

        return redirect()
            ->route('admin.moderators.index')
            ->with('success', "Moderator \"{$name}\" deleted.");
    }
}
