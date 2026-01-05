<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Common\SearchController as BaseSearchController;
use Illuminate\Http\Request;

class SearchController extends BaseSearchController
{
    public function index(Request $request)
    {
        // タイムラインデータ（店舗の投稿）
        $timelineData = [
            [
                'name' => 'CLUB ETERNITY',
                'img' => asset('storage/mock/shops/out-1.png'),
                'time' => '5分前',
                'text' => "【急募】本日21時から働ける方募集！\n体験入店も歓迎です。",
                'tags' => ['高時給', '即日払い']
            ],
            [
                'name' => 'THE GOLDSTONE',
                'img' => asset('storage/mock/shops/out-2.png'),
                'time' => '1時間前',
                'text' => "週末の大型イベントに向けてキャスト大募集✨",
                'tags' => ['ノルマなし', '送りあり']
            ]
        ];

        // 店舗一覧（$itemsに改名）
        $items = [
            ['id' => 1, 'shop_name' => 'CLUB ETERNITY', 'pref' => '東京都', 'city' => '港区', 'main_img' => asset('storage/mock/shops/out-1.png')],
            ['id' => 2, 'shop_name' => 'THE GOLDSTONE', 'pref' => '東京都', 'city' => '中央区', 'main_img' => asset('storage/mock/shops/out-2.png')],
        ];

        return $this->renderIndex([
            'guideMessage' => "あなたの希望に合うお店を探そう！\n条件を絞り込んで検索してみてね。",
            'timelineData' => $timelineData,
            'items'        => $items, // $items という名前で渡す
            'activeTab'    => $request->query('tab', 'pane-timeline')
        ]);
    }
}