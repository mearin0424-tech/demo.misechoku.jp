<?php

namespace Tests\Feature\Shop;

use App\Enums\ShopManagerRole;
use App\Models\ShopManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Shop staff management: 1-owner-per-shop rule and staff permission gates.
 */
class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function owner_can_add_staff_member(): void
    {
        $owner = $this->makeManager('m22220001', 's22220001', ShopManager::ROLE_OWNER);

        $this->actingAs($owner, 'shop')
            ->post(route('shop.mypage.staff.store'), [
                'name'  => 'New Staff',
                'email' => 'new-staff@test.example',
                'password' => 'StaffPass1!',
                'password_confirmation' => 'StaffPass1!',
            ])
            ->assertRedirect(route('shop.mypage.staff.index'));

        $this->assertDatabaseHas('shop_managers', [
            'shop_id' => 's22220001',
            'email'   => 'new-staff@test.example',
            'role'    => ShopManager::ROLE_STAFF,
        ]);
    }

    /** @test */
    public function newly_created_manager_is_always_staff_role(): void
    {
        $owner = $this->makeManager('m22220002', 's22220002', ShopManager::ROLE_OWNER);

        // Even if a malicious request sneaks in role=1, controller forces STAFF.
        $this->actingAs($owner, 'shop')
            ->post(route('shop.mypage.staff.store'), [
                'name'  => 'Tricky',
                'email' => 'tricky@test.example',
                'password' => 'StaffPass1!',
                'password_confirmation' => 'StaffPass1!',
                'role'  => ShopManager::ROLE_OWNER,   // ignored by store()
            ]);

        $row = DB::table('shop_managers')->where('email', 'tricky@test.example')->first();
        $this->assertNotNull($row);
        $this->assertSame(ShopManager::ROLE_STAFF, (int) $row->role);
    }

    /** @test */
    public function staff_cannot_add_another_staff(): void
    {
        // Shop with 1 owner + 1 staff. Staff tries to add someone.
        $this->makeManager('m22220003', 's22220003', ShopManager::ROLE_OWNER);
        $staff = $this->makeManager('m22220004', 's22220003', ShopManager::ROLE_STAFF, 'staff-add@test.example');

        $this->actingAs($staff, 'shop')
            ->post(route('shop.mypage.staff.store'), [
                'name'  => 'Impossible',
                'email' => 'impossible@test.example',
                'password' => 'StaffPass1!',
                'password_confirmation' => 'StaffPass1!',
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('shop_managers', ['email' => 'impossible@test.example']);
    }

    /** @test */
    public function staff_gets_403_on_owner_only_routes(): void
    {
        $this->makeManager('m22220005', 's22220005', ShopManager::ROLE_OWNER);
        $staff = $this->makeManager('m22220006', 's22220005', ShopManager::ROLE_STAFF, 'staff-403@test.example');

        // Sample of routes protected by shop.owner middleware.
        $this->actingAs($staff, 'shop')->get(route('shop.profile.edit'))->assertStatus(403);
        $this->actingAs($staff, 'shop')->get(route('shop.recruits.edit'))->assertStatus(403);
        $this->actingAs($staff, 'shop')->get(route('shop.mypage.staff.create'))->assertStatus(403);
    }

    /** @test */
    public function role_enum_bridges_int_constant_correctly(): void
    {
        $owner = $this->makeManager('m22220007', 's22220007', ShopManager::ROLE_OWNER);
        $enum = $owner->roleEnum();
        $this->assertSame(ShopManagerRole::Owner, $enum);
        $this->assertTrue($enum->canManageStaff());
    }

    private function makeManager(string $managerId, string $shopId, int $role, ?string $email = null): ShopManager
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
