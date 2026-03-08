<?php
// prj/app/Http/Controllers/Shops/HomeController.php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use App\Models\Cast; // キャストモデル

class HomeController extends Controller
{
    public function index()
    {
        $isCastPortal = request()->is('cast/*');

        if ($isCastPortal) {
            // キャスト側：お店を探している → お店一覧
            $shops = [
                ['id' => 1, 'name' => 'CLUB ETERNITY', 'age' => null, 'tags' => ['高時給', '即日払い'], 'like_count' => 8],
                ['id' => 2, 'name' => 'THE GOLDSTONE', 'age' => null, 'tags' => ['ノルマなし', '送りあり'], 'like_count' => 12],
                ['id' => 3, 'name' => 'Club Luxurious', 'age' => null, 'tags' => ['六本木', '高級'], 'like_count' => 5],
                ['id' => 4, 'name' => 'BAR STELLA', 'age' => null, 'tags' => ['落ち着いた', 'カジュアル'], 'like_count' => 3],
            ];
            return view('shops.home.index', [
                'pageId' => 'home',
                'items' => $shops,
                'itemType' => 'shop',
            ]);
        }

        // お店側：キャストを探している → キャスト一覧
        $casts = [
            ['id' => 1, 'name' => 'みさき', 'age' => 23, 'tags' => ['モデル系', 'お酒強い'], 'like_count' => 12],
            ['id' => 2, 'name' => '愛華', 'age' => 21, 'tags' => ['癒やし系', '聞き上手'], 'like_count' => 8],
            ['id' => 3, 'name' => 'さくら', 'age' => 25, 'tags' => ['元気系', 'トーク上手'], 'like_count' => 24],
            ['id' => 4, 'name' => 'ナナ', 'age' => 22, 'tags' => ['清楚系', 'お酒弱い'], 'like_count' => 5],
        ];
        return view('shops.home.index', [
            'pageId' => 'home',
            'items' => $casts,
            'itemType' => 'cast',
        ]);
    }
}