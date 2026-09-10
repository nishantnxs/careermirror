<?php

namespace App\Support;

use App\Enums\AdminPermissionModule;

class AdminPermissions
{
    /**
     * Canonical permission catalogue grouped by module.
     *
     * @return array<string, list<array{slug: string, name: string, description: string}>>
     */
    public static function catalogue(): array
    {
        return [
            AdminPermissionModule::Dashboard->value => [
                ['slug' => 'dashboard.view', 'name' => 'View dashboard', 'description' => 'Access the admin dashboard overview.'],
            ],
            AdminPermissionModule::Employers->value => [
                ['slug' => 'employers.view', 'name' => 'View employers', 'description' => 'Browse and search employer accounts.'],
                ['slug' => 'employers.create', 'name' => 'Create employers', 'description' => 'Add new employer accounts.'],
                ['slug' => 'employers.manage', 'name' => 'Manage employers', 'description' => 'Update status and assign plans.'],
            ],
            AdminPermissionModule::Candidates->value => [
                ['slug' => 'candidates.view', 'name' => 'View candidates', 'description' => 'Browse and search candidate accounts.'],
                ['slug' => 'candidates.create', 'name' => 'Create candidates', 'description' => 'Add new candidate accounts.'],
                ['slug' => 'candidates.manage', 'name' => 'Manage candidates', 'description' => 'Review profiles and update status.'],
            ],
            AdminPermissionModule::Jobs->value => [
                ['slug' => 'jobs.view', 'name' => 'View jobs', 'description' => 'Browse job postings.'],
                ['slug' => 'jobs.manage', 'name' => 'Manage jobs', 'description' => 'Create, edit and remove job postings.'],
                ['slug' => 'jobs.review', 'name' => 'Review job postings', 'description' => 'Approve or reject submitted jobs.'],
                ['slug' => 'jobs.featured', 'name' => 'Manage featured jobs', 'description' => 'Feature or unfeature job listings.'],
            ],
            AdminPermissionModule::Plans->value => [
                ['slug' => 'plans.view', 'name' => 'View plans', 'description' => 'Browse subscription plans.'],
                ['slug' => 'plans.manage', 'name' => 'Manage plans', 'description' => 'Create and update subscription plans.'],
            ],
            AdminPermissionModule::Orders->value => [
                ['slug' => 'orders.view', 'name' => 'View orders', 'description' => 'Browse employer orders.'],
                ['slug' => 'orders.manage', 'name' => 'Manage orders', 'description' => 'Update order and subscription records.'],
                ['slug' => 'payments.view', 'name' => 'View payments', 'description' => 'View payment audit details.'],
            ],
            AdminPermissionModule::Settings->value => [
                ['slug' => 'settings.manage', 'name' => 'Manage settings', 'description' => 'Update website settings.'],
            ],
            AdminPermissionModule::Access->value => [
                ['slug' => 'roles.manage', 'name' => 'Manage roles', 'description' => 'Create roles and assign permissions.'],
                ['slug' => 'moderators.manage', 'name' => 'Manage moderators', 'description' => 'Create and activate/deactivate moderators.'],
            ],
            AdminPermissionModule::Activity->value => [
                ['slug' => 'activity.view', 'name' => 'View activity log', 'description' => 'Browse the system audit trail.'],
            ],
            AdminPermissionModule::Categories->value => [
                ['slug' => 'categories.view', 'name' => 'View categories', 'description' => 'Browse job categories.'],
                ['slug' => 'categories.manage', 'name' => 'Manage categories', 'description' => 'Create and update job categories.'],
                ['slug' => 'categories.review', 'name' => 'Review category suggestions', 'description' => 'Approve or reject employer-added categories.'],
            ],
            AdminPermissionModule::Domains->value => [
                ['slug' => 'domains.view', 'name' => 'View domains', 'description' => 'Browse managed websites/domains.'],
                ['slug' => 'domains.manage', 'name' => 'Manage domains', 'description' => 'Create domains and update domain website settings.'],
            ],
        ];
    }

    /** @return list<string> */
    public static function allSlugs(): array
    {
        return collect(self::catalogue())
            ->flatten(1)
            ->pluck('slug')
            ->values()
            ->all();
    }
}
