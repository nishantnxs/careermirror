<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Employer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $profile
     */
    private function fakeGoogleProfile(array $profile = []): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'test-access-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ]),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'sub' => $profile['id'] ?? 'google-123',
                'name' => $profile['name'] ?? 'Jane Candidate',
                'email' => $profile['email'] ?? 'jane@gmail.com',
                'email_verified' => true,
            ]),
        ]);
    }

    private function googleCallbackState(string $redirectUrl): string
    {
        $query = [];
        parse_str((string) parse_url($redirectUrl, PHP_URL_QUERY), $query);

        return (string) ($query['state'] ?? '');
    }

    public function test_login_page_links_to_google_for_candidates(): void
    {
        $this->get('/candidate/login')
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee(route('candidate.auth.google'), false)
            ->assertDontSee('Coming soon');
    }

    public function test_candidate_google_redirect_goes_to_google(): void
    {
        $response = $this->get('/candidate/auth/google');

        $response->assertRedirect();
        $this->assertStringContainsString(
            'https://accounts.google.com/o/oauth2/v2/auth',
            (string) $response->headers->get('Location'),
        );
    }

    public function test_candidate_can_register_with_google(): void
    {
        $this->fakeGoogleProfile();

        $state = $this->googleCallbackState(
            (string) $this->get('/candidate/auth/google')->headers->get('Location')
        );

        $this->get('/candidate/auth/google/callback?code=test-code&state='.$state)
            ->assertRedirect('/candidate')
            ->assertSessionHas('success');

        $this->assertAuthenticated('candidate');
        $this->assertDatabaseHas('candidates', [
            'email' => 'jane@gmail.com',
            'google_id' => 'google-123',
            'name' => 'Jane Candidate',
        ]);
        $this->assertGuest('employer');
    }

    public function test_existing_candidate_can_sign_in_with_google(): void
    {
        $candidate = Candidate::factory()->create([
            'email' => 'jane@gmail.com',
            'name' => 'Jane Candidate',
        ]);

        $this->fakeGoogleProfile();

        $state = $this->googleCallbackState(
            (string) $this->get('/candidate/auth/google')->headers->get('Location')
        );

        $this->get('/candidate/auth/google/callback?code=test-code&state='.$state)
            ->assertRedirect('/candidate');

        $this->assertAuthenticatedAs($candidate, 'candidate');
        $this->assertSame('google-123', $candidate->fresh()->google_id);
        $this->assertSame(1, Candidate::query()->count());
    }

    public function test_inactive_candidate_cannot_sign_in_with_google(): void
    {
        Candidate::factory()->inactive()->create([
            'email' => 'jane@gmail.com',
        ]);

        $this->fakeGoogleProfile();

        $state = $this->googleCallbackState(
            (string) $this->get('/candidate/auth/google')->headers->get('Location')
        );

        $this->get('/candidate/auth/google/callback?code=test-code&state='.$state)
            ->assertRedirect('/candidate/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest('candidate');
    }

    public function test_candidate_google_sign_in_allows_an_email_already_used_by_an_employer(): void
    {
        Employer::factory()->create(['email' => 'shared@gmail.com']);

        $this->fakeGoogleProfile([
            'id' => 'google-candidate-shared',
            'email' => 'shared@gmail.com',
        ]);

        $state = $this->googleCallbackState(
            (string) $this->get('/candidate/auth/google')->headers->get('Location')
        );

        $this->get('/candidate/auth/google/callback?code=test-code&state='.$state)
            ->assertRedirect('/candidate');

        $this->assertAuthenticated('candidate');
        $this->assertDatabaseHas('candidates', [
            'email' => 'shared@gmail.com',
            'google_id' => 'google-candidate-shared',
        ]);
        $this->assertDatabaseHas('employers', [
            'email' => 'shared@gmail.com',
        ]);
        $this->assertGuest('employer');
    }

    public function test_google_callback_rejects_invalid_state(): void
    {
        $this->fakeGoogleProfile();

        $this->get('/candidate/auth/google');

        $this->get('/candidate/auth/google/callback?code=test-code&state=tampered')
            ->assertRedirect('/candidate/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest('candidate');
    }

    public function test_employer_can_register_with_google(): void
    {
        $this->fakeGoogleProfile([
            'id' => 'google-employer-1',
            'name' => 'Alex Hiring',
            'email' => 'alex@gmail.com',
        ]);

        $state = $this->googleCallbackState(
            (string) $this->get('/employer/auth/google')->headers->get('Location')
        );

        $this->get('/employer/auth/google/callback?code=test-code&state='.$state)
            ->assertRedirect('/employer');

        $this->assertAuthenticated('employer');
        $this->assertDatabaseHas('employers', [
            'email' => 'alex@gmail.com',
            'google_id' => 'google-employer-1',
            'name' => 'Alex Hiring',
            'company_name' => 'Alex Hiring',
        ]);
        $this->assertTrue(Employer::query()->where('email', 'alex@gmail.com')->first()->domains()->exists());
    }

    public function test_existing_employer_can_sign_in_with_google(): void
    {
        $employer = Employer::factory()->create([
            'email' => 'alex@gmail.com',
            'name' => 'Alex Hiring',
        ]);

        $this->fakeGoogleProfile([
            'id' => 'google-employer-1',
            'name' => 'Alex Hiring',
            'email' => 'alex@gmail.com',
        ]);

        $state = $this->googleCallbackState(
            (string) $this->get('/employer/auth/google')->headers->get('Location')
        );

        $this->get('/employer/auth/google/callback?code=test-code&state='.$state)
            ->assertRedirect('/employer');

        $this->assertAuthenticatedAs($employer, 'employer');
        $this->assertSame('google-employer-1', $employer->fresh()->google_id);
    }

    public function test_employer_google_sign_in_allows_an_email_already_used_by_a_candidate(): void
    {
        Candidate::factory()->create(['email' => 'shared@gmail.com']);

        $this->fakeGoogleProfile([
            'id' => 'google-employer-shared',
            'name' => 'Alex Hiring',
            'email' => 'shared@gmail.com',
        ]);

        $state = $this->googleCallbackState(
            (string) $this->get('/employer/auth/google')->headers->get('Location')
        );

        $this->get('/employer/auth/google/callback?code=test-code&state='.$state)
            ->assertRedirect('/employer');

        $this->assertAuthenticated('employer');
        $this->assertDatabaseHas('employers', [
            'email' => 'shared@gmail.com',
            'google_id' => 'google-employer-shared',
        ]);
        $this->assertGuest('candidate');
    }
}
