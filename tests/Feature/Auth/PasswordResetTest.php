<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Password reset flow (cast + shop).
 *
 * Covers:
 *   - Forgot form submits with unknown email -> generic success message (enumeration guard)
 *   - Forgot form submits with known email  -> token row inserted
 *   - Reset form submits with valid token   -> password updated + token deleted
 *   - Reset form submits with expired token -> rejected
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function forgot_form_returns_generic_success_for_unknown_email(): void
    {
        $res = $this->post(route('password.forgot.post'), ['email' => 'nobody@nowhere.example']);
        $res->assertRedirect(route('password.forgot.show'))->assertSessionHas('message');
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'nobody@nowhere.example']);
    }

    /** @test */
    public function forgot_form_creates_token_for_registered_cast(): void
    {
        DB::table('casts')->insert([
            'id' => 'c99999901',
            'email' => 'test-cast-reset@example.com',
            'password' => Hash::make('OldPass123!'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('password.forgot.post'), ['email' => 'test-cast-reset@example.com'])
            ->assertRedirect(route('password.forgot.show'));

        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'test-cast-reset@example.com']);
    }

    /** @test */
    public function reset_updates_password_and_clears_token(): void
    {
        DB::table('casts')->insert([
            'id' => 'c99999902',
            'email' => 'reset-me@example.com',
            'password' => Hash::make('OldPass123!'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // trigger forgot flow to seed a valid token
        $this->post(route('password.forgot.post'), ['email' => 'reset-me@example.com']);
        // we don't know the raw token (hashed) — so re-insert deterministically for the test
        $rawToken = 'test-raw-token-123456789';
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'reset-me@example.com'],
            ['token' => Hash::make($rawToken), 'created_at' => now()]
        );

        $res = $this->post(route('password.reset.post'), [
            'token' => $rawToken,
            'email' => 'reset-me@example.com',
            'password' => 'NewPass456!',
            'password_confirmation' => 'NewPass456!',
        ]);
        $res->assertRedirect(route('cast.login'))->assertSessionHas('message');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'reset-me@example.com']);
        $newHash = DB::table('casts')->where('id', 'c99999902')->value('password');
        $this->assertTrue(Hash::check('NewPass456!', $newHash));
    }

    /** @test */
    public function reset_with_expired_token_is_rejected(): void
    {
        DB::table('casts')->insert([
            'id' => 'c99999903',
            'email' => 'expired-token@example.com',
            'password' => Hash::make('OldPass123!'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('password_reset_tokens')->insert([
            'email' => 'expired-token@example.com',
            'token' => Hash::make('some-raw'),
            'created_at' => now()->subMinutes(90),   // beyond 60 min TTL
        ]);

        $this->from(route('password.reset.show', ['token' => 'some-raw', 'email' => 'expired-token@example.com']))
            ->post(route('password.reset.post'), [
                'token' => 'some-raw',
                'email' => 'expired-token@example.com',
                'password' => 'ShouldNotApply!',
                'password_confirmation' => 'ShouldNotApply!',
            ])
            ->assertSessionHasErrors('token');

        $stillHashed = DB::table('casts')->where('id', 'c99999903')->value('password');
        $this->assertTrue(Hash::check('OldPass123!', $stillHashed));
    }
}
