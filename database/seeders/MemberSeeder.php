<?php

namespace Database\Seeders;

use App\Models\Member;
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
        Member::truncate();

        $member_data = [];
        for ($i = 1; $i <= 100; $i++) {

            if($i<=20) {
                $pref = "東京都";
                $jendar = 1;
            }else if($i<=40) {
                 $pref = "千葉県";
                $jendar = 1;

            }else if($i<=60) {
                 $pref = "北海道";
                $jendar = 1;

            }else if($i<=80) {
                 $pref = "神奈川県";
                $jendar = 1;

            }else if($i<=100) {
                 $pref = "愛知県";
                $jendar = 2;
            }
            
            Member::create( [
                'email' => sprintf('member%02d@example.com', $i),
                'password' => sprintf('pass%04d', $i),
                'line_user_id' => sprintf('line%04d', $i),
                'nickname' => sprintf('nickname%04d', $i),
                'name' => sprintf('名前%04d', $i),
                'kana' => sprintf('なまえ%04d', $i),
                'zip' => sprintf('%03d', $i).sprintf('%04d', $i),
                'pref'=>$pref,
                'addr1' => sprintf('addr1%04d', $i),
                'addr2' => sprintf('addr2%04d', $i),
                'addr3' => sprintf('addr3%04d', $i),
                'birthday_y' => rand(1975,2000),
                'birthday_m' => rand(1,12),
                'birthday_d' => rand(1,28),
                'height' => rand(140,200),
                'weight' => rand(40,100),
                'b' => rand(75,100),
                'w' => rand(50,100),
                'h' => rand(80,100),
                'shift'=>rand(0,6),
                'profession'=> sprintf('職業%04d', $i),
                'exp'=> rand(0,1),
                'years_exp'=> rand(1,15),
                'where_work'=> sprintf('where_work%04d', $i),
                'pr'=> sprintf('pr%04d', $i),
                'charm_point'=> sprintf('charm_point%04d', $i)
            ]);
        }
/*
        foreach($member_data as $data) {
            $member = new Member();
            $member->email = $data['email'];
            $member->password = Hash::make($data['password']);
            $member->save();
        }
*/
    }
}
