<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Common\SearchController as BaseSearchController;
use Illuminate\Http\Request;

class SearchController extends BaseSearchController
{
    public function index(Request $request, ?string $tab = 'timeline')
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

        // キャスト一覧（名前・タグで検索用）
        $allItems = [
            ['id' => 1, 'name' => 'みさき', 'age' => 23, 'img' => asset('storage/mock/casts/1-1.png'), 'tags' => ['モデル系', 'お酒強い']],
            ['id' => 2, 'name' => '愛華', 'age' => 21, 'img' => asset('storage/mock/casts/2-1.png'), 'tags' => ['癒やし系', '女子大生']],
            ['id' => 3, 'name' => 'Rena', 'age' => 25, 'img' => asset('storage/mock/casts/3-1.png'), 'tags' => ['フリーランス', 'ハーフ系']],
        ];

        $items = $this->filterShopSearchItems($allItems, $request);

        $activeTab = 'pane-' . (in_array($tab, ['timeline', 'list', 'ai'], true) ? $tab : 'timeline');

        return $this->renderIndex([
            'guideMessage' => "ここでは気になるキャストを検索できるよ！\nスワイプして探してみてね。",
            'timelineData' => $timelineData,
            'items'        => $items,
            'activeTab'    => $activeTab,
            'searchTab'    => $tab,
        ]);
    }

    /**
     * 一覧・検索：キーワード・業種などでフィルタ（モック用）
     */
    private function filterShopSearchItems(array $items, Request $request): array
    {
        $keyword = $request->query('keyword');
        $keyword = is_string($keyword) ? trim($keyword) : '';
        $industries = $request->query('industry', []);
        $industries = is_array($industries) ? $industries : (is_string($industries) ? [$industries] : []);

        return array_values(array_filter($items, function ($item) use ($keyword, $industries) {
            if ($keyword !== '') {
                $haystack = ($item['name'] ?? '') . ' ' . implode(' ', $item['tags'] ?? []);
                if (mb_stripos($haystack, $keyword) === false) {
                    return false;
                }
            }
            if (!empty($industries)) {
                $itemTags = $item['tags'] ?? [];
                $match = false;
                foreach ($industries as $ind) {
                    if (in_array($ind, $itemTags, true)) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) {
                    return false;
                }
            }
            return true;
        }));
    }
}