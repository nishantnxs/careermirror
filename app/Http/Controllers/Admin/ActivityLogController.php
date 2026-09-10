<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityAction;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->search($request->string('search')->trim()->value())
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->input('action')))
            ->when($request->filled('actor_guard'), fn ($query) => $query->where('actor_guard', $request->input('actor_guard')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to')))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity.index', [
            'logs' => $logs,
            'actions' => ActivityAction::options(),
            'guards' => [
                'admin' => 'Admin / moderator',
                'employer' => 'Employer',
                'candidate' => 'Candidate',
                'system' => 'System',
            ],
        ]);
    }

    public function show(ActivityLog $activity): View
    {
        return view('admin.activity.show', [
            'log' => $activity,
        ]);
    }
}
