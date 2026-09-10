<?php

namespace Database\Seeders;

use App\Enums\CategoryStatus;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Engineering',
            'Design',
            'Product',
            'Marketing',
            'Sales',
            'Customer Support',
            'Finance',
            'Healthcare',
            'Skilled Trades',
            'Education',
            'Operations',
        ];

        foreach ($categories as $index => $name) {
            Category::query()->updateOrCreate(
                ['slug' => slug_from($name, 'category')],
                [
                    'name' => $name,
                    'status' => CategoryStatus::Approved,
                    'suggestion_count' => 1,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
