<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Enums\ActivityAction;
use App\Enums\PaymentMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignEmployerPlanRequest;
use App\Http\Requests\Admin\StoreEmployerRequest;
use App\Models\Domain;
use App\Models\Employer;
use App\Models\Plan;
use App\Services\ActivityLogger;
use App\Services\PlanPurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployerController extends Controller
{
    public function __construct(public ActivityLogger $activity) {}

    public function index(Request $request): View
    {
        $employers = Employer::query()
            ->search($request->string('search')->trim()->value())
            ->status($request->string('status')->toString() ?: null)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.employers.index', [
            'employers' => $employers,
            'statuses' => AccountStatus::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.employers.create', [
            'employer' => new Employer(['status' => AccountStatus::Active]),
            'statuses' => AccountStatus::options(),
            'domains' => Domain::query()->active()->orderBy('name')->get(),
            'selectedDomainIds' => old('domain_ids', Domain::query()->active()->pluck('id')->all()),
        ]);
    }

    public function store(StoreEmployerRequest $request): RedirectResponse
    {
        $employer = Employer::create($request->employerData());

        $domainIds = collect($request->input('domain_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($domainIds === []) {
            $defaultId = Domain::query()->where('is_default', true)->value('id')
                ?? Domain::query()->value('id');
            $domainIds = $defaultId ? [$defaultId] : [];
        }

        $employer->domains()->sync($domainIds);

        $this->activity->record(
            ActivityAction::EmployerCreated,
            $employer,
            null,
            [
                ...$employer->only(['company_name', 'name', 'email', 'phone', 'status']),
                'domain_ids' => $domainIds,
            ],
            "Employer \"{$employer->company_name}\" created.",
        );

        return redirect()->route('admin.employers.show', $employer)
            ->with('success', "Employer \"{$employer->company_name}\" created successfully.");
    }

    public function show(Employer $employer): View
    {
        $employer->load([
            'domains',
            'subscriptions' => fn ($query) => $query->with('plan')->latest('id'),
            'orders' => fn ($query) => $query->with('plan')->latest('id')->limit(10),
            'jobPostings' => fn ($query) => $query->latest('id')->limit(10),
        ]);

        $activeSubscription = $employer->subscriptions
            ->first(fn ($subscription) => $subscription->isCurrentlyActive());

        $plans = Plan::query()->active()->ordered()->get();

        return view('admin.employers.show', [
            'employer' => $employer,
            'statuses' => AccountStatus::options(),
            'plans' => $plans,
            'paymentModes' => PaymentMode::adminAssignmentOptions(),
            'activeSubscription' => $activeSubscription,
            'domains' => Domain::query()->active()->orderBy('name')->get(),
            'selectedDomainIds' => old('domain_ids', $employer->domains->pluck('id')->all()),
        ]);
    }

    public function assignPlan(
        AssignEmployerPlanRequest $request,
        Employer $employer,
        PlanPurchaseService $purchases,
    ): RedirectResponse {
        $plan = $request->plan();

        $order = $purchases->assignByAdmin(
            $request->user('admin'),
            $employer,
            $plan,
            $request->assignmentData(),
        );

        return redirect()
            ->route('admin.employers.show', $employer)
            ->with('success', sprintf(
                'Plan "%s" assigned to %s. Order %s recorded.',
                $plan->title,
                $employer->company_name,
                $order->order_number,
            ));
    }

    public function updateStatus(Request $request, Employer $employer): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(AccountStatus::class)],
        ]);

        $previous = $employer->status;
        $employer->update(['status' => $validated['status']]);
        $employer = $employer->fresh();

        $action = $employer->status === AccountStatus::Suspended
            ? ActivityAction::EmployerSuspended
            : ActivityAction::EmployerStatusChanged;

        $this->activity->record(
            $action,
            $employer,
            ['status' => $previous],
            ['status' => $employer->status],
            sprintf('Employer "%s" is now %s.', $employer->company_name, $employer->status->label()),
        );

        return back()->with('success', sprintf(
            'Employer "%s" is now %s.',
            $employer->company_name,
            $employer->status->label(),
        ));
    }

    public function updateDomains(Request $request, Employer $employer): RedirectResponse
    {
        $validated = $request->validate([
            'domain_ids' => ['required', 'array', 'min:1'],
            'domain_ids.*' => ['integer', 'exists:domains,id'],
        ], [
            'domain_ids.required' => 'Select at least one domain for this employer.',
            'domain_ids.min' => 'Select at least one domain for this employer.',
        ]);

        $before = $employer->domains()->pluck('domains.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $domainIds = collect($validated['domain_ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();

        $employer->domains()->sync($domainIds);

        $this->activity->record(
            ActivityAction::EmployerDomainsUpdated,
            $employer,
            ['domain_ids' => $before],
            ['domain_ids' => $domainIds],
            "Allowed domains updated for employer \"{$employer->company_name}\".",
        );

        return back()->with('success', 'Allowed publishing domains updated for this employer.');
    }
}
