<?php

namespace Tests\Feature\Candidate;

use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_view_and_update_profile(): void
    {
        Storage::fake('public');

        $candidate = Candidate::factory()->create([
            'name' => 'Jane Candidate',
            'email' => 'jane@example.com',
        ]);

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/profile')
            ->assertOk()
            ->assertSee('My profile');

        $this->actingAs($candidate, 'candidate')
            ->put('/candidate/profile', [
                'name' => 'Jane Updated',
                'email' => 'jane.updated@example.com',
                'phone' => '9998887777',
                'headline' => 'Laravel Developer',
                'summary' => 'Builds job portals.',
                'location' => 'Mumbai',
                'current_title' => 'Software Engineer',
                'years_of_experience' => 4,
                'preferred_job_type' => 'full-time',
                'currency' => 'INR',
                'expected_salary_min' => 800000,
                'expected_salary_max' => 1200000,
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ])
            ->assertRedirect('/candidate/profile')
            ->assertSessionHas('success');

        $candidate->refresh();

        $this->assertSame('Jane Updated', $candidate->name);
        $this->assertSame('jane.updated@example.com', $candidate->email);
        $this->assertSame('Laravel Developer', $candidate->headline);
        $this->assertSame('Mumbai', $candidate->location);
        $this->assertNotNull($candidate->avatar_path);
        Storage::disk('public')->assertExists($candidate->avatar_path);
    }

    public function test_candidate_can_change_password_from_settings(): void
    {
        $candidate = Candidate::factory()->create();

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/settings')
            ->assertOk();

        $this->actingAs($candidate, 'candidate')
            ->put('/candidate/settings/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect('/candidate/settings')
            ->assertSessionHas('success');

        $this->assertTrue(password_verify('new-password', $candidate->fresh()->password));
    }
}
