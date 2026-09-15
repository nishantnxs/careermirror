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

    public function test_home_page_uses_the_bundled_favicon(): void
    {
        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertFileExists(public_path('favicon.png'));
        $this->assertFileExists(public_path('favicon.svg'));
        $this->assertFileExists(public_path('apple-touch-icon.png'));

        $this->get('/')
            ->assertOk()
            ->assertSee(asset('favicon.ico'), false)
            ->assertSee(asset('favicon.png'), false)
            ->assertSee(asset('favicon.svg'), false)
            ->assertSee(asset('apple-touch-icon.png'), false);
    }

    public function test_home_page_lists_only_categories_with_available_jobs(): void
    {
        $domain = Domain::query()->where('is_default', true)->firstOrFail();

        $listed = Category::factory()->create(['name' => 'Customer Support']);
        $unused = Category::factory()->create(['name' => 'Design']);
        $draftOnly = Category::factory()->create(['name' => 'Product']);
        $expiredOnly = Category::factory()->create(['name' => 'Finance']);

        $live = JobPosting::factory()->create([
            'title' => 'Support Specialist',
            'status' => JobStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDays(20),
            'category_id' => $listed->id,
        ]);
        $live->domains()->sync([$domain->id]);

        $draft = JobPosting::factory()->create([
            'title' => 'Hidden Product Role',
            'status' => JobStatus::Draft,
            'category_id' => $draftOnly->id,
        ]);
        $draft->domains()->sync([$domain->id]);

        $expired = JobPosting::factory()->create([
            'title' => 'Expired Finance Role',
            'status' => JobStatus::Published,
            'published_at' => now()->subMonth(),
            'expires_at' => now()->subDay(),
            'category_id' => $expiredOnly->id,
        ]);
        $expired->domains()->sync([$domain->id]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Popular categories')
            ->assertSee('Customer Support')
            ->assertDontSee('category_id='.$unused->id, false)
            ->assertDontSee('category_id='.$draftOnly->id, false)
            ->assertDontSee('category_id='.$expiredOnly->id, false)
            ->assertDontSee('>Design</a>', false)
            ->assertDontSee('>Product</a>', false)
            ->assertDontSee('>Finance</a>', false);
    }

    public function test_home_page_hides_popular_categories_when_none_have_jobs(): void
    {
        Category::factory()->create(['name' => 'Design']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Popular categories')
            ->assertDontSee('>Design</a>', false);
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
