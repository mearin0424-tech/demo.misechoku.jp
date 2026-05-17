<?php

namespace App\Services;

use App\Models\Favorite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 「条件が似ているお店／キャスト」レコメンド。
 *
 * - キャスト KEEP 画面下部の「おすすめお店」
 * - ショップ KEEP 画面下部の「おすすめキャスト」
 *
 * 絞り込みは AND、ただし該当条件が未設定なら **その条件は適用しない**。
 * KEEP 履歴のタグ集計でスコアを与え、第2優先で並び替える。
 */
class RecommendationService
{
    public function __construct(
        private readonly UserLocationService $location,
    ) {
    }

    /**
     * キャスト向け「おすすめお店」一覧を返す。
     *
     * @return array<int, array<string, mixed>>
     */
    public function recommendShopsForCast(string $castId, int $limit = 6): array
    {
        if ($castId === '' || !Schema::hasTable('shops') || !Schema::hasTable('shop_profiles') || !Schema::hasTable('shop_jobs')) {
            return [];
        }

        $prefs = $this->loadCastPreferences($castId);
        $keepShopIds = $this->keptShopIdsByCast($castId);

        $q = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->join('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->where(function ($q) {
                if (Schema::hasColumn('shop_jobs', 'regular_status')) {
                    $q->where('shop_jobs.regular_status', 1);
                } else {
                    $q->where('shop_jobs.status', 1);
                }
            });

        if (!empty($keepShopIds)) {
            $q->whereNotIn('shops.id', $keepShopIds);
        }

        // 業種で絞り込み（cast の希望業種）
        if (!empty($prefs['industry_ids'])) {
            $q->whereIn('shop_profiles.industry_id', $prefs['industry_ids']);
        }

        // 時給で絞り込み
        if (!empty($prefs['hourly_wage_min']) && Schema::hasColumn('shop_jobs', 'regular_hourly_wage')) {
            $q->where('shop_jobs.regular_hourly_wage', '>=', (int) $prefs['hourly_wage_min']);
        }

        $hourlyWageCol = Schema::hasColumn('shop_jobs', 'regular_hourly_wage') ? 'shop_jobs.regular_hourly_wage' : DB::raw('0');

        $select = [
            'shops.id',
            'shop_profiles.shop_name as name',
            'shop_profiles.pref',
            'shop_profiles.city',
            'shop_profiles.industry_id',
            'shop_profiles.latitude',
            'shop_profiles.longitude',
            DB::raw('(' . (Schema::hasColumn('shop_jobs', 'regular_hourly_wage') ? 'shop_jobs.regular_hourly_wage' : '0') . ') as hourly_wage'),
            DB::raw('GREATEST(COALESCE(shop_jobs.updated_at, 0), COALESCE(shop_profiles.updated_at, 0)) as last_updated_at'),
            DB::raw("(SELECT si.image_path FROM shop_images si WHERE si.shop_id = shops.id ORDER BY si.is_main DESC, si.main_order IS NULL, si.main_order, si.id LIMIT 1) as main_image_path"),
        ];

        $rows = $q->select($select)->limit(200)->get();
        if ($rows->isEmpty()) {
            return [];
        }

        // 距離フィルタ（候補取得後に PHP 側で適用、緯度経度未登録は除外しない）
        $origin = null;
        if (!empty($prefs['max_distance_km']) && $prefs['max_distance_km'] > 0) {
            $origin = $this->castOrigin($castId);
        }
        $maxDistanceKm = (int) ($prefs['max_distance_km'] ?? 0);

        // タグスコア用：cast が KEEP している店舗のタグ集計
        $tagCounts = $this->aggregateShopTagsByShopIds($keepShopIds);
        $candidateShopIds = $rows->pluck('id')->map(fn ($v) => (string) $v)->all();
        $candidateTags = $this->shopTagsByShopIds($candidateShopIds);

        $hourlyWageMin = (int) ($prefs['hourly_wage_min'] ?? 0);

