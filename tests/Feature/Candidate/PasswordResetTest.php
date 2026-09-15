<?php

namespace Tests\Feature\Candidate;

use App\Models\Candidate;
use App\Notifications\Candidate\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $this->get('/candidate/forgot-password')
            ->assertOk()
            ->assertSee('Forgot password');
    }

    public function test_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $candidate = Candidate::factory()->create(['email' => 'jane@example.com']);

        $this->post('/candidate/forgot-password', [
            'email' => 'jane@example.com',
        ])->assertSessionHas('success');

        Notification::assertSentTo($candidate, ResetPasswordNotification::class);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $candidate = Candidate::factory()->create(['email' => 'jane@example.com']);

        $token = Password::broker('candidates')->createToken($candidate);

        $this->post('/candidate/reset-password', [
            'token' => $token,
            'email' => 'jane@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect('/candidate/login')
            ->assertSessionHas('success');

        $this->assertTrue(password_verify('new-password', $candidate->fresh()->password));
    }
}
