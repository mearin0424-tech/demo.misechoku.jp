<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Common\SearchController as BaseSearchController;
use Illuminate\Http\Request;

class SearchController extends BaseSearchController
{
    public function index(Request $request, ?string $tab = 'timeline')
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

        // 店舗一覧（業種・エリア等を含む）
        $allItems = [
            ['id' => 1, 'shop_name' => 'CLUB ETERNITY', 'pref' => '東京都', 'city' => '港区', 'main_img' => asset('storage/mock/shops/out-1.png'), 'industries' => ['キャバクラ'], 'pref_label' => '東京都'],
            ['id' => 2, 'shop_name' => 'THE GOLDSTONE', 'pref' => '東京都', 'city' => '中央区', 'main_img' => asset('storage/mock/shops/out-2.png'), 'industries' => ['ラウンジ'], 'pref_label' => '東京都'],
            ['id' => 3, 'shop_name' => '六本木BAR', 'pref' => '東京都', 'city' => '港区', 'main_img' => asset('storage/mock/shops/out-1.png'), 'industries' => ['バー'], 'pref_label' => '東京都'],
        ];

        $items = $this->filterCastSearchItems($allItems, $request);

        $activeTab = 'pane-' . (in_array($tab, ['timeline', 'list', 'ai'], true) ? $tab : 'timeline');

        return $this->renderIndex([
            'guideMessage' => "あなたの希望に合うお店を探そう！\n条件を絞り込んで検索してみてね。",
            'timelineData' => $timelineData,
            'items'        => $items,
            'activeTab'    => $activeTab,
            'searchTab'    => $tab,
        ]);
    }

    /**
     * 求人検索：キーワード・業種などでフィルタ（モック用）
     */
    private function filterCastSearchItems(array $items, Request $request): array
    {
        $keyword = $request->query('keyword');
        $keyword = is_string($keyword) ? trim($keyword) : '';
        $industries = $request->query('industry', []);
        $industries = is_array($industries) ? $industries : (is_string($industries) ? [$industries] : []);

        return array_values(array_filter($items, function ($item) use ($keyword, $industries) {
            if ($keyword !== '') {
                $haystack = implode(' ', [
                    $item['shop_name'] ?? '',
                    $item['pref'] ?? '',
                    $item['city'] ?? '',
                    $item['pref_label'] ?? '',
                ]);
                if (mb_stripos($haystack, $keyword) === false) {
                    return false;
                }
            }
            if (!empty($industries)) {
                $itemIndustries = $item['industries'] ?? [];
                $match = false;
                foreach ($industries as $ind) {
                    if (in_array($ind, $itemIndustries, true)) {
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