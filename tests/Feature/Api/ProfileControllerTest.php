<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->getJson('/api/profile')
            ->assertOk()
            ->assertJson(['email' => $user->email, 'name' => $user->name]);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_user_can_update_their_name(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        Passport::actingAs($user);

        $this->patchJson('/api/profile', ['name' => 'New Name'])
            ->assertOk()
            ->assertJson(['user' => ['name' => 'New Name']]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_user_can_update_their_email(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->patchJson('/api/profile', ['email' => 'new@example.com'])
            ->assertOk()
            ->assertJson(['user' => ['email' => 'new@example.com']]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'new@example.com']);
    }

    public function test_user_can_keep_their_own_email_without_unique_conflict(): void
    {
        $user = User::factory()->create(['email' => 'mine@example.com']);
        Passport::actingAs($user);

        $this->patchJson('/api/profile', ['name' => 'New Name', 'email' => 'mine@example.com'])
            ->assertOk();
    }

    public function test_update_rejects_email_already_taken_by_another_user(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->patchJson('/api/profile', ['email' => 'taken@example.com'])
            ->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_update_rejects_invalid_email_format(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->patchJson('/api/profile', ['email' => 'not-an-email'])
            ->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_update_rejects_name_exceeding_255_chars(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->patchJson('/api/profile', ['name' => str_repeat('a', 256)])
            ->assertUnprocessable()->assertJsonValidationErrors(['name']);
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $this->patchJson('/api/profile', ['name' => 'Test'])->assertUnauthorized();
    }

    // ── Update Password ───────────────────────────────────────────────────────

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass123!')]);
        Passport::actingAs($user);

        $this->putJson('/api/profile/password', [
            'current_password'      => 'OldPass123!',
            'password'              => 'NewPass456!',
            'password_confirmation' => 'NewPass456!',
        ])->assertOk()->assertJson(['message' => 'Password updated successfully.']);

        $this->assertTrue(Hash::check('NewPass456!', $user->fresh()->password));
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->putJson('/api/profile/password', [
            'current_password'      => 'wrong-password',
            'password'              => 'NewPass456!',
            'password_confirmation' => 'NewPass456!',
        ])->assertUnprocessable()->assertJsonValidationErrors(['current_password']);
    }

    public function test_change_password_rejects_new_password_shorter_than_8_chars(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass123!')]);
        Passport::actingAs($user);

        $this->putJson('/api/profile/password', [
            'current_password'      => 'OldPass123!',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }

    public function test_change_password_rejects_mismatched_confirmation(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass123!')]);
        Passport::actingAs($user);

        $this->putJson('/api/profile/password', [
            'current_password'      => 'OldPass123!',
            'password'              => 'NewPass456!',
            'password_confirmation' => 'Different789!',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }

    public function test_unauthenticated_user_cannot_change_password(): void
    {
        $this->putJson('/api/profile/password', [
            'current_password'      => 'OldPass123!',
            'password'              => 'NewPass456!',
            'password_confirmation' => 'NewPass456!',
        ])->assertUnauthorized();
    }
}
