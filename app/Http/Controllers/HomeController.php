<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\JobPosting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $jobs = JobPosting::query()
            ->available()
            ->forDomain(current_domain())
            ->with(['employer', 'category'])
            ->latest('published_at')
            ->latest('id')
            ->take(6)
            ->get();

        $categories = Category::query()
            ->approved()
            ->ordered()
            ->limit(12)
            ->get();

        return view('welcome', [
            'jobs' => $jobs,
            'categories' => $categories,
            'siteName' => setting('site_name', config('app.name')),
        ]);
    }
}
