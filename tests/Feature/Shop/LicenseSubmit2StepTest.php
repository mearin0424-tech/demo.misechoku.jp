<?php

namespace Tests\Feature\Shop;

use App\Models\ShopLicenseDocument;
use App\Models\ShopManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Shop license document 2-step submit flow (upload -> request-review -> withdraw).
 */
class LicenseSubmit2StepTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    /** @test */
    public function upload_creates_draft_status_row(): void
    {
        $manager = $this->makeShopManager('m99990001', 's99990001', ShopManager::ROLE_OWNER);

        $this->actingAs($manager, 'shop')
            ->post(route('shop.mypage.documents.upload'), [
                'type' => 'business',
                'file' => UploadedFile::fake()->image('license.jpg'),
                'expired_at' => now()->addYear()->format('Y-m-d'),
            ])
            ->assertOk();

        $doc = DB::table('shop_license_documents')
            ->where('shop_id', 's99990001')
            ->where('type', 'business')
            ->first();
        $this->assertNotNull($doc);
        $this->assertSame(ShopLicenseDocument::STATUS_DRAFT, (int) $doc->status);
    }

    /** @test */
    public function request_review_transitions_draft_to_pending(): void
    {
        $manager = $this->makeShopManager('m99990002', 's99990002', ShopManager::ROLE_OWNER);

        // Seed a draft first
        DB::table('shop_license_documents')->insert([
            'shop_id' => 's99990002',
            'type' => 'business',
            'image_path' => 'private/dummy/license.jpg',
            'status' => ShopLicenseDocument::STATUS_DRAFT,
            'expired_at' => now()->addYear()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($manager, 'shop')
            ->postJson(route('shop.mypage.documents.request-review'), [
                'type' => 'business',
                'expired_at' => now()->addYear()->format('Y-m-d'),
            ])
            ->assertOk();

        $status = DB::table('shop_license_documents')
            ->where('shop_id', 's99990002')
            ->where('type', 'business')
            ->value('status');
        $this->assertSame(ShopLicenseDocument::STATUS_PENDING, (int) $status);
    }

    /** @test */
    public function withdraw_transitions_pending_back_to_draft(): void
    {
        $manager = $this->makeShopManager('m99990003', 's99990003', ShopManager::ROLE_OWNER);
        DB::table('shop_license_documents')->insert([
            'shop_id' => 's99990003',
            'type' => 'business',
            'image_path' => 'private/dummy/license.jpg',
            'status' => ShopLicenseDocument::STATUS_PENDING,
            'expired_at' => now()->addYear()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($manager, 'shop')
            ->post(route('shop.mypage.documents.withdraw-review'), [
                'type' => 'business',
            ])
            ->assertRedirect();

        $status = DB::table('shop_license_documents')
            ->where('shop_id', 's99990003')
            ->where('type', 'business')
            ->value('status');
        $this->assertSame(ShopLicenseDocument::STATUS_DRAFT, (int) $status);
    }

    /** @test */
    public function staff_cannot_upload_license(): void
    {
        // Shop with 1 owner + 1 staff
        $this->makeShopManager('m99990004', 's99990004', ShopManager::ROLE_OWNER);
        $staff = $this->makeShopManager('m99990005', 's99990004', ShopManager::ROLE_STAFF, 'staff-lic@test.example');

        $this->actingAs($staff, 'shop')
            ->post(route('shop.mypage.documents.upload'), [
                'type' => 'business',
                'file' => UploadedFile::fake()->image('sneak.jpg'),
                'expired_at' => now()->addYear()->format('Y-m-d'),
            ])
            ->assertStatus(403);
    }

    private function makeShopManager(string $managerId, string $shopId, int $role, ?string $email = null): ShopManager
    {
        DB::table('shops')->insertOrIgnore([
            'id' => $shopId,
            'email' => $shopId . '@test.example',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('shop_managers')->insert([
            'id' => $managerId,
            'shop_id' => $shopId,
            'name' => $role === ShopManager::ROLE_OWNER ? 'Owner' : 'Staff',
            'email' => $email ?? ($managerId . '@test.example'),
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => ShopManager::STATUS_ACTIVE,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return ShopManager::findOrFail($managerId);
    }
}
