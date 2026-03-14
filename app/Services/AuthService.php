<?php

namespace App\Services;

use App\Models\Cast;
use App\Models\Shop;
use App\Models\ShopManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * 認証・登録関連の共通ビジネスロジック
 */
class AuthService
{
    private const ADMIN_ROLE_TYPE = 10;

    /**
     * キャストの新規登録
     */
    public function registerCast(array $data): Cast
    {
        return DB::transaction(function () use ($data): Cast {
            $castId = $this->nextSequentialId('casts', 'c');

            $cast = Cast::query()->create([
                'id' => $castId,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 1,
                'identity_status' => 1,
                'last_login_at' => now(),
            ]);

            $cast->profile()->create([
                'nickname' => $data['nickname'] ?? null,
                'name' => $data['name'] ?? null,
            ]);

            return $cast;
        });
    }

    /**
     * 店舗の新規登録
     */
    public function registerShop(array $data): Shop
    {
        return DB::transaction(function () use ($data): Shop {
            $shopId = $this->nextSequentialId('shops', 's');
            $managerId = $this->nextSequentialId('shop_managers', 'm');

            $shop = Shop::query()->create([
                'id' => $shopId,
                'email' => $data['email'] ?? null,
                'status' => 1,
                'license_status' => 1,
            ]);

            $shop->profile()->create([
                'shop_name' => $data['shop_name'],
                'pref' => $data['pref'] ?? '',
                'addr2' => $data['addr2'] ?? ($data['address'] ?? '-'),
                'city' => $data['city'] ?? null,
            ]);

            ShopManager::query()->create([
                'id' => $managerId,
                'shop_id' => $shopId,
                'name' => $data['manager_name'] ?? ($data['contact_name'] ?? null),
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 1,
                'status' => 1,
                'last_login_at' => now(),
            ]);

            return $shop;
        });
    }

    /**
     * マルチ認証ログイン試行
     */
    public function attemptLogin(string $email, string $password, string $guard)
    {
        return Auth::guard($guard)->attempt([
            'email' => $email,
            'password' => $password
        ]);
    }

    private function nextSequentialId(string $table, string $prefix): string
    {
        $lastId = DB::table($table)
            ->where('id', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('id');

        $nextNumber = $lastId
            ? ((int) substr($lastId, 1)) + 1
            : 1;

        return $prefix . str_pad((string) $nextNumber, 8, '0', STR_PAD_LEFT);
    }
}