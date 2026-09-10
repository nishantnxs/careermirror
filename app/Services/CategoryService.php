<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Enums\CategoryStatus;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Employer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(public ActivityLogger $activity) {}

    /**
     * Live / pre-submit check for a custom "Other" category name.
     *
     * @return array{status: string, message: string, category_id: int|null}
     */
    public function checkSuggestedName(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            return [
                'status' => 'empty',
                'message' => 'Enter a category name.',
                'category_id' => null,
            ];
        }

        $existing = Category::query()->matchingName($name)->first();

        if ($existing === null) {
            return [
                'status' => 'available',
                'message' => 'This category can be suggested for admin approval.',
                'category_id' => null,
            ];
        }

        if ($existing->status === CategoryStatus::Approved) {
            return [
                'status' => 'exists_approved',
                'message' => 'This category already exists. Please select it from the list.',
                'category_id' => $existing->id,
            ];
        }

        if ($existing->status === CategoryStatus::Pending) {
            return [
                'status' => 'exists_pending',
                'message' => 'This category is already awaiting admin approval. Your job can still use it.',
                'category_id' => $existing->id,
            ];
        }

        return [
            'status' => 'exists_rejected',
            'message' => 'This name was rejected before. Suggesting it again will send it back for review.',
            'category_id' => $existing->id,
        ];
    }

    /**
     * Resolve the category for a job posting (approved pick or "Other" suggestion).
     *
     * @throws ValidationException
     */
    public function resolveForJob(Employer $employer, ?int $categoryId, ?string $customName): Category
    {
        if ($categoryId !== null) {
            $category = Category::query()->approved()->whereKey($categoryId)->first();

            if ($category === null) {
                throw ValidationException::withMessages([
                    'category_id' => 'Please select a valid category.',
                ]);
            }

            return $category;
        }

        $customName = trim((string) $customName);

        if ($customName === '') {
            throw ValidationException::withMessages([
                'custom_category_name' => 'Please enter a category name, or choose one from the list.',
            ]);
        }

        return $this->suggestOrReuse($employer, $customName);
    }

    /**
     * Create or reuse a pending employer-suggested category.
     *
     * Approved names are blocked. Pending names increment suggestion_count.
     * Rejected names are reopened as pending.
     *
     * @throws ValidationException
     */
    public function suggestOrReuse(Employer $employer, string $name): Category
    {
        $name = trim($name);

        return DB::transaction(function () use ($employer, $name) {
            /** @var Category|null $existing */
            $existing = Category::query()
                ->matchingName($name)
                ->lockForUpdate()
                ->first();

            if ($existing !== null && $existing->status === CategoryStatus::Approved) {
                throw ValidationException::withMessages([
                    'custom_category_name' => 'This category already exists. Please select it from the list.',
                ]);
            }

            if ($existing !== null && $existing->status === CategoryStatus::Pending) {
                $existing->increment('suggestion_count');

                $this->activity->record(
                    ActivityAction::CategorySuggested,
                    $existing->fresh(),
                    ['suggestion_count' => $existing->suggestion_count - 1],
                    ['suggestion_count' => $existing->fresh()->suggestion_count],
                    "Category \"{$existing->name}\" suggested again.",
                    ['employer_id' => $employer->id],
                );

                return $existing->fresh();
            }

            if ($existing !== null && $existing->status === CategoryStatus::Rejected) {
                $before = $existing->only(['status', 'suggestion_count', 'is_active']);

                $existing->update([
                    'status' => CategoryStatus::Pending,
                    'suggestion_count' => $existing->suggestion_count + 1,
                    'is_active' => false,
                    'reviewed_by_admin_id' => null,
                    'reviewed_at' => null,
                    'suggested_by_employer_id' => $existing->suggested_by_employer_id ?: $employer->id,
                ]);

                $existing = $existing->fresh();

                $this->activity->record(
                    ActivityAction::CategorySuggested,
                    $existing,
                    $before,
                    $existing->only(['status', 'suggestion_count', 'is_active']),
                    "Rejected category \"{$existing->name}\" re-suggested.",
                    ['employer_id' => $employer->id],
                );

                return $existing;
            }

            $category = Category::create([
                'name' => $name,
                'slug' => Category::uniqueSlug($name),
                'status' => CategoryStatus::Pending,
                'suggestion_count' => 1,
                'suggested_by_employer_id' => $employer->id,
                'sort_order' => 999,
                'is_active' => false,
            ]);

            $this->activity->record(
                ActivityAction::CategorySuggested,
                $category,
                null,
                $category->only(['name', 'slug', 'status', 'suggestion_count']),
                "Category \"{$category->name}\" suggested by employer.",
                ['employer_id' => $employer->id],
            );

            return $category;
        });
    }

    public function approve(Category $category, Admin $admin): Category
    {
        $before = $category->only(['status', 'is_active']);

        $category->update([
            'status' => CategoryStatus::Approved,
            'is_active' => true,
            'reviewed_by_admin_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $category = $category->fresh();

        $this->activity->record(
            ActivityAction::CategoryApproved,
            $category,
            $before,
            $category->only(['status', 'is_active']),
            "Category \"{$category->name}\" approved.",
        );

        return $category;
    }

    public function reject(Category $category, Admin $admin): Category
    {
        $before = $category->only(['status', 'is_active']);

        $category->update([
            'status' => CategoryStatus::Rejected,
            'is_active' => false,
            'reviewed_by_admin_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $category = $category->fresh();

        $this->activity->record(
            ActivityAction::CategoryRejected,
            $category,
            $before,
            $category->only(['status', 'is_active']),
            "Category \"{$category->name}\" rejected.",
        );

        return $category;
    }
}
