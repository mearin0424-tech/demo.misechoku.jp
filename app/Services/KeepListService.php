<?php

namespace App\Services;

use App\Http\Concerns\ResolvesActor;
use App\Models\Favorite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * キープリスト（メッセージを送る前の保存リスト）の取得。
 * 旧 KEEPS（interaction）画面から SEARCH のキープタブへ移設。
 */
class KeepListService
{
    use ResolvesActor;

    /**
     * キャストがキープしたお店の一覧。
     *
     * @return array<int, array<string, mixed>>
     */
    public function keptShopsForCast(string $castId): array
    {
        if ($castId === '' || !Schema::hasTable('favorites')) {
            return [];
        }

        $rows = DB::table('favorites')
            ->join('shops', 'favorites.shop_id', '=', 'shops.id')
            ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->where('favorites.cast_id', $castId)
            ->where('favorites.action_type', Favorite::ACTION_KEEP)
            ->where('favorites.sender_type', Favorite::SENDER_CAST)
            ->orderByDesc('favorites.created_at')
            ->select(
                'shops.id',
                'shop_profiles.shop_name as name',
                'shop_profiles.pref',
                'shop_profiles.city',
                DB::raw("(SELECT si.image_path FROM shop_images si WHERE si.shop_id = shops.id ORDER BY si.is_main DESC, si.main_order IS NULL, si.main_order, si.id LIMIT 1) as main_image_path"),
                'favorites.created_at'
            )
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'id' => $row->id,
                'name' => $row->name ?: '店舗',
                'pref' => $row->pref ?? '',
                'city' => $row->city ?? '',
                'img' => $this->assetPathForStored($row->main_image_path ?? null),
                'updated_at' => $this->formatKeptAt($row->created_at),
            ];
        }

        return $items;
    }

    /**
     * 店舗がキープしたキャストの一覧。
     *
     * @return array<int, array<string, mixed>>
     */
    public function keptCastsForShop(string $shopId): array
    {
        if ($shopId === '' || !Schema::hasTable('favorites')) {
            return [];
        }

        $rows = DB::table('favorites')
            ->join('casts', 'favorites.cast_id', '=', 'casts.id')
            ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->where('favorites.shop_id', $shopId)
            ->where('favorites.action_type', Favorite::ACTION_KEEP)
            ->where('favorites.sender_type', Favorite::SENDER_SHOP)
            ->orderByDesc('favorites.created_at')
            ->select(
                'casts.id',
                'cast_profiles.nickname as name',
                'cast_profiles.birthday',
                'cast_profiles.pref',
                'cast_profiles.city',
                'cast_profiles.profession',
                DB::raw("(SELECT ci.image_path FROM cast_images ci WHERE ci.cast_id = casts.id ORDER BY ci.is_main DESC, ci.main_order IS NULL, ci.main_order, ci.id LIMIT 1) as main_image_path"),
                'favorites.created_at'
            )
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $age = $row->birthday ? \Carbon\Carbon::parse($row->birthday)->age : null;
            $items[] = [
                'id' => $row->id,
                'name' => $row->name ?: 'ゲスト',
                'age' => $age,
                'profession' => $row->profession ?? '',
                'pref' => $row->pref ?? '',
                'city' => $row->city ?? '',
                'img' => $this->assetPathForStored($row->main_image_path ?? null),
                'updated_at' => $this->formatKeptAt($row->created_at),
            ];
        }

        return $items;
    }

    private function formatKeptAt(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    // assetPathForStored() is now provided by ResolvesActor trait.
}
