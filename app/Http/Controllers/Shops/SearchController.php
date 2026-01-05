<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Common\SearchController as BaseSearchController;
use Illuminate\Http\Request;

class SearchController extends BaseSearchController
{
    public function index(Request $request)
    {
        // タイムラインデータ（キャストの投稿）
        $timelineData = [
            [
                'name' => '美咲',
                'img' => asset('storage/mock/casts/1-1.png'),
                'time' => '10分前',
                'text' => "今から六本木エリアで働けます！\nお誘い待ってます✨",
                'tags' => ['モデル系', 'お酒強い']
            ],
            [
                'name' => '愛華',
                'img' => asset('storage/mock/casts/2-1.png'),
                'time' => '30分前',
                'text' => "今日から新しく登録しました！\nよろしくお願いします♪",
                'tags' => ['癒やし系', '聞き上手']
            ]
        ];

        // キャスト一覧（$itemsに改名）
        $items = [
            ['id' => 1, 'name' => 'みさき', 'age' => 23, 'img' => asset('storage/mock/casts/1-1.png'), 'tags' => ['モデル系', 'お酒強い']],
            ['id' => 2, 'name' => '愛華', 'age' => 21, 'img' => asset('storage/mock/casts/2-1.png'), 'tags' => ['癒やし系', '女子大生']],
            ['id' => 3, 'name' => 'Rena', 'age' => 25, 'img' => asset('storage/mock/casts/3-1.png'), 'tags' => ['フリーランス', 'ハーフ系']],
        ];

        return $this->renderIndex([
            'guideMessage' => "ここでは気になるキャストを検索できるよ！\nスワイプして探してみてね。",
            'timelineData' => $timelineData,
            'items'        => $items, // $items という名前で渡す
            'activeTab'    => $request->query('tab', 'pane-timeline')
        ]);
    }
}