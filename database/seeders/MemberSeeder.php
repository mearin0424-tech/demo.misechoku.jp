<?php

namespace Database\Seeders;

use App\Models\Cast;
use App\Models\CastProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cast_profiles')->delete();
        DB::table('casts')->delete();

        for ($i = 1; $i <= 20; $i++) {
            $castId = sprintf('c%08d', $i);
            $pref = match (true) {
                $i <= 5 => '東京都',
                $i <= 10 => '千葉県',
                $i <= 15 => '神奈川県',
                default => '愛知県',
            };

            Cast::query()->create([
                'id' => $castId,
                'email' => sprintf('cast%02d@example.com', $i),
                'password' => Hash::make(sprintf('pass%04d', $i)),
                'status' => 1,
                'identity_status' => 1,
                'last_login_at' => now(),
            ]);

            CastProfile::query()->create([
                'cast_id' => $castId,
                'nickname' => sprintf('cast%02d', $i),
                'name' => sprintf('キャスト%02d', $i),
                'birthday' => sprintf('%04d-%02d-%02d', rand(1988, 2002), rand(1, 12), rand(1, 28)),
                'pref' => $pref,
                'city' => sprintf('%s市', mb_substr($pref, 0, 2)),
                'addr1' => sprintf('addr1%02d', $i),
                'addr2' => sprintf('addr2%02d', $i),
                'addr3' => sprintf('addr3%02d', $i),
                'tel' => sprintf('0901234%04d', $i),
                'shift' => rand(0, 6),
                'exp' => rand(0, 1),
                'profession' => sprintf('職業%02d', $i),
                'years_exp' => (string) rand(1, 10),
                'where_work' => sprintf('勤務地%02d', $i),
                'pr' => sprintf('自己PR%02d', $i),
                'charm_point' => sprintf('チャームポイント%02d', $i),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
