@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Platform overview for '.$range->label().'.')

@section('content')
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 align-items-end" id="dashboardRangeForm">
                <div class="col-md-3">
                    <label for="range" class="form-label">Date range</label>
                    <select name="range" id="range" class="form-select">
                        @foreach ($rangePresets as $value => $label)
                            <option value="{{ $value }}" @selected($range->preset === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3" data-custom-date @class(['d-none' => $range->preset !== 'custom'])>
                    <label for="start_date" class="form-label">Start date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                           value="{{ request('start_date', $range->preset === 'custom' ? $range->start->toDateString() : '') }}">
                </div>
                <div class="col-md-3" data-custom-date @class(['d-none' => $range->preset !== 'custom'])>
                    <label for="end_date" class="form-label">End date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                           value="{{ request('end_date', $range->preset === 'custom' ? $range->end->toDateString() : '') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-funnel me-1"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    @php
        $primaryCards = [
            ['label' => 'Candidates (period)', 'value' => $stats['total_candidates'], 'icon' => 'bi-people', 'tone' => 'primary'],
            ['label' => 'Employers (period)', 'value' => $stats['total_employers'], 'icon' => 'bi-building', 'tone' => 'info'],
            ['label' => 'Active candidates', 'value' => $stats['active_candidates'], 'icon' => 'bi-person-check', 'tone' => 'success'],
            ['label' => 'Active employers', 'value' => $stats['active_employers'], 'icon' => 'bi-building-check', 'tone' => 'success'],
            ['label' => 'Jobs posted', 'value' => $stats['jobs_posted'], 'icon' => 'bi-briefcase', 'tone' => 'primary'],
            ['label' => 'Active jobs', 'value' => $stats['active_jobs'], 'icon' => 'bi-check2-circle', 'tone' => 'success'],
            ['label' => 'Expired jobs', 'value' => $stats['expired_jobs'], 'icon' => 'bi-hourglass-bottom', 'tone' => 'warning'],
            ['label' => 'Featured jobs', 'value' => $stats['featured_jobs'], 'icon' => 'bi-star', 'tone' => 'warning'],
            ['label' => 'Plans available', 'value' => $stats['plans_available'], 'icon' => 'bi-card-checklist', 'tone' => 'secondary'],
            ['label' => 'Plans purchased', 'value' => $stats['plans_purchased'], 'icon' => 'bi-bag-check', 'tone' => 'info'],
            ['label' => 'Applications', 'value' => $stats['applications'], 'icon' => 'bi-file-earmark-text', 'tone' => 'primary'],
            ['label' => 'Revenue', 'value' => '₹'.number_format((float) $stats['revenue'], 2), 'icon' => 'bi-currency-rupee', 'tone' => 'success', 'raw' => true],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach ($primaryCards as $card)
            <div class="col-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-{{ $card['tone'] }} bg-opacity-10 text-{{ $card['tone'] }}">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="h4 mb-0 fw-bold">
                                @if (! empty($card['raw']))
                                    {{ $card['value'] }}
                                @else
                                    {{ number_format($card['value']) }}
                                @endif
                            </div>
                            <div class="text-secondary small">{{ $card['label'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent candidates</span>
                    <a href="{{ route('admin.candidates.index') }}" class="btn btn-sm btn-outline-secondary">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recent_candidates as $candidate)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.candidates.show', $candidate) }}" class="fw-semibold text-dark">
                                            {{ $candidate->name }}
                                        </a>
                                    </td>
                                    <td class="text-secondary">{{ $candidate->email }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $candidate->status->badgeClass() }}">{{ $candidate->status->label() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">No candidates yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent employers</span>
                    <a href="{{ route('admin.employers.index') }}" class="btn btn-sm btn-outline-secondary">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Contact</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recent_employers as $employer)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.employers.show', $employer) }}" class="fw-semibold text-dark">
                                            {{ $employer->company_name }}
                                        </a>
                                    </td>
                                    <td class="text-secondary">{{ $employer->name }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $employer->status->badgeClass() }}">{{ $employer->status->label() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">No employers yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const rangeSelect = document.getElementById('range');
    const customFields = document.querySelectorAll('[data-custom-date]');

    rangeSelect?.addEventListener('change', function () {
        const isCustom = this.value === 'custom';
        customFields.forEach((field) => field.classList.toggle('d-none', !isCustom));
        if (!isCustom) {
            document.getElementById('dashboardRangeForm')?.requestSubmit();
        }
    });
</script>
@endpush
