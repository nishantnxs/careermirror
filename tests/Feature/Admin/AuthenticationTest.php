<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Sign in');
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = Admin::factory()->create(['email' => 'admin@example.com']);

        $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_admin_cannot_login_with_invalid_password(): void
    {
        Admin::factory()->create(['email' => 'admin@example.com']);

        $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_deactivated_admin_cannot_login(): void
    {
        Admin::factory()->inactive()->create(['email' => 'off@example.com']);

        $this->post('/admin/login', [
            'email' => 'off@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/plans')->assertRedirect('/admin/login');
        $this->get('/admin/settings')->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_is_redirected_away_from_login(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get('/admin/login')
            ->assertRedirect('/admin');
    }

    public function test_admin_can_logout(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post('/admin/logout')
            ->assertRedirect('/admin/login');

        $this->assertGuest('admin');
    }

    public function test_dashboard_is_reachable_once_authenticated(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard');
    }
}
