<?php

namespace Tests\Feature;

use App\Enums\UserReportStatus;
use App\Models\Cast;
use App\Models\UserReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * User report submission (cast -> shop and shop -> cast) + admin triage.
 */
class UserReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cast_can_submit_report_against_a_shop(): void
    {
        $cast = $this->makeCast('c88880001');
        $shopId = $this->makeShop('s88880001');

        $this->actingAs($cast, 'member')
            ->postJson(route('pages.user-report.store'), [
                'target_type' => 'shop',
                'target_id'   => $shopId,
                'reason'      => 'contact_info',
                'detail'      => 'LINE ID sharing attempt',
                'context_type' => 'talk',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_reports', [
            'reporter_type' => 'cast',
            'reporter_id'   => 'c88880001',
            'target_type'   => 'shop',
            'target_id'     => $shopId,
            'reason'        => 'contact_info',
            'status'        => UserReportStatus::Pending->value,
        ]);
    }

    /** @test */
    public function repeat_report_within_24h_is_deduplicated(): void
    {
        $cast = $this->makeCast('c88880002');
        $shopId = $this->makeShop('s88880002');

        UserReport::create([
            'reporter_type' => 'cast',
            'reporter_id'   => 'c88880002',
            'target_type'   => 'shop',
            'target_id'     => $shopId,
            'reason'        => 'harassment',
            'status'        => UserReportStatus::Pending->value,
            'created_at'    => now()->subHours(2),
            'updated_at'    => now()->subHours(2),
        ]);

        $this->actingAs($cast, 'member')
            ->postJson(route('pages.user-report.store'), [
                'target_type' => 'shop',
                'target_id'   => $shopId,
                'reason'      => 'inappropriate',
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'deduped' => true]);

        $this->assertSame(1, UserReport::count());
    }

    /** @test */
    public function self_report_is_rejected(): void
    {
        $cast = $this->makeCast('c88880003');

        $this->actingAs($cast, 'member')
            ->postJson(route('pages.user-report.store'), [
                'target_type' => 'cast',
                'target_id'   => 'c88880003',
                'reason'      => 'other',
            ])
            ->assertStatus(422);
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

    private function makeShop(string $id): string
    {
        DB::table('shops')->insert([
            'id' => $id,
            'email' => $id . '@test.example',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $id;
    }
}
