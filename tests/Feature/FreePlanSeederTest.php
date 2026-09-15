<?php

namespace Tests\Feature;

use App\Enums\PlanType;
use App\Models\Domain;
use App\Models\Plan;
use Database\Seeders\FreePlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreePlanSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_a_free_starter_plan_with_one_job_posting(): void
    {
        $this->seed(FreePlanSeeder::class);

        $plan = Plan::query()->where('slug', 'starter')->first();

        $this->assertNotNull($plan);
        $this->assertSame('Starter', $plan->title);
        $this->assertSame(PlanType::Free, $plan->plan_type);
        $this->assertTrue($plan->is_active);
        $this->assertSame(1, $plan->jobs_allowed);
        $this->assertTrue($plan->isFree());
        $this->assertContains('1 active job posting', $plan->features);

        $defaultDomain = Domain::query()->where('is_default', true)->first();

        if ($defaultDomain) {
            $this->assertTrue($plan->domains()->where('domains.id', $defaultDomain->id)->exists());
        }
    }

    public function test_it_is_idempotent(): void
    {
        $this->seed(FreePlanSeeder::class);
        $this->seed(FreePlanSeeder::class);

        $this->assertSame(1, Plan::query()->where('slug', 'starter')->count());
    }
}
