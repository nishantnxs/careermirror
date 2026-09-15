<?php

namespace Tests\Feature\Candidate;

use App\Enums\ResumeSource;
use App\Models\Candidate;
use App\Models\CandidateResume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_build_preview_and_set_default_resume(): void
    {
        $candidate = Candidate::factory()->create([
            'name' => 'Jane Candidate',
            'email' => 'jane@example.com',
        ]);

        $this->actingAs($candidate, 'candidate')
            ->post('/candidate/resumes', [
                'title' => 'Primary Resume',
                'is_default' => '1',
                'name' => 'Jane Candidate',
                'email' => 'jane@example.com',
                'phone' => '5551234567',
                'headline' => 'Frontend Developer',
                'location' => 'Toronto, ON',
                'content' => [
                    'summary' => 'Experienced developer',
                    'skills' => 'PHP, Laravel',
                    'experience' => [[
                        'title' => 'Developer',
                        'company' => 'Acme',
                        'period' => '2021 – Present',
                        'description' => 'Built apps',
                    ]],
                    'education' => [[
                        'institution' => 'George Brown College',
                        'degree' => 'Diploma in Computer Programming',
                        'period' => '2020 – 2022',
                    ]],
                    'certifications' => [[
                        'name' => 'AWS Cloud Practitioner',
                        'issuer' => 'Amazon',
                        'year' => '2024',
                    ]],
                    'languages' => [[
                        'name' => 'English',
                        'proficiency' => 'Fluent',
                    ]],
                    'projects' => [[
                        'name' => 'CareerMirror',
                        'url' => 'https://example.com',
                        'description' => 'Job marketplace',
                    ]],
                    'achievements' => "Shipped feature X\nMentored juniors",
                    'other' => 'Open to relocate',
                ],
            ])
            ->assertRedirect();

        $resume = CandidateResume::query()->first();

        $this->assertNotNull($resume);
        $this->assertSame(ResumeSource::Builder, $resume->source);
        $this->assertTrue($resume->is_default);
        $this->assertSame(['PHP', 'Laravel'], $resume->content['skills']);
        $this->assertCount(1, $resume->content['experience']);
        $this->assertSame('2021 – Present', $resume->content['experience'][0]['period']);
        $this->assertSame('Frontend Developer', $candidate->fresh()->headline);
        $this->assertSame('5551234567', $candidate->fresh()->phone);

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/resumes/'.$resume->id.'/preview')
            ->assertOk()
            ->assertSee('Experienced developer')
            ->assertSee('Acme')
            ->assertSee('George Brown College')
            ->assertSee('AWS Cloud Practitioner')
            ->assertSee('English')
            ->assertSee('CareerMirror')
            ->assertSee('Open to relocate');

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/resumes/'.$resume->id.'/edit')
            ->assertOk()
            ->assertSee('My resume')
            ->assertSee('Basics')
            ->assertSee('Experience')
            ->assertSee('Education')
            ->assertSee('Certifications')
            ->assertSee('Languages')
            ->assertSee('Projects')
            ->assertSee('Achievements')
            ->assertSee('Other relevant information')
            ->assertSee('Save resume');

        $second = CandidateResume::factory()->for($candidate)->create(['title' => 'Secondary']);

        $this->actingAs($candidate, 'candidate')
            ->patch('/candidate/resumes/'.$second->id.'/default')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($resume->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
    }

    public function test_candidate_can_upload_a_resume_file(): void
    {
        Storage::fake('public');

        $candidate = Candidate::factory()->create();
        $file = UploadedFile::fake()->create('jane-resume.pdf', 200, 'application/pdf');

        $this->actingAs($candidate, 'candidate')
            ->post('/candidate/resumes/upload', [
                'title' => 'Uploaded CV',
                'resume_file' => $file,
                'is_default' => '1',
            ])
            ->assertRedirect('/candidate/resumes')
            ->assertSessionHas('success');

        $resume = CandidateResume::query()->first();

        $this->assertNotNull($resume);
        $this->assertSame(ResumeSource::Upload, $resume->source);
        $this->assertSame('jane-resume.pdf', $resume->original_filename);
        Storage::disk('public')->assertExists($resume->file_path);
    }

    public function test_candidate_cannot_access_another_candidates_resume(): void
    {
        $owner = Candidate::factory()->create();
        $intruder = Candidate::factory()->create();
        $resume = CandidateResume::factory()->for($owner)->create();

        $this->actingAs($intruder, 'candidate')
            ->get('/candidate/resumes/'.$resume->id.'/preview')
            ->assertNotFound();
    }
}
