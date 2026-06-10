<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    // ── Page renders ─────────────────────────────────────────────────────────

    public function test_login_page_is_accessible_to_guests(): void
    {
        $this->get('/login')->assertOk()->assertViewIs('auth.login');
    }

    public function test_register_page_is_accessible_to_guests(): void
    {
        $this->get('/register')->assertOk()->assertViewIs('auth.register');
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect(route('dashboard'));
    }

    public function test_authenticated_user_is_redirected_away_from_register(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/register')
            ->assertRedirect(route('dashboard'));
    }

    // ── Register ──────────────────────────────────────────────────────────────

    public function test_user_can_register_and_is_redirected_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'New User',
            'email'                 => 'new@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_register_validates_required_name(): void
    {
        $this->post('/register', [
            'email'                 => 'test@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors(['name']);
    }

    public function test_register_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', [
            'name'                  => 'Test',
            'email'                 => 'taken@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors(['email']);
    }

    public function test_register_validates_password_minimum_length(): void
    {
        $this->post('/register', [
            'name'                  => 'Test',
            'email'                 => 'test@example.com',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors(['password']);
    }

    public function test_register_validates_password_confirmation(): void
    {
        $this->post('/register', [
            'name'                  => 'Test',
            'email'                 => 'test@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Different!',
        ])->assertSessionHasErrors(['password']);
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    public function test_user_can_login_and_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $this->post('/login', [
            'email'    => 'nobody@example.com',
            'password' => 'Password123!',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_throttles_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email'    => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('email')
        );
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertViewIs('dashboard');
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guest_cannot_access_logout(): void
    {
        $this->post('/logout')->assertRedirect(route('login'));
    }
}
