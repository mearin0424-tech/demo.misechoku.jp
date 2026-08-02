<?php

namespace Tests\Feature\Shop;

use App\Models\ShopManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Emergency help broadcast (shop -> multiple casts).
 * Covers cooldown, validation, and message insertion.
 */
class HelpBroadcastTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function shop_can_broadcast_to_multiple_casts(): void
    {
        $manager = $this->makeShopManager('m66660001', 's66660001');
        $this->makeCast('c66660001');
        $this->makeCast('c66660002');

        $this->actingAs($manager, 'shop')
            ->postJson(route('shop.help-broadcast.send'), [
                'cast_ids' => ['c66660001', 'c66660002'],
                'body'     => 'Help wanted right now! Please reply if available.',
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'sent_count' => 2]);

        $this->assertSame(2, DB::table('messages')->where('shop_id', 's66660001')->count());
    }

    /** @test */
    public function repeat_broadcast_within_6h_is_skipped(): void
    {
        $manager = $this->makeShopManager('m66660002', 's66660002');
        $this->makeCast('c66660003');

        // Simulate a recent shop-to-cast message
        DB::table('messages')->insert([
            'cast_id' => 'c66660003',
            'shop_id' => 's66660002',
            'sender_type' => 2,
            'type' => 1,
            'content' => 'earlier ping',
            'is_read' => false,
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        $this->actingAs($manager, 'shop')
            ->postJson(route('shop.help-broadcast.send'), [
                'cast_ids' => ['c66660003'],
                'body'     => 'Help wanted right now!',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function empty_body_is_rejected(): void
    {
        $manager = $this->makeShopManager('m66660003', 's66660003');
        $this->makeCast('c66660004');

        $this->actingAs($manager, 'shop')
            ->postJson(route('shop.help-broadcast.send'), [
                'cast_ids' => ['c66660004'],
                'body'     => 'x',
            ])
            ->assertStatus(422);
    }

    private function makeCast(string $id): void
    {
        DB::table('casts')->insert([
            'id' => $id,
            'email' => $id . '@test.example',
            'password' => Hash::make('password'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeShopManager(string $managerId, string $shopId): ShopManager
    {
        DB::table('shops')->insert([
            'id' => $shopId,
            'email' => $shopId . '@test.example',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('shop_managers')->insert([
            'id' => $managerId,
            'shop_id' => $shopId,
            'name' => 'Test Owner',
            'email' => $managerId . '@test.example',
            'password' => Hash::make('password'),
            'role' => ShopManager::ROLE_OWNER,
            'status' => ShopManager::STATUS_ACTIVE,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return ShopManager::findOrFail($managerId);
    }
}
