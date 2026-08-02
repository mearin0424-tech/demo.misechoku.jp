<?php

namespace Tests\Feature\Auth;

use App\Models\Cast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Email verification (signed URL + resend).
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function signed_verification_link_sets_email_verified_at(): void
    {
        DB::table('casts')->insert([
            'id' => 'c55550001',
            'email' => 'verify-me@test.example',
            'password' => Hash::make('password'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $url = URL::temporarySignedRoute(
            'auth.email.verify',
            now()->addMinutes(60),
            ['type' => 'cast', 'id' => 'c55550001']
        );

        $this->get($url)->assertRedirect(route('cast.login'));

        $verifiedAt = DB::table('casts')->where('id', 'c55550001')->value('email_verified_at');
        $this->assertNotNull($verifiedAt);
    }

    /** @test */
    public function unsigned_url_is_rejected(): void
    {
        DB::table('casts')->insert([
            'id' => 'c55550002',
            'email' => 'unsigned@test.example',
            'password' => Hash::make('password'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Direct hit without signature params
        $this->get(route('auth.email.verify', ['type' => 'cast', 'id' => 'c55550002']))
            ->assertRedirect(route('login.demo'));

        $verifiedAt = DB::table('casts')->where('id', 'c55550002')->value('email_verified_at');
        $this->assertNull($verifiedAt);
    }

    /** @test */
    public function resend_requires_login(): void
    {
        $this->post(route('auth.email.send'))
            ->assertRedirect();   // back() to previous, with error
    }
}
