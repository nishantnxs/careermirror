<?php

namespace Tests\Feature\Employer;

use App\Models\Employer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/employer/login')
            ->assertOk()
            ->assertSee('Sign in');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $this->get('/employer/register')
            ->assertOk()
            ->assertSee('Create an account');
    }

    public function test_employer_can_register_and_is_stored_in_the_employers_table(): void
    {
        $this->post('/employer/register', [
            'name' => 'Alex Hiring',
            'company_name' => 'Acme Staffing',
            'email' => 'alex@acme.test',
            'phone' => '9876543210',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/employer');

        $this->assertDatabaseHas('employers', [
            'name' => 'Alex Hiring',
            'company_name' => 'Acme Staffing',
            'email' => 'alex@acme.test',
        ]);

        $this->assertDatabaseMissing('candidates', [
            'email' => 'alex@acme.test',
        ]);

        $this->assertAuthenticated('employer');
        $this->assertGuest('candidate');
    }

    public function test_registration_requires_a_company_name(): void
    {
        $this->post('/employer/register', [
            'name' => 'Alex Hiring',
            'email' => 'alex@acme.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('company_name');

        $this->assertGuest('employer');
    }

    public function test_employer_can_login_with_valid_credentials(): void
    {
        $employer = Employer::factory()->create(['email' => 'alex@acme.test']);

        $this->post('/employer/login', [
            'email' => 'alex@acme.test',
            'password' => 'password',
        ])->assertRedirect('/employer');

        $this->assertAuthenticatedAs($employer, 'employer');
        $this->assertNotNull($employer->fresh()->last_login_at);
    }

    public function test_employer_cannot_login_with_invalid_password(): void
    {
        Employer::factory()->create(['email' => 'alex@acme.test']);

        $this->post('/employer/login', [
            'email' => 'alex@acme.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('employer');
    }

    public function test_deactivated_employer_cannot_login(): void
    {
        Employer::factory()->inactive()->create(['email' => 'off@acme.test']);

        $this->post('/employer/login', [
            'email' => 'off@acme.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('employer');
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/employer')->assertRedirect('/employer/login');
    }

    public function test_authenticated_employer_is_redirected_away_from_login(): void
    {
        $this->actingAs(Employer::factory()->create(), 'employer')
            ->get('/employer/login')
            ->assertRedirect('/employer');
    }

    public function test_employer_can_logout(): void
    {
        $this->actingAs(Employer::factory()->create(), 'employer')
            ->post('/employer/logout')
            ->assertRedirect('/employer/login');

        $this->assertGuest('employer');
    }

    public function test_dashboard_is_reachable_once_authenticated(): void
    {
        $employer = Employer::factory()->create([
            'name' => 'Alex Hiring',
            'company_name' => 'Acme Staffing',
        ]);

        $this->actingAs($employer, 'employer')
            ->get('/employer')
            ->assertOk()
            ->assertSee('Acme Staffing');
    }
}