        $items = [];
        foreach ($rows as $row) {
            $shopId = (string) $row->id;

            // 距離フィルタ
            if ($origin && $maxDistanceKm > 0) {
                $km = $this->location->distanceKm(
                    (float) $origin['lat'], (float) $origin['lng'],
                    $row->latitude !== null ? (float) $row->latitude : null,
                    $row->longitude !== null ? (float) $row->longitude : null
                );
                if ($km !== null && $km > $maxDistanceKm) {
                    continue;
                }
            }

            // タグスコア（候補店のタグのうち、KEEP 店舗で出現したタグの「店舗数」を合算）
            $tagScore = 0;
            foreach ($candidateTags[$shopId] ?? [] as $tagKey) {
                $tagScore += (int) ($tagCounts[$tagKey] ?? 0);
            }

            $hourlyWage = (int) ($row->hourly_wage ?? 0);
            $wageGap = $hourlyWageMin > 0 ? max(0, $hourlyWageMin - $hourlyWage) : 0;

            $items[] = [
                'id'              => $shopId,
                'name'            => (string) ($row->name ?: '店舗'),
                'pref'            => (string) ($row->pref ?? ''),
                'city'            => (string) ($row->city ?? ''),
                'hourly_wage'     => $hourlyWage,
                'image'           => $this->imageUrl((string) ($row->main_image_path ?? '')),
                '_wage_gap'       => $wageGap,
                '_tag_score'      => $tagScore,
                '_last_updated'   => (string) ($row->last_updated_at ?? ''),
            ];
        }

        // ソート: ① 時給差↑ ② タグスコア↓ ③ 更新日↓
        usort($items, function ($a, $b) {
            if ($a['_wage_gap'] !== $b['_wage_gap']) {
                return $a['_wage_gap'] <=> $b['_wage_gap'];
            }
            if ($a['_tag_score'] !== $b['_tag_score']) {
                return $b['_tag_score'] <=> $a['_tag_score'];
            }
            return strcmp((string) $b['_last_updated'], (string) $a['_last_updated']);
        });

