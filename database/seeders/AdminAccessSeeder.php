<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Support\AdminPermissions;
use Illuminate\Database\Seeder;

class AdminAccessSeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;
        $permissionIdsBySlug = [];

        foreach (AdminPermissions::catalogue() as $module => $permissions) {
            foreach ($permissions as $permission) {
                $model = AdminPermission::updateOrCreate(
                    ['slug' => $permission['slug']],
                    [
                        'name' => $permission['name'],
                        'module' => $module,
                        'description' => $permission['description'],
                        'sort_order' => ++$sort,
                    ],
                );

                $permissionIdsBySlug[$model->slug] = $model->id;
            }
        }

        $roles = [
            'job-moderator' => [
                'name' => 'Job Moderator',
                'description' => 'Manage and review job postings, including featured jobs.',
                'permissions' => [
                    'dashboard.view',
                    'jobs.view',
                    'jobs.manage',
                    'jobs.review',
                    'jobs.featured',
                    'employers.view',
                    'categories.view',
                    'categories.manage',
                    'categories.review',
                ],
            ],
            'candidate-moderator' => [
                'name' => 'Candidate Moderator',
                'description' => 'Manage candidates and review candidate profiles.',
                'permissions' => [
                    'dashboard.view',
                    'candidates.view',
                    'candidates.create',
                    'candidates.manage',
                ],
            ],
            'finance-admin' => [
                'name' => 'Finance Admin',
                'description' => 'Manage plans, orders and view payments.',
                'permissions' => [
                    'dashboard.view',
                    'plans.view',
                    'plans.manage',
                    'orders.view',
                    'orders.manage',
                    'payments.view',
                    'employers.view',
                    'employers.manage',
                    'activity.view',
                ],
            ],
        ];

        foreach ($roles as $slug => $roleData) {
            $role = AdminRole::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'is_active' => true,
                ],
            );

            $ids = collect($roleData['permissions'])
                ->map(fn (string $permission) => $permissionIdsBySlug[$permission] ?? null)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($ids);
        }

        Admin::query()
            ->where(function ($query): void {
                $query->where('email', env('ADMIN_EMAIL', 'admin@careermirror.com'))
                    ->orWhereNull('admin_role_id');
            })
            ->update(['is_super_admin' => true, 'admin_role_id' => null]);
    }
}
