<?php

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Models\Category;
use App\Models\Domain;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_the_marketing_design(): void
    {
        $domain = Domain::query()->where('is_default', true)->firstOrFail();
        $category = Category::factory()->create(['name' => 'Engineering']);

        $job = JobPosting::factory()->create([
            'title' => 'UI/UX Designer',
            'status' => JobStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDays(20),
            'location' => 'Remote',
            'category_id' => $category->id,
        ]);
        $job->domains()->sync([$domain->id]);

        $this->get('/')
            ->assertOk()
            ->assertSee('See the job market clearly.')
            ->assertSee('Latest openings')
            ->assertSee('UI/UX Designer')
            ->assertSee('FOR EMPLOYERS')
            ->assertSee('For candidates')
            ->assertSee('Popular categories')
            ->assertSee('Engineering');
    }

    public function test_public_job_board_lists_available_jobs(): void
    {
        $domain = Domain::query()->where('is_default', true)->firstOrFail();

        $job = JobPosting::factory()->create([
            'title' => 'Frontend Web Developer',
            'status' => JobStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDays(10),
        ]);
        $job->domains()->sync([$domain->id]);

        $this->get('/jobs?keyword=Frontend')
            ->assertOk()
            ->assertSee('Find your next role')
            ->assertSee('Frontend Web Developer')
            ->assertSee('About the role');

        $this->get('/jobs/'.$job->id)
            ->assertRedirect('/jobs?job='.$job->id);
    }
}
