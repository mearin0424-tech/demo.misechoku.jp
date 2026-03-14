<?php

namespace Database\Seeders;

use App\Models\ShopManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shop_managers')->delete();

        for ($i = 1; $i <= 10; $i++) {
            $shopId = sprintf('s%08d', $i);

            if (!DB::table('shops')->where('id', $shopId)->exists()) {
                continue;
            }

            ShopManager::query()->create([
                'id' => sprintf('m%08d', $i),
                'shop_id' => $shopId,
                'name' => sprintf('店舗担当者%02d', $i),
                'email' => sprintf('shop%03d@test.jp', $i),
                'password' => Hash::make(sprintf('pass%04d', $i)),
                'role' => 1,
                'status' => 1,
                'last_login_at' => now(),
            ]);
        }
    }
}

