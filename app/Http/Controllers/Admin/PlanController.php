<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityAction;
use App\Enums\DurationUnit;
use App\Enums\PlanType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlanRequest;
use App\Models\Domain;
use App\Models\Plan;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function __construct(public ActivityLogger $activity) {}

    public function index(Request $request): View
    {
        $plans = Plan::query()
            ->with('domains')
            ->search($request->string('search')->trim()->value())
            ->when($request->filled('plan_type'), fn ($query) => $query->where('plan_type', $request->input('plan_type')))
            ->when($request->input('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($request->filled('domain_id'), fn ($query) => $query->forDomain((int) $request->input('domain_id')))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.plans.index', [
            'plans' => $plans,
            'planTypes' => PlanType::options(),
            'domains' => Domain::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.plans.create', [
            'plan' => new Plan([
                'currency' => 'INR',
                'duration_unit' => DurationUnit::Month,
                'duration_value' => 1,
                'jobs_allowed' => 1,
                'job_duration_value' => 30,
                'job_duration_unit' => DurationUnit::Day,
                'plan_type' => PlanType::Basic,
                'is_active' => true,
            ]),
            'planTypes' => PlanType::options(),
            'durationUnits' => DurationUnit::options(),
            'domains' => Domain::query()->orderBy('name')->get(),
            'selectedDomainIds' => old('domain_ids', Domain::query()->where('is_default', true)->pluck('id')->all()),
        ]);
    }

    public function store(PlanRequest $request): RedirectResponse
    {
        $plan = Plan::create($request->planData());
        $plan->domains()->sync($request->domainIds());

        $this->activity->record(
            ActivityAction::PlanCreated,
            $plan,
            null,
            [
                ...$plan->only([
                    'title', 'slug', 'plan_type', 'amount', 'discount_amount', 'currency',
                    'jobs_allowed', 'is_active', 'is_featured',
                ]),
                'domain_ids' => $request->domainIds(),
            ],
            "Plan \"{$plan->title}\" created.",
        );

        if ($plan->discount_amount !== null && (float) $plan->discount_amount > 0) {
            $this->activity->record(
                ActivityAction::DiscountApplied,
                $plan,
                null,
                [
                    'amount' => $plan->amount,
                    'discount_amount' => $plan->discount_amount,
                    'payable_amount' => $plan->payable_amount,
                ],
                "Discount applied to plan \"{$plan->title}\".",
            );
        }

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$plan->title}\" created successfully.");
    }

    public function show(Plan $plan): View
    {
        $plan->load('domains');

        return view('admin.plans.show', compact('plan'));
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', [
            'plan' => $plan,
            'planTypes' => PlanType::options(),
            'durationUnits' => DurationUnit::options(),
            'domains' => Domain::query()->orderBy('name')->get(),
            'selectedDomainIds' => old('domain_ids', $plan->domains()->pluck('domains.id')->all()),
        ]);
    }

    public function update(PlanRequest $request, Plan $plan): RedirectResponse
    {
        $tracked = [
            'title', 'slug', 'plan_type', 'description', 'duration_unit', 'duration_value',
            'currency', 'amount', 'discount_amount', 'trial_days', 'jobs_allowed',
            'job_duration_unit', 'job_duration_value', 'features', 'expiry_date',
            'is_featured', 'is_active', 'sort_order',
        ];

        $before = $plan->only($tracked);
        $previousDiscount = $plan->discount_amount;

        $plan->update($request->planData());
        $plan->domains()->sync($request->domainIds());
        $plan = $plan->fresh();

        [$old, $new] = $this->activity->diff($before, $plan->only($tracked));

        if ($old !== [] || $new !== []) {
            $this->activity->record(
                ActivityAction::PlanUpdated,
                $plan,
                $old,
                $new,
                "Plan \"{$plan->title}\" updated.",
            );
        }

        if (
            $plan->discount_amount !== null
            && (float) $plan->discount_amount > 0
            && (string) $previousDiscount !== (string) $plan->discount_amount
        ) {
            $this->activity->record(
                ActivityAction::DiscountApplied,
                $plan,
                ['discount_amount' => $previousDiscount],
                [
                    'amount' => $plan->amount,
                    'discount_amount' => $plan->discount_amount,
                    'payable_amount' => $plan->payable_amount,
                ],
                "Discount applied to plan \"{$plan->title}\".",
            );
        }

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$plan->title}\" updated successfully.");
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $snapshot = $plan->only(['title', 'slug', 'plan_type', 'amount', 'discount_amount', 'is_active']);
        $title = $plan->title;

        $this->activity->record(
            ActivityAction::PlanDeleted,
            $plan,
            $snapshot,
            null,
            "Plan \"{$title}\" deleted.",
        );

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$title}\" deleted successfully.");
    }

    public function toggleStatus(Plan $plan): RedirectResponse
    {
        $previous = $plan->is_active;
        $plan->update(['is_active' => ! $plan->is_active]);

        $this->activity->record(
            ActivityAction::PlanStatusChanged,
            $plan,
            ['is_active' => $previous],
            ['is_active' => $plan->is_active],
            sprintf('Plan "%s" is now %s.', $plan->title, $plan->is_active ? 'active' : 'inactive'),
        );

        return back()->with('success', sprintf(
            'Plan "%s" is now %s.',
            $plan->title,
            $plan->is_active ? 'active' : 'inactive',
        ));
    }
}
