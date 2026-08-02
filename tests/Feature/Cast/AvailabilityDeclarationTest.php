<?php

namespace Tests\Feature\Cast;

use App\Models\Cast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Cast "available now" declaration + Tier A/B reflection.
 */
class AvailabilityDeclarationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cast_can_declare_availability_for_2_hours(): void
    {
        $cast = $this->makeCast('c77770001');
        DB::table('cast_profiles')->insert(['cast_id' => 'c77770001', 'cast_id' => 'c77770001', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($cast, 'member')
            ->postJson(route('cast.mypage.availability.declare'), ['hours' => 2])
            ->assertOk()
            ->assertJson(['success' => true]);

        $profile = DB::table('cast_profiles')->where('cast_id', 'c77770001')->first();
        $this->assertNotNull($profile->available_until);
        $this->assertTrue(strtotime($profile->available_until) > time());
        $this->assertTrue(strtotime($profile->available_until) <= (time() + 2 * 3600 + 10));
    }

    /** @test */
    public function invalid_hours_value_is_rejected(): void
    {
        $cast = $this->makeCast('c77770002');
        DB::table('cast_profiles')->insert(['cast_id' => 'c77770002', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($cast, 'member')
            ->postJson(route('cast.mypage.availability.declare'), ['hours' => 5])
            ->assertStatus(422);
    }

    /** @test */
    public function cast_can_clear_availability(): void
    {
        $cast = $this->makeCast('c77770003');
        DB::table('cast_profiles')->insert([
            'cast_id' => 'c77770003',
            'available_until' => now()->addHours(4),
            'available_declared_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($cast, 'member')
            ->deleteJson(route('cast.mypage.availability.clear'))
            ->assertOk()
            ->assertJson(['success' => true]);

        $profile = DB::table('cast_profiles')->where('cast_id', 'c77770003')->first();
        $this->assertNull($profile->available_until);
    }

    private function makeCast(string $id): Cast
    {
        DB::table('casts')->insert([
            'id' => $id,
            'email' => $id . '@test.example',
            'password' => Hash::make('password'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return Cast::findOrFail($id);
    }
}
