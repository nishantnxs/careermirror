<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_plans' => Plan::count(),
            'active_plans' => Plan::active()->count(),
            'expiring_plans' => Plan::whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', now()->toDateString())
                ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
                ->count(),
            'expired_plans' => Plan::whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', now()->toDateString())
                ->count(),
        ];

        $recentPlans = Plan::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentPlans'));
    }
}
