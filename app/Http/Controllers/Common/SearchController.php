<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 検索機能の基底コントローラー
 */
abstract class SearchController extends Controller
{
    /**
     * 共通のインデックス表示ロジック
     */
    protected function renderIndex(array $data)
    {
        return view('common.search.index', array_merge([
            'pageId' => 'search',
        ], $data));
    }

    /**
     * 検索結果アイテム配列に is_keeping / is_liked を付与する。
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  string  $itemType  'shop' or 'cast'
     * @return array<int, array<string, mixed>>
     */
    protected function attachFavoriteStates(array $items, string $itemType): array
    {
        if ($items === []) {
            return $items;
        }

        $senderType = null;
        $myCastId = null;
        $myShopId = null;
        if (Auth::guard('shop')->check()) {
            $senderType = Favorite::SENDER_SHOP;
            $myShopId = (string) (Auth::guard('shop')->user()->shop_id ?? '');
        } elseif (Auth::guard('member')->check()) {
            $senderType = Favorite::SENDER_CAST;
            $myCastId = (string) (Auth::guard('member')->user()->id ?? '');
        } else {
            // 未ログインは全部 false で返す
            foreach ($items as &$it) {
                $it['is_keeping'] = false;
                $it['is_liked'] = false;
            }
            unset($it);
            return $items;
        }

        $ids = array_values(array_filter(array_map(fn ($it) => (string) ($it['id'] ?? ''), $items)));
        if ($ids === []) {
            foreach ($items as &$it) {
                $it['is_keeping'] = false;
                $it['is_liked'] = false;
            }
            unset($it);
            return $items;
        }

        $favoriteColumn = $itemType === 'shop' ? 'shop_id' : 'cast_id';

        // 自分のアクションの取得（個別 is_keeping / is_liked 用）
        $rows = DB::table('favorites')
            ->select('action_type', $favoriteColumn . ' as target_id')
            ->where('sender_type', $senderType)
            ->whereIn('action_type', [Favorite::ACTION_KEEP, Favorite::ACTION_LIKE])
            ->when($senderType === Favorite::SENDER_CAST && $myCastId !== '', fn ($q) => $q->where('cast_id', $myCastId))
            ->when($senderType === Favorite::SENDER_SHOP && $myShopId !== '', fn ($q) => $q->where('shop_id', $myShopId))
            ->whereIn($favoriteColumn, $ids)
            ->get();

        $keepSet = [];
        $likeSet = [];
        foreach ($rows as $r) {
            $targetId = (string) $r->target_id;
            if ($r->action_type === Favorite::ACTION_KEEP) {
                $keepSet[$targetId] = true;
            } elseif ($r->action_type === Favorite::ACTION_LIKE) {
                $likeSet[$targetId] = true;
            }
        }

        // 受け手側の合計 LIKE / KEEP 数（社会的証明）
        // itemType=cast の場合：cast_id 毎の受信 LIKE 数（sender_type=shop の LIKE）
        // itemType=shop の場合：shop_id 毎の受信 LIKE 数（sender_type=cast の LIKE）
        $oppositeSender = $itemType === 'cast' ? Favorite::SENDER_SHOP : Favorite::SENDER_CAST;
        $countRows = DB::table('favorites')
            ->select($favoriteColumn . ' as target_id', 'action_type', DB::raw('COUNT(*) as cnt'))
            ->whereIn('action_type', [Favorite::ACTION_KEEP, Favorite::ACTION_LIKE])
            ->where('sender_type', $oppositeSender)
            ->whereIn($favoriteColumn, $ids)
            ->groupBy('target_id', 'action_type')
            ->get();

        $likeCounts = [];
        $keepCounts = [];
        foreach ($countRows as $r) {
            $targetId = (string) $r->target_id;
            if ($r->action_type === Favorite::ACTION_LIKE) {
                $likeCounts[$targetId] = (int) $r->cnt;
            } elseif ($r->action_type === Favorite::ACTION_KEEP) {
                $keepCounts[$targetId] = (int) $r->cnt;
            }
        }

        foreach ($items as &$it) {
            $id = (string) ($it['id'] ?? '');
            $it['is_keeping'] = isset($keepSet[$id]);
            $it['is_liked'] = isset($likeSet[$id]);
            // 既存の like_count を上書きしない（home.js 側で別ソース提供しているケースに配慮）
            if (!isset($it['like_count'])) {
                $it['like_count'] = $likeCounts[$id] ?? 0;
            }
            if (!isset($it['keep_count'])) {
                $it['keep_count'] = $keepCounts[$id] ?? 0;
            }
        }
        unset($it);

        return $items;
    }

    /**
     * ひらがな/カタカナ、全角/半角、英字大小の揺れを吸収する。
     */
    protected function normalizeSearchText(?string $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $value = mb_convert_kana($value, 'asKV', 'UTF-8');
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace_callback('/[\x{30A1}-\x{30F6}]/u', function (array $matches) {
            return mb_chr(mb_ord($matches[0], 'UTF-8') - 0x60, 'UTF-8');
        }, $value) ?? $value;

        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }
}