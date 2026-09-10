<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityAction;
use App\Enums\CategoryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Services\ActivityLogger;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        public ActivityLogger $activity,
        public CategoryService $categories,
    ) {}

    public function index(Request $request): View
    {
        $managed = Category::query()
            ->where('status', CategoryStatus::Approved)
            ->search($request->string('search')->trim()->value())
            ->when($request->input('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->ordered()
            ->paginate(15, ['*'], 'managed_page')
            ->withQueryString();

        $suggestions = Category::query()
            ->with('suggestedBy')
            ->pending()
            ->latest('id')
            ->paginate(10, ['*'], 'suggestions_page')
            ->withQueryString();

        return view('admin.categories.index', compact('managed', 'suggestions'));
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->categoryData());

        $this->activity->record(
            ActivityAction::CategoryCreated,
            $category,
            null,
            $category->only(['name', 'slug', 'status', 'is_active', 'sort_order']),
            "Category \"{$category->name}\" created.",
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Category \"{$category->name}\" created successfully.");
    }

    public function edit(Category $category): View
    {
        abort_unless($category->status === CategoryStatus::Approved, 404);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        abort_unless($category->status === CategoryStatus::Approved, 404);

        $before = $category->only(['name', 'slug', 'is_active', 'sort_order']);
        $category->update($request->categoryData());
        $category = $category->fresh();

        [$old, $new] = $this->activity->diff($before, $category->only(['name', 'slug', 'is_active', 'sort_order']));

        if ($old !== [] || $new !== []) {
            $this->activity->record(
                ActivityAction::CategoryUpdated,
                $category,
                $old,
                $new,
                "Category \"{$category->name}\" updated.",
            );
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Category \"{$category->name}\" updated successfully.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_unless($category->status === CategoryStatus::Approved, 404);

        $name = $category->name;
        $snapshot = $category->only(['name', 'slug', 'status', 'is_active']);

        $this->activity->record(
            ActivityAction::CategoryDeleted,
            $category,
            $snapshot,
            null,
            "Category \"{$name}\" deleted.",
        );

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Category \"{$name}\" deleted.");
    }

    public function approve(Category $category): RedirectResponse
    {
        abort_unless($category->status === CategoryStatus::Pending, 404);

        $this->categories->approve($category, auth('admin')->user());

        return back()->with('success', "Category \"{$category->fresh()->name}\" approved and added to the listing.");
    }

    public function reject(Category $category): RedirectResponse
    {
        abort_unless($category->status === CategoryStatus::Pending, 404);

        $this->categories->reject($category, auth('admin')->user());

        return back()->with('success', "Category \"{$category->fresh()->name}\" rejected.");
    }
}
