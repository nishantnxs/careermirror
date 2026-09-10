<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use App\Support\DateRange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AdminDashboardService $dashboard): View|RedirectResponse
    {
        try {
            $range = DateRange::fromRequest($request);
        } catch (InvalidArgumentException) {
            return redirect()->route('admin.dashboard', ['range' => 'this_month'])
                ->withErrors(['start_date' => 'Please provide a valid custom date range.']);
        }

        $overview = $dashboard->overview($range);

        return view('admin.dashboard', [
            ...$overview,
            'rangePresets' => DateRange::presetOptions(),
        ]);
    }
}
