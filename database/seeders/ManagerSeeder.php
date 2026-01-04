<?php

namespace Database\Seeders;

use App\Models\Manager;
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
        Manager::truncate();

        $administrators_data = [];
        for ($i = 1; $i <= 10; $i++) {
            $administrators_data[] = [
                'email' => sprintf('shop%03d@test.jp', $i),
                'password' => sprintf('pass%04d', $i),
                'shop_name' => sprintf('shopName%03d', $i),
                'name' => sprintf('Name%03d', $i),

            ];
        }

        foreach($administrators_data as $data) {
            $administrator = new Manager();
            $administrator->email = $data['email'];
            $administrator->password = Hash::make($data['password']);
            $administrator->shop_name = $data['shop_name'];
            $administrator->name = $data['name'];
            $administrator->save();
        }
    }
}

