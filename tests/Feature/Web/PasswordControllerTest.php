<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    // ── Forgot Password — page ────────────────────────────────────────────────

    public function test_forgot_password_page_renders(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertViewIs('auth.forgot-password');
    }

    // ── Forgot Password — send link ───────────────────────────────────────────

    public function test_reset_link_is_sent_for_registered_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_same_success_message_is_returned_for_unknown_email(): void
    {
        $this->post('/forgot-password', ['email' => 'ghost@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_forgot_password_validates_email_format(): void
    {
        $this->post('/forgot-password', ['email' => 'not-an-email'])
            ->assertSessionHasErrors(['email']);
    }

    public function test_forgot_password_requires_email_field(): void
    {
        $this->post('/forgot-password', [])
            ->assertSessionHasErrors(['email']);
    }

    // ── Reset Password — page ─────────────────────────────────────────────────

    public function test_reset_password_page_renders_with_token(): void
    {
        $this->get('/reset-password/some-token?email=test@example.com')
            ->assertOk()
            ->assertViewIs('auth.reset-password')
            ->assertViewHas('token', 'some-token');
    }

    // ── Reset Password — submit ───────────────────────────────────────────────

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertRedirect(route('login'))
          ->assertSessionHas('status', 'Password reset successfully. You can now log in.');

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->post('/reset-password', [
            'token'                 => 'bad-token',
            'email'                 => $user->email,
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertSessionHasErrors(['email']);
    }

    public function test_reset_password_validates_new_password_min_length(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors(['password']);
    }

    public function test_reset_password_requires_token(): void
    {
        $this->post('/reset-password', [
            'email'                 => 'test@example.com',
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertSessionHasErrors(['token']);
    }

    public function test_reset_password_validates_confirmation_match(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'Different456!',
        ])->assertSessionHasErrors(['password']);
    }
}
