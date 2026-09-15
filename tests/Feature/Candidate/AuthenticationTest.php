<?php

namespace Tests\Feature\Candidate;

use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/candidate/login')
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('Sign in')
            ->assertSee('candidate', false)
            ->assertSee('hiring', false);
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $this->get('/candidate/register')
            ->assertOk()
            ->assertSee('Create your account')
            ->assertSee('candidate', false)
            ->assertSee('hiring', false)
            ->assertSee('Phone');
    }

    public function test_candidate_can_register_and_is_stored_in_the_candidates_table(): void
    {
        $this->post('/candidate/register', [
            'name' => 'Jane Candidate',
            'email' => 'jane@example.com',
            'phone' => '9876543210',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/candidate');

        $this->assertDatabaseHas('candidates', [
            'name' => 'Jane Candidate',
            'email' => 'jane@example.com',
            'phone' => '9876543210',
        ]);

        $this->assertDatabaseMissing('employers', [
            'email' => 'jane@example.com',
        ]);

        $this->assertAuthenticated('candidate');
        $this->assertGuest('employer');
    }

    public function test_registration_requires_a_unique_candidate_email(): void
    {
        Candidate::factory()->create(['email' => 'jane@example.com']);

        $this->post('/candidate/register', [
            'name' => 'Jane Two',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('candidate');
    }

    public function test_candidate_can_login_with_valid_credentials(): void
    {
        $candidate = Candidate::factory()->create(['email' => 'jane@example.com']);

        $this->post('/candidate/login', [
            'email' => 'jane@example.com',
            'password' => 'password',
        ])->assertRedirect('/candidate');

        $this->assertAuthenticatedAs($candidate, 'candidate');
        $this->assertNotNull($candidate->fresh()->last_login_at);
    }

    public function test_candidate_cannot_login_with_invalid_password(): void
    {
        Candidate::factory()->create(['email' => 'jane@example.com']);

        $this->post('/candidate/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('candidate');
    }

    public function test_deactivated_candidate_cannot_login(): void
    {
        Candidate::factory()->inactive()->create(['email' => 'off@example.com']);

        $this->post('/candidate/login', [
            'email' => 'off@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('candidate');
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/candidate')->assertRedirect('/candidate/login');
    }

    public function test_authenticated_candidate_is_redirected_away_from_login(): void
    {
        $this->actingAs(Candidate::factory()->create(), 'candidate')
            ->get('/candidate/login')
            ->assertRedirect('/candidate');
    }

    public function test_candidate_can_logout(): void
    {
        $this->actingAs(Candidate::factory()->create(), 'candidate')
            ->post('/candidate/logout')
            ->assertRedirect('/candidate/login');

        $this->assertGuest('candidate');
    }

    public function test_dashboard_is_reachable_once_authenticated(): void
    {
        $candidate = Candidate::factory()->create(['name' => 'Jane Candidate']);

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate')
            ->assertOk()
            ->assertSee('Jane Candidate');
    }
}
