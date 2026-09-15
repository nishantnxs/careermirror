<?php

namespace Tests\Feature\Candidate;

use App\Enums\JobStatus;
use App\Models\Candidate;
use App\Models\Category;
use App\Models\Domain;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobSearchTest extends TestCase
{
    use RefreshDatabase;

    private function publishJob(array $attributes = [], ?Domain $domain = null): JobPosting
    {
        $domain ??= Domain::query()->where('is_default', true)->firstOrFail();

        $job = JobPosting::factory()->create(array_merge([
            'status' => JobStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDays(20),
            'title' => 'Laravel Developer',
            'location' => 'Mumbai',
            'job_type' => 'full-time',
            'experience_level' => 'mid',
            'salary_min' => 40000,
            'salary_max' => 80000,
        ], $attributes));

        $job->domains()->sync([$domain->id]);

        return $job;
    }

    public function test_candidate_portal_lists_jobs_from_every_website(): void
    {
        $domain = Domain::query()->where('is_default', true)->firstOrFail();
        $otherDomain = Domain::factory()->create([
            'host' => 'other-board.test',
            'url' => 'https://other-board.test',
        ]);

        $category = Category::factory()->create(['name' => 'Engineering']);

        $visible = $this->publishJob([
            'title' => 'Visible Laravel Role',
            'category_id' => $category->id,
            'location' => 'Pune',
        ], $domain);

        $this->publishJob([
            'title' => 'Other Domain Job',
        ], $otherDomain);

        $this->publishJob([
            'title' => 'Draft Should Hide',
            'status' => JobStatus::Draft,
        ], $domain);

        $candidate = Candidate::factory()->create();

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/jobs?keyword=Laravel&location=Pune&category_id='.$category->id)
            ->assertOk()
            ->assertSee('Visible Laravel Role')
            ->assertDontSee('Other Domain Job')
            ->assertDontSee('Draft Should Hide');

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/jobs')
            ->assertOk()
            ->assertSee('Visible Laravel Role')
            ->assertSee('Other Domain Job');

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/jobs/'.$visible->id)
            ->assertOk()
            ->assertSee('Visible Laravel Role')
            ->assertSee('Apply');
    }

    public function test_public_job_board_only_lists_jobs_for_the_current_website(): void
    {
        $domain = Domain::query()->where('is_default', true)->firstOrFail();
        $otherDomain = Domain::factory()->create([
            'host' => 'other-board.test',
            'url' => 'https://other-board.test',
        ]);

        $this->publishJob(['title' => 'Localhost Role'], $domain);
        $this->publishJob(['title' => 'Other Board Role'], $otherDomain);

        $this->get('/jobs')
            ->assertOk()
            ->assertSee('Localhost Role')
            ->assertDontSee('Other Board Role');
    }

    public function test_candidate_can_save_and_unsave_a_job(): void
    {
        $job = $this->publishJob(['title' => 'Saved Role']);
        $candidate = Candidate::factory()->create();

        $this->actingAs($candidate, 'candidate')
            ->post('/candidate/jobs/'.$job->id.'/save')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('saved_jobs', [
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
        ]);

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/saved-jobs')
            ->assertOk()
            ->assertSee('Saved Role');

        $this->actingAs($candidate, 'candidate')
            ->delete('/candidate/jobs/'.$job->id.'/save')
            ->assertRedirect();

        $this->assertDatabaseMissing('saved_jobs', [
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
        ]);
    }

    public function test_browse_jobs_shows_save_controls_for_candidates(): void
    {
        $job = $this->publishJob(['title' => 'Bookmark Me Role']);
        $candidate = Candidate::factory()->create();

        $this->actingAs($candidate, 'candidate')
            ->get('/jobs')
            ->assertOk()
            ->assertSee('Bookmark Me Role')
            ->assertSee('Saved jobs')
            ->assertSee('Save job')
            ->assertSee('fa-regular fa-bookmark', false)
            ->assertDontSee('Unsave');

        $this->actingAs($candidate, 'candidate')
            ->from('/jobs?job='.$job->id)
            ->post('/candidate/jobs/'.$job->id.'/save')
            ->assertRedirect('/jobs?job='.$job->id);

        $this->actingAs($candidate, 'candidate')
            ->get('/jobs?job='.$job->id)
            ->assertOk()
            ->assertSee('Remove from saved jobs')
            ->assertSee('fa-solid fa-bookmark', false);

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/saved-jobs')
            ->assertOk()
            ->assertSee('Bookmark Me Role')
            ->assertSee('/jobs?job='.$job->id);
    }

    public function test_expired_jobs_are_hidden_from_search(): void
    {
        $this->publishJob([
            'title' => 'Expired Role',
            'expires_at' => now()->subDay(),
        ]);

        $candidate = Candidate::factory()->create();

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/jobs')
            ->assertOk()
            ->assertDontSee('Expired Role');
    }
}
