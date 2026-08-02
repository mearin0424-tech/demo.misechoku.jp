<?php

namespace Tests\Feature\Shop;

use App\Models\ShopManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Shop reply to cast review (post / edit / delete).
 */
class ReviewReplyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function shop_can_post_reply_to_own_review(): void
    {
        $manager = $this->makeShopManager('m33330001', 's33330001');
        $reviewId = $this->makeReview('s33330001', 'c33330001');

        $this->actingAs($manager, 'shop')
            ->postJson(route('shop.review.reply'), [
                'id' => $reviewId,
                'reply_body' => 'Thank you for the review!',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $row = DB::table('reviews')->where('id', $reviewId)->first();
        $this->assertSame('Thank you for the review!', $row->reply_body);
        $this->assertNotNull($row->reply_at);
    }

    /** @test */
    public function empty_body_deletes_the_reply(): void
    {
        $manager = $this->makeShopManager('m33330002', 's33330002');
        $reviewId = $this->makeReview('s33330002', 'c33330002', 'Existing reply');

        $this->actingAs($manager, 'shop')
            ->postJson(route('shop.review.reply'), [
                'id' => $reviewId,
                'reply_body' => '',
            ])
            ->assertOk();

        $row = DB::table('reviews')->where('id', $reviewId)->first();
        $this->assertNull($row->reply_body);
        $this->assertNull($row->reply_at);
    }

    /** @test */
    public function shop_cannot_reply_to_another_shops_review(): void
    {
        $ownerA = $this->makeShopManager('m33330003', 's33330003');
        $reviewId = $this->makeReview('s33330099', 'c33330003');   // review belongs to a different shop

        $this->actingAs($ownerA, 'shop')
            ->postJson(route('shop.review.reply'), [
                'id' => $reviewId,
                'reply_body' => 'Hacking attempt',
            ])
            ->assertStatus(403);

        $row = DB::table('reviews')->where('id', $reviewId)->first();
        $this->assertNull($row->reply_body);
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
            'name' => 'Owner',
            'email' => $managerId . '@test.example',
            'password' => Hash::make('password'),
            'role' => ShopManager::ROLE_OWNER,
            'status' => ShopManager::STATUS_ACTIVE,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return ShopManager::findOrFail($managerId);
    }

    private function makeReview(string $shopId, string $castId, ?string $existingReply = null): int
    {
        // Cast must exist for FK
        DB::table('casts')->insertOrIgnore([
            'id' => $castId,
            'email' => $castId . '@test.example',
            'password' => Hash::make('password'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Shop must exist for FK
        DB::table('shops')->insertOrIgnore([
            'id' => $shopId,
            'email' => $shopId . '@test.example',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('reviews')->insertGetId([
            'cast_id' => $castId,
            'shop_id' => $shopId,
            'contents' => 'Great place!',
            'eva' => 4.5,
            'is_anonymous' => 1,
            'reply_body' => $existingReply,
            'reply_at'   => $existingReply ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
