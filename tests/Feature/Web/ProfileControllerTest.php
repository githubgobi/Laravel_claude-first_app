<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    // ── Edit page ─────────────────────────────────────────────────────────────

    public function test_profile_page_requires_authentication(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/profile')
            ->assertOk()
            ->assertViewIs('profile.edit');
    }

    // ── Update profile info ───────────────────────────────────────────────────

    public function test_user_can_update_name_and_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name'  => 'Updated Name',
                'email' => 'updated@example.com',
            ])->assertRedirect()
              ->assertSessionHas('status', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_can_keep_their_own_email_when_updating(): void
    {
        $user = User::factory()->create(['email' => 'mine@example.com']);

        $this->actingAs($user)
            ->patch('/profile', [
                'name'  => 'New Name',
                'email' => 'mine@example.com',
            ])->assertRedirect()
              ->assertSessionHas('status');
    }

    public function test_update_validates_required_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', ['email' => 'test@example.com'])
            ->assertSessionHasErrors(['name']);
    }

    public function test_update_validates_email_format(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', ['name' => 'Test', 'email' => 'not-an-email'])
            ->assertSessionHasErrors(['email']);
    }

    public function test_update_rejects_email_taken_by_another_user(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', ['name' => 'Test', 'email' => 'taken@example.com'])
            ->assertSessionHasErrors(['email']);
    }

    public function test_guest_cannot_update_profile(): void
    {
        $this->patch('/profile', ['name' => 'Test', 'email' => 'test@example.com'])
            ->assertRedirect(route('login'));
    }

    // ── Update password ───────────────────────────────────────────────────────

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass123!')]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password'      => 'OldPass123!',
                'password'              => 'NewPass456!',
                'password_confirmation' => 'NewPass456!',
            ])->assertRedirect()
              ->assertSessionHas('password_status', 'Password updated successfully.');

        $this->assertTrue(Hash::check('NewPass456!', $user->fresh()->password));
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password'      => 'wrong-password',
                'password'              => 'NewPass456!',
                'password_confirmation' => 'NewPass456!',
            ])->assertSessionHasErrors(['current_password']);
    }

    public function test_change_password_validates_minimum_length(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass123!')]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password'      => 'OldPass123!',
                'password'              => 'short',
                'password_confirmation' => 'short',
            ])->assertSessionHasErrors(['password']);
    }

    public function test_change_password_validates_confirmation_match(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass123!')]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password'      => 'OldPass123!',
                'password'              => 'NewPass456!',
                'password_confirmation' => 'Different789!',
            ])->assertSessionHasErrors(['password']);
    }

    public function test_guest_cannot_change_password(): void
    {
        $this->put('/profile/password', [
            'current_password'      => 'OldPass123!',
            'password'              => 'NewPass456!',
            'password_confirmation' => 'NewPass456!',
        ])->assertRedirect(route('login'));
    }
}
