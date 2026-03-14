<?php

namespace Database\Seeders;

use App\Models\SystemAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAccountSeeder extends Seeder
{
    public function run(): void
    {
        SystemAccount::query()->updateOrCreate(
            ['email' => 'admin@misechoku.jp'],
            [
                'name' => '管理者アカウント1',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => SystemAccount::ROLE_ADMIN,
                'is_active' => true,
                'remember_token' => null,
            ]
        );
    }
}
