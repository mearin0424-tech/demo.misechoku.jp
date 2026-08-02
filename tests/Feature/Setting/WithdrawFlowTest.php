<?php

namespace Tests\Feature\Setting;

use App\Models\Cast;
use App\Models\ShopManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Withdraw flow: PII anonymization + last-owner protection.
 */
class WithdrawFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cast_withdrawal_anonymizes_pii(): void
    {
        DB::table('casts')->insert([
            'id' => 'c44440001',
            'email' => 'quit@test.example',
            'password' => Hash::make('SecretPass1!'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('cast_profiles')->insert([
            'cast_id' => 'c44440001',
            'nickname' => 'Real Nickname',
            'name' => 'Real Name',
            'tel' => '090-0000-0000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cast = Cast::findOrFail('c44440001');
        $this->actingAs($cast, 'member')
            ->post(route('setting.account.withdraw'), [
                'current_password' => 'SecretPass1!',
                'agreement' => '1',
                'reason' => 'Testing withdrawal',
            ])
            ->assertRedirect(route('cast.login'));

        $row = DB::table('casts')->where('id', 'c44440001')->first();
        $this->assertNotNull($row->deleted_at);
        $this->assertNull($row->email);
        $this->assertNull($row->password);

        $profile = DB::table('cast_profiles')->where('cast_id', 'c44440001')->first();
        $this->assertNull($profile->name);
        $this->assertNull($profile->tel);
        $this->assertSame('退会したユーザー', $profile->nickname);
    }

    /** @test */
    public function last_owner_cannot_withdraw(): void
    {
        DB::table('shops')->insert([
            'id' => 's44440001',
            'email' => 'shop-quit@test.example',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('shop_managers')->insert([
            'id' => 'm44440001',
            'shop_id' => 's44440001',
            'name' => 'Sole Owner',
            'email' => 'owner-quit@test.example',
            'password' => Hash::make('OwnerPass1!'),
            'role' => ShopManager::ROLE_OWNER,
            'status' => ShopManager::STATUS_ACTIVE,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $owner = ShopManager::findOrFail('m44440001');
        $this->actingAs($owner, 'shop')
            ->from(route('setting.account'))
            ->post(route('setting.account.withdraw'), [
                'current_password' => 'OwnerPass1!',
                'agreement' => '1',
            ])
            ->assertSessionHasErrors('agreement');

        // Row still active
        $row = DB::table('shop_managers')->where('id', 'm44440001')->first();
        $this->assertNull($row->deleted_at ?? null);
        $this->assertNotNull($row->email);
    }

    /** @test */
    public function wrong_password_blocks_withdrawal(): void
    {
        DB::table('casts')->insert([
            'id' => 'c44440002',
            'email' => 'wrong-pw@test.example',
            'password' => Hash::make('CorrectPass!'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cast = Cast::findOrFail('c44440002');
        $this->actingAs($cast, 'member')
            ->from(route('setting.account'))
            ->post(route('setting.account.withdraw'), [
                'current_password' => 'WrongPass!',
                'agreement' => '1',
            ])
            ->assertSessionHasErrors('current_password');

        $row = DB::table('casts')->where('id', 'c44440002')->first();
        $this->assertNull($row->deleted_at);
        $this->assertNotNull($row->email);
    }
}
