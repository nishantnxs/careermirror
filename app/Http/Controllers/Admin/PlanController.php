<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DurationUnit;
use App\Enums\PlanType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        $plans = Plan::query()
            ->search($request->string('search')->trim()->value())
            ->when($request->filled('plan_type'), fn ($query) => $query->where('plan_type', $request->input('plan_type')))
            ->when($request->input('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.plans.index', [
            'plans' => $plans,
            'planTypes' => PlanType::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.plans.create', [
            'plan' => new Plan([
                'currency' => 'INR',
                'duration_unit' => DurationUnit::Month,
                'duration_value' => 1,
                'plan_type' => PlanType::Basic,
                'is_active' => true,
            ]),
            'planTypes' => PlanType::options(),
            'durationUnits' => DurationUnit::options(),
        ]);
    }

    public function store(PlanRequest $request): RedirectResponse
    {
        $plan = Plan::create($request->planData());

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$plan->title}\" created successfully.");
    }

    public function show(Plan $plan): View
    {
        return view('admin.plans.show', compact('plan'));
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', [
            'plan' => $plan,
            'planTypes' => PlanType::options(),
            'durationUnits' => DurationUnit::options(),
        ]);
    }

    public function update(PlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->planData());

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$plan->title}\" updated successfully.");
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$plan->title}\" deleted successfully.");
    }

    public function toggleStatus(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return back()->with('success', sprintf(
            'Plan "%s" is now %s.',
            $plan->title,
            $plan->is_active ? 'active' : 'inactive',
        ));
    }
}
