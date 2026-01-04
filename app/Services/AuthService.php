<?php

namespace App\Services;

use App\Models\User;
use App\Models\Cast;
use App\Models\Shop;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * 認証・登録関連の共通ビジネスロジック
 */
class AuthService
{
    /**
     * キャストの新規登録
     */
    public function registerCast(array $data): Cast
    {
        // User (認証基盤) と Cast (属性) を同時に作成
        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'cast',
        ]);

        return Cast::create([
            'user_id' => $user->id,
            'nickname' => $data['nickname'],
            // その他の初期値
        ]);
    }

    /**
     * 店舗の新規登録
     */
    public function registerShop(array $data): Shop
    {
        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'shop',
        ]);

        return Shop::create([
            'user_id' => $user->id,
            'shop_name' => $data['shop_name'],
            // 求人票などの初期データ
        ]);
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
}