<?php
// prj/app/Http/Controllers/Shops/HomeController.php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use App\Models\Cast; // キャストモデル

class HomeController extends Controller
{
    public function index()
    {
        // 実際には条件に合うキャストを取得
        $casts = [
            ['id' => 1, 'name' => '美咲', 'age' => 23, 'tags' => ['モデル系', 'お酒強い']],
            ['id' => 2, 'name' => '愛華', 'age' => 21, 'tags' => ['癒やし系', '聞き上手']],
            ['id' => 3, 'name' => 'さくら', 'age' => 25, 'tags' => ['元気系', 'トーク上手']],
            ['id' => 4, 'name' => 'ナナ', 'age' => 22, 'tags' => ['清楚系', 'お酒弱い']],
        ];

        return view('shops.home.index', [
            'pageId' => 'home',
            'casts' => $casts
        ]);
    }
}