        return array_slice($items, 0, $limit);
    }

    /**
     * ショップ向け「おすすめキャスト」一覧を返す。
     *
     * @return array<int, array<string, mixed>>
     */
    public function recommendCastsForShop(string $shopId, int $limit = 6): array
    {
        if ($shopId === '' || !Schema::hasTable('casts') || !Schema::hasTable('cast_profiles')) {
            return [];
        }

        $shopRow = DB::table('shop_profiles')
            ->where('shop_id', $shopId)
            ->select('industry_id', 'latitude', 'longitude')
            ->first();
        $shopIndustryId = $shopRow && $shopRow->industry_id !== null ? (int) $shopRow->industry_id : null;

        $prefs = $this->loadShopPreferences($shopId);
        $keepCastIds = $this->keptCastIdsByShop($shopId);

        $q = DB::table('casts')
            ->join('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id');

        if (!empty($keepCastIds)) {
            $q->whereNotIn('casts.id', $keepCastIds);
        }

        // 業種マッチ：cast の希望業種に shop の業種が含まれているか（JSON_CONTAINS）
        if ($shopIndustryId !== null && Schema::hasTable('cast_search_preferences')) {
            $q->join('cast_search_preferences', 'casts.id', '=', 'cast_search_preferences.cast_id')
              ->whereRaw('JSON_CONTAINS(cast_search_preferences.industry_ids, JSON_QUOTE(?))', [(string) $shopIndustryId])
              ->orWhereRaw('JSON_CONTAINS(cast_search_preferences.industry_ids, CAST(? AS JSON))', [$shopIndustryId]);
        }

        // 年齢範囲
        if (!empty($prefs['age_min'])) {
            $maxBirthday = now()->subYears((int) $prefs['age_min'])->toDateString();
            $q->where('cast_profiles.birthday', '<=', $maxBirthday);
        }
        if (!empty($prefs['age_max'])) {
            $minBirthday = now()->subYears((int) $prefs['age_max'] + 1)->toDateString();
            $q->where('cast_profiles.birthday', '>', $minBirthday);
        }

        // ナイトワーク経験
        if (!empty($prefs['night_work_exp']) && $prefs['night_work_exp'] !== 'any' && Schema::hasColumn('cast_profiles', 'exp')) {
            $q->where('cast_profiles.exp', $prefs['night_work_exp'] === 'yes' ? 1 : 0);
        }

        $rows = $q->select([
            'casts.id',
            'cast_profiles.nickname',
            'cast_profiles.name',
            'cast_profiles.birthday',
            'cast_profiles.pref',
            'cast_profiles.city',
            'cast_profiles.latitude',
            'cast_profiles.longitude',
            'cast_profiles.updated_at',
            DB::raw("(SELECT ci.image_path FROM cast_images ci WHERE ci.cast_id = casts.id AND ci.type = 1 ORDER BY ci.is_main DESC, ci.main_order IS NULL, ci.main_order, ci.id LIMIT 1) as main_image_path"),
        ])->limit(200)->get();

        if ($rows->isEmpty()) {
            return [];
        }

        // 距離フィルタ
        $maxDistanceKm = (int) ($prefs['max_distance_km'] ?? 0);
        $origin = ($maxDistanceKm > 0 && $shopRow && $shopRow->latitude !== null && $shopRow->longitude !== null)
            ? ['lat' => (float) $shopRow->latitude, 'lng' => (float) $shopRow->longitude]
            : null;

        // タグスコア：shop が KEEP している cast のタグ集計
        $tagCounts = $this->aggregateCastTagsByCastIds($keepCastIds);
        $candidateCastIds = $rows->pluck('id')->map(fn ($v) => (string) $v)->all();
        $candidateTags = $this->castTagsByCastIds($candidateCastIds);

        // 年齢の中央値（希望年齢の中心）
        $ageMid = null;
        if (!empty($prefs['age_min']) || !empty($prefs['age_max'])) {
            $a = (int) ($prefs['age_min'] ?? 0);
            $b = (int) ($prefs['age_max'] ?? 0);
            if ($a > 0 && $b > 0) $ageMid = ($a + $b) / 2;
            elseif ($a > 0) $ageMid = $a;
            elseif ($b > 0) $ageMid = $b;
        }

        $items = [];
        foreach ($rows as $row) {
            $castId = (string) $row->id;

            if ($origin) {
                $km = $this->location->distanceKm(
                    $origin['lat'], $origin['lng'],
                    $row->latitude !== null ? (float) $row->latitude : null,
                    $row->longitude !== null ? (float) $row->longitude : null
                );
                if ($km !== null && $km > $maxDistanceKm) {
                    continue;
                }
            }

            $age = null;
            if ($row->birthday) {
                try {
                    $age = \Carbon\Carbon::parse($row->birthday)->age;
                } catch (\Throwable $e) {
                    $age = null;
                }
            }

            $ageGap = ($ageMid !== null && $age !== null) ? abs($age - $ageMid) : 0;

            $tagScore = 0;
            foreach ($candidateTags[$castId] ?? [] as $tagKey) {
                $tagScore += (int) ($tagCounts[$tagKey] ?? 0);
            }

            $items[] = [
                'id'            => $castId,
                'name'          => (string) ($row->nickname ?: ($row->name ?: 'ゲスト')),
                'age'           => $age,
                'pref'          => (string) ($row->pref ?? ''),
                'city'          => (string) ($row->city ?? ''),
                'image'         => $this->imageUrl((string) ($row->main_image_path ?? '')),
                '_age_gap'      => $ageGap,
                '_tag_score'    => $tagScore,
                '_last_updated' => (string) ($row->updated_at ?? ''),
            ];
        }

        usort($items, function ($a, $b) {
            if ($a['_age_gap'] !== $b['_age_gap']) {
                return $a['_age_gap'] <=> $b['_age_gap'];
            }
            if ($a['_tag_score'] !== $b['_tag_score']) {
                return $b['_tag_score'] <=> $a['_tag_score'];
            }
            return strcmp((string) $b['_last_updated'], (string) $a['_last_updated']);
        });

        return array_slice($items, 0, $limit);
    }

    // ===== ロジック説明（UI のインフォ表示用） =====

    /** @return array<int, string> */
    public static function castRecommendLogicLines(): array
    {
        return [
            '✔ 公開中の求人のみを対象にしています。',
            '✔ すでに KEEP しているお店は除外しています。',
            '✔ 希望業種が登録されている場合、その業種の店舗だけを表示します。',
            '✔ 距離フィルタが設定されている場合、半径内の店舗だけを表示します。',
            '✔ 希望時給が登録されている場合、その金額以上の求人だけを表示します。',
            '🔢 並び順は ① 希望時給との差が小さい順 → ② あなたが KEEP しているお店のタグと一致する数が多い順 → ③ 求人の更新日が新しい順 です。',
            '※ 各条件は未設定のものはスキップします。',
        ];
    }

    /** @return array<int, string> */
    public static function shopRecommendLogicLines(): array
    {
        return [
            '✔ すでに KEEP しているキャストは除外しています。',
            '✔ 自店舗の業種を希望業種に登録しているキャストだけを表示します。',
            '✔ 距離フィルタが設定されている場合、半径内のキャストだけを表示します。',
            '✔ 希望年齢を登録している場合、その範囲のキャストだけを表示します。',
            '✔ ナイトワーク経験の希望を登録している場合、それに合うキャストだけを表示します。',
            '🔢 並び順は ① 希望年齢中央値との差が小さい順 → ② 貴店が KEEP しているキャストのタグと一致する数が多い順 → ③ プロフィール更新日が新しい順 です。',
            '※ 各条件は未設定のものはスキップします。',
        ];
    }

    // ===== private =====

    private function loadCastPreferences(string $castId): array
    {
        if (!Schema::hasTable('cast_search_preferences')) {
            return [];
        }
        $row = DB::table('cast_search_preferences')
            ->where('cast_id', $castId)
            ->first();
        if (!$row) return [];
        $industryIds = $this->decodeJsonArray($row->industry_ids ?? null);
        return [
            'industry_ids'    => array_map('intval', $industryIds),
            'max_distance_km' => isset($row->max_distance_km) ? (int) $row->max_distance_km : 0,
            'hourly_wage_min' => isset($row->hourly_wage_min) ? (int) $row->hourly_wage_min : 0,
            'work_periods'    => $this->decodeJsonArray($row->work_periods ?? null),
        ];
    }

    private function loadShopPreferences(string $shopId): array
    {
        if (!Schema::hasTable('shop_search_preferences')) {
            return [];
        }
        $row = DB::table('shop_search_preferences')
            ->where('shop_id', $shopId)
            ->first();
        if (!$row) return [];
        return [
            'max_distance_km' => isset($row->max_distance_km) ? (int) $row->max_distance_km : 0,
            'age_min'         => isset($row->age_min) ? (int) $row->age_min : 0,
            'age_max'         => isset($row->age_max) ? (int) $row->age_max : 0,
            'work_periods'    => $this->decodeJsonArray($row->work_periods ?? null),
            'looks_tag_ids'   => array_map('intval', $this->decodeJsonArray($row->looks_tag_ids ?? null)),
            'personality_tag_ids' => array_map('intval', $this->decodeJsonArray($row->personality_tag_ids ?? null)),
            'night_work_exp'  => $row->night_work_exp ?? null,
        ];
    }

    /** @return array<int, string> */
    private function keptShopIdsByCast(string $castId): array
    {
        if (!Schema::hasTable('favorites')) return [];
        return DB::table('favorites')
            ->where('cast_id', $castId)
            ->where('action_type', Favorite::ACTION_KEEP)
            ->where('sender_type', Favorite::SENDER_CAST)
            ->whereNotNull('shop_id')
            ->pluck('shop_id')->map(fn ($v) => (string) $v)->values()->all();
    }

    /** @return array<int, string> */
    private function keptCastIdsByShop(string $shopId): array
    {
        if (!Schema::hasTable('favorites')) return [];
        return DB::table('favorites')
            ->where('shop_id', $shopId)
            ->where('action_type', Favorite::ACTION_KEEP)
            ->where('sender_type', Favorite::SENDER_SHOP)
            ->whereNotNull('cast_id')
            ->pluck('cast_id')->map(fn ($v) => (string) $v)->values()->all();
    }

    /**
     * KEEP 中の店舗群のタグを集計。tag_type:tag_id をキーに、「いくつの店舗で出現したか」を返す。
     *
     * @param array<int, string> $shopIds
     * @return array<string, int>
     */
    private function aggregateShopTagsByShopIds(array $shopIds): array
    {
        if ($shopIds === [] || !Schema::hasTable('shop_tag_relations')) return [];
        $rows = DB::table('shop_tag_relations')
            ->whereIn('shop_id', $shopIds)
            ->get(['shop_id', 'tag_id', 'tag_type']);
        $counts = [];
        foreach ($rows as $row) {
            $key = ($row->tag_type ?? '') . ':' . ($row->tag_id ?? '');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }
        return $counts;
    }

    /**
     * 候補店舗のタグキー（tag_type:tag_id）配列。
     *
     * @param array<int, string> $shopIds
     * @return array<string, array<int, string>>  shop_id => [tagKey, ...]
     */
    private function shopTagsByShopIds(array $shopIds): array
    {
        if ($shopIds === [] || !Schema::hasTable('shop_tag_relations')) return [];
        $rows = DB::table('shop_tag_relations')
            ->whereIn('shop_id', $shopIds)
            ->get(['shop_id', 'tag_id', 'tag_type']);
        $out = [];
        foreach ($rows as $row) {
            $sid = (string) $row->shop_id;
            $out[$sid][] = ($row->tag_type ?? '') . ':' . ($row->tag_id ?? '');
        }
        return $out;
    }

    /**
     * @param array<int, string> $castIds
     * @return array<string, int>
     */
    private function aggregateCastTagsByCastIds(array $castIds): array
    {
        if ($castIds === [] || !Schema::hasTable('cast_tag_relations')) return [];
        $rows = DB::table('cast_tag_relations')
            ->whereIn('cast_id', $castIds)
            ->get(['cast_id', 'tag_id', 'tag_type']);
        $counts = [];
        foreach ($rows as $row) {
            $key = ($row->tag_type ?? '') . ':' . ($row->tag_id ?? '');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }
        return $counts;
    }

    /**
     * @param array<int, string> $castIds
     * @return array<string, array<int, string>>
     */
    private function castTagsByCastIds(array $castIds): array
    {
        if ($castIds === [] || !Schema::hasTable('cast_tag_relations')) return [];
        $rows = DB::table('cast_tag_relations')
            ->whereIn('cast_id', $castIds)
            ->get(['cast_id', 'tag_id', 'tag_type']);
        $out = [];
        foreach ($rows as $row) {
            $cid = (string) $row->cast_id;
            $out[$cid][] = ($row->tag_type ?? '') . ':' . ($row->tag_id ?? '');
        }
        return $out;
    }

    private function castOrigin(string $castId): ?array
    {
        $row = DB::table('cast_profiles')
            ->where('cast_id', $castId)
            ->select('latitude', 'longitude')
            ->first();
        if (!$row || $row->latitude === null || $row->longitude === null) return null;
        return ['lat' => (float) $row->latitude, 'lng' => (float) $row->longitude];
    }

    private function decodeJsonArray(?string $json): array
    {
        if ($json === null || $json === '') return [];
        $decoded = json_decode($json, true);
        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function imageUrl(string $path): string
    {
        if ($path === '') return asset('assets/images/common/no-image.png');
        if (str_starts_with($path, 'uploads/')) return asset($path);
        if (str_starts_with($path, 'public/')) return asset('storage/' . substr($path, 7));
        return asset(ltrim($path, '/'));
    }
}
