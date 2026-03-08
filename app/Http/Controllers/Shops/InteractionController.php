<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InteractionController extends Controller
{
    public function index()
    {
        // 旧ロジックの変数を再現（実運用では各Modelから取得）
        $keepCasts = [
            [
                'id' => 1, 'name' => 'みさき', 'age' => 23, 'img' => '', 'profession' => 'モデル', 
                'pref' => '東京都', 'city' => '港区', 'height' => 165, 'b' => 85, 'w' => 58, 'h' => 86
            ]
        ];
        
        $likeCasts = [
            [
                'id' => 2, 'name' => '愛華', 'age' => 21, 'img' => '', 'profession' => '女子大生',
                'pref' => '東京都', 'city' => '渋谷区', 'created_at' => '2026-01-02 10:00:00', 'is_match' => false
            ]
        ];
        
        $footprintCasts = [
            [
                'id' => 3, 'name' => 'Rena', 'age' => 25, 'img' => '', 'profession' => 'フリーランス', 
                'pref' => '神奈川県', 'city' => '横浜市', 'visited_at' => '2026-01-02 18:00:00'
            ]
        ];

        return view('shops.interaction.index', [
            'pageId' => 'connection',
            'keepCasts' => $keepCasts,
            'likeCasts' => $likeCasts,
            'footprintCasts' => $footprintCasts,
        ]);
    }
}