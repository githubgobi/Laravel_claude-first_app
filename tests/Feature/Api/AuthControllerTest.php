<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Artisan::call('passport:client', [
            '--personal'       => true,
            '--name'           => 'Test Personal Access Client',
            '--no-interaction' => true,
        ]);
    }

    // ── Register ─────────────────────────────────────────────────────────────

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'access_token', 'token_type', 'user'])
            ->assertJson(['token_type' => 'Bearer']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_register_requires_name(): void
    {
        $this->postJson('/api/auth/register', [
            'email'                 => 'test@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name']);
    }

    public function test_register_requires_valid_email(): void
    {
        $this->postJson('/api/auth/register', [
            'name'                  => 'Test',
            'email'                 => 'not-an-email',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/auth/register', [
            'name'                  => 'Test',
            'email'                 => 'taken@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_register_rejects_password_shorter_than_8_chars(): void
    {
        $this->postJson('/api/auth/register', [
            'name'                  => 'Test',
            'email'                 => 'test@example.com',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }

    public function test_register_rejects_mismatched_password_confirmation(): void
    {
        $this->postJson('/api/auth/register', [
            'name'                  => 'Test',
            'email'                 => 'test@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Different123!',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => 'Password123!',
        ])->assertOk()->assertJsonStructure(['message', 'access_token', 'token_type', 'user']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'Password123!',
        ])->assertUnprocessable();
    }

    public function test_login_requires_email_field(): void
    {
        $this->postJson('/api/auth/login', [
            'password' => 'Password123!',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password_field(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }

    public function test_login_throttles_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email'    => $user->email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('test-token')->accessToken;

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out successfully.']);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $this->postJson('/api/auth/logout')->assertUnauthorized();
    }

    // ── User ──────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_get_own_data(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJson(['email' => $user->email, 'name' => $user->name]);
    }

    public function test_unauthenticated_request_to_user_endpoint_returns_401(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    // ── Forgot Password ───────────────────────────────────────────────────────

    public function test_forgot_password_sends_notification_to_registered_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/api/auth/forgot-password', ['email' => $user->email])
            ->assertOk()
            ->assertJson(['message' => 'If that email address is registered, a reset link has been sent.']);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_returns_same_message_for_unknown_email(): void
    {
        $this->postJson('/api/auth/forgot-password', ['email' => 'ghost@example.com'])
            ->assertOk()
            ->assertJson(['message' => 'If that email address is registered, a reset link has been sent.']);
    }

    public function test_forgot_password_validates_email_format(): void
    {
        $this->postJson('/api/auth/forgot-password', ['email' => 'not-an-email'])
            ->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_requires_email_field(): void
    {
        $this->postJson('/api/auth/forgot-password', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    // ── Reset Password ────────────────────────────────────────────────────────

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertOk()->assertJson(['message' => 'Password reset successfully.']);

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/reset-password', [
            'token'                 => 'invalid-token',
            'email'                 => $user->email,
            'password'              => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertUnprocessable();
    }

    public function test_reset_password_validates_new_password_min_length(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }

    public function test_reset_password_requires_all_fields(): void
    {
        $this->postJson('/api/auth/reset-password', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['token', 'email', 'password']);
    }
}
