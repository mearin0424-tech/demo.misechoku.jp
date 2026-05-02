<?php
// prj/app/Http/Controllers/Shops/HomeController.php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $this->cleanupStaleImageReferences();

        $isCastPortal = request()->is('cast/*');

        if ($isCastPortal) {
            $recruits = $this->getHomeRecruits();
            return view('shops.home.index', [
                'pageId' => 'home',
                'items' => $recruits,
                'itemType' => 'recruit',
            ]);
        }

        $casts = $this->getHomeCasts();
        return view('shops.home.index', [
            'pageId' => 'home',
            'items' => $casts,
            'itemType' => 'cast',
        ]);
    }

    private function getHomeCasts(): array
    {
        $rows = DB::table('casts')
            ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->select(
                'casts.id',
                'cast_profiles.nickname',
                'cast_profiles.name',
                'cast_profiles.birthday',
                'cast_profiles.pref',
                'cast_profiles.city',
                'cast_profiles.pr',
                'cast_profiles.main_image_path'
            )
            ->orderBy('casts.id')
            ->limit(20)
            ->get();

        // LIKE数は favorites テーブルから集計（存在しない環境でも動くようにガード）
        $likeCounts = [];
        if (Schema::hasTable('favorites')) {
            $likeRows = DB::table('favorites')
                ->select('cast_id', DB::raw('COUNT(*) as cnt'))
                ->whereNotNull('cast_id')
                ->where('action_type', 3)
                ->groupBy('cast_id')
                ->get();
            foreach ($likeRows as $lr) {
                if ($lr->cast_id !== null) {
                    $likeCounts[$lr->cast_id] = (int) $lr->cnt;
                }
            }
        }

        $items = [];
        foreach ($rows as $row) {
            $birthday = $row->birthday ? Carbon::parse($row->birthday) : null;
            $images = $this->getCastImages($row->id, $row->main_image_path);
            $items[] = [
                'id' => $row->id,
                'name' => $row->nickname ?: ($row->name ?: 'ゲスト'),
                'age' => $birthday ? $birthday->age : null,
                'tags' => $this->buildCastTags($row),
                'like_count' => $likeCounts[$row->id] ?? 0,
                'images' => $images,
            ];
        }

        if (!empty($items)) {
            return $items;
        }

        return [
            ['id' => 1, 'name' => 'みさき', 'age' => 23, 'tags' => ['モデル系', 'お酒強い'], 'like_count' => 12, 'images' => [asset('storage/mock/casts/1-1.png'), asset('storage/mock/casts/1-2.png'), asset('storage/mock/casts/1-3.png')]],
            ['id' => 2, 'name' => '愛華', 'age' => 21, 'tags' => ['癒やし系', '聞き上手'], 'like_count' => 8, 'images' => [asset('storage/mock/casts/2-1.png'), asset('storage/mock/casts/2-2.png'), asset('storage/mock/casts/2-3.png')]],
            ['id' => 3, 'name' => 'さくら', 'age' => 25, 'tags' => ['元気系', 'トーク上手'], 'like_count' => 24, 'images' => [asset('storage/mock/casts/3-1.png')]],
            ['id' => 4, 'name' => 'ナナ', 'age' => 22, 'tags' => ['清楚系', 'お酒弱い'], 'like_count' => 5, 'images' => [asset('storage/mock/casts/4-1.png')]],
        ];
    }

    /**
     * キャスト向けホーム：求人票ベースの一覧（ボーナス金・時給など重要情報を表示するため）
     */
    private function getHomeRecruits(): array
    {
        $useJobType = Schema::hasColumn('shop_jobs', 'job_type');

        $q = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->join('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->where('shop_jobs.status', 1);
        if ($useJobType) {
            $q->where('shop_jobs.job_type', 1);
        }

        $rows = $q->select(
            'shops.id',
            'shop_profiles.shop_name',
            'shop_profiles.pref',
            'shop_profiles.city',
            'shop_profiles.main_image_path',
            'shop_jobs.hourly_wage_regular',
            'shop_jobs.has_trial',
            'shop_jobs.has_help',
            'shop_jobs.trial_hourly_wage',
            'shop_jobs.help_hourly_wage',
            'shop_jobs.noruma_reward',
            'shop_jobs.noruma_cond'
        )
            ->orderBy('shops.id')
            ->limit(20)
            ->get();

        $shopIds = $rows->pluck('id')->unique()->values()->all();
        $trialByShop = collect();
        $helpByShop = collect();
        if ($useJobType && $shopIds !== []) {
            $trialByShop = DB::table('shop_jobs')
                ->whereIn('shop_id', $shopIds)
                ->where('job_type', 2)
                ->get()
                ->keyBy('shop_id');
            $helpByShop = DB::table('shop_jobs')
                ->whereIn('shop_id', $shopIds)
                ->where('job_type', 3)
                ->get()
                ->keyBy('shop_id');
        }

        // 店舗側求人カードの LIKE数（cast -> shop のいいね数）
        $likeCounts = [];
        if (Schema::hasTable('favorites')) {
            $likeRows = DB::table('favorites')
                ->select('shop_id', DB::raw('COUNT(*) as cnt'))
                ->whereNotNull('shop_id')
                ->where('action_type', 3)
                ->groupBy('shop_id')
                ->get();
            foreach ($likeRows as $lr) {
                if ($lr->shop_id !== null) {
                    $likeCounts[$lr->shop_id] = (int) $lr->cnt;
                }
            }
        }

        $items = [];
        foreach ($rows as $row) {
            // 画面からは「DBの店舗ID（例: s00000001）」でアクセスできるようにする
            $numericId = $this->toNumericShopId($row->id);
            $images = $this->getShopImages($row->id, $row->main_image_path);
            $meta = $this->decodeRecruitMeta($row->noruma_cond ?? null);

            $trialRow = $useJobType ? ($trialByShop[$row->id] ?? null) : null;
            $helpRow = $useJobType ? ($helpByShop[$row->id] ?? null) : null;

            $trialHourly = $trialRow && !empty($trialRow->status) && !empty($trialRow->trial_hourly_wage)
                ? (int) $trialRow->trial_hourly_wage
                : (!empty($row->has_trial) && isset($row->trial_hourly_wage) ? (int) $row->trial_hourly_wage : null);

            $helpHourly = $helpRow && !empty($helpRow->status) && !empty($helpRow->help_hourly_wage)
                ? (int) $helpRow->help_hourly_wage
                : (!empty($row->has_help) && isset($row->help_hourly_wage) ? (int) $row->help_hourly_wage : null);

            $mainBonus = isset($row->noruma_reward) ? (int) $row->noruma_reward : 0;
            $bonusTrial = ($trialRow && isset($trialRow->noruma_reward) && (int) $trialRow->noruma_reward > 0)
                ? (int) $trialRow->noruma_reward
                : $mainBonus;
            $bonusHelp = ($helpRow && isset($helpRow->noruma_reward) && (int) $helpRow->noruma_reward > 0)
                ? (int) $helpRow->noruma_reward
                : $mainBonus;

            $offerFulltime = isset($row->hourly_wage_regular) && (int) $row->hourly_wage_regular > 0;
            $offerTrial = $trialHourly !== null && $trialHourly > 0;
            $offerHelp = $helpHourly !== null && $helpHourly > 0;

            $items[] = [
                // ルート用には文字列ID（例: s00000001）をそのまま渡す
                'id' => $row->id,
                // 必要に応じて数値IDを併用したい場合に備えて保持
                'numeric_id' => $numericId,
                'name' => $row->shop_name ?: '店舗',
                'images' => $images,
                'hourly_wage_regular' => isset($row->hourly_wage_regular) ? (int) $row->hourly_wage_regular : 0,
                'trial_hourly_wage' => $trialHourly,
                'noruma_reward' => $mainBonus,
                'bonus_condition' => $meta['bonus_condition'] ?? '',
                'catch_copy' => $meta['catch_copy'] ?? '',
                'tags' => $this->buildRecruitCardTags($row, $meta),
                'pref' => $row->pref ?? '',
                'city' => $row->city ?? '',
                'like_count' => $likeCounts[$row->id] ?? 0,
                'recruit_bonus_lines' => [
                    ['label' => '体入', 'amount' => $bonusTrial, 'offered' => $offerTrial],
                    ['label' => 'ヘルプ', 'amount' => $bonusHelp, 'offered' => $offerHelp],
                    ['label' => '本入', 'amount' => $mainBonus, 'offered' => $offerFulltime],
                ],
            ];
        }

        if (!empty($items)) {
            return $items;
        }

        return [
            [
                'id' => 1,
                'name' => 'CLUB ETERNITY',
                'images' => [asset('storage/mock/shops/out-1.png')],
                'hourly_wage_regular' => 3500,
                'trial_hourly_wage' => 3000,
                'noruma_reward' => 50000,
                'bonus_condition' => '',
                'catch_copy' => '未経験歓迎',
                'tags' => ['高時給', 'ボーナスあり', '六本木'],
                'pref' => '東京都',
                'city' => '港区',
                'like_count' => 0,
                'recruit_bonus_lines' => [
                    ['label' => '体入', 'amount' => 50000, 'offered' => true],
                    ['label' => 'ヘルプ', 'amount' => 50000, 'offered' => true],
                    ['label' => '本入', 'amount' => 50000, 'offered' => true],
                ],
            ],
            [
                'id' => 2,
                'name' => 'THE GOLDSTONE',
                'images' => [asset('storage/mock/shops/out-2.png')],
                'hourly_wage_regular' => 3200,
                'trial_hourly_wage' => null,
                'noruma_reward' => 0,
                'bonus_condition' => '',
                'catch_copy' => 'ノルマなし',
                'tags' => ['送りあり', '六本木'],
                'pref' => '東京都',
                'city' => '港区',
                'like_count' => 0,
                'recruit_bonus_lines' => [
                    ['label' => '体入', 'amount' => 0, 'offered' => false],
                    ['label' => 'ヘルプ', 'amount' => 0, 'offered' => false],
                    ['label' => '本入', 'amount' => 0, 'offered' => true],
                ],
            ],
        ];
    }

    private function toNumericShopId(string $shopId): int
    {
        if (!str_starts_with($shopId, 's')) {
            return is_numeric($shopId) ? (int) $shopId : 0;
        }

        return (int) ltrim(substr($shopId, 1), '0') ?: 0;
    }

    private function decodeRecruitMeta(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function buildRecruitCardTags(object $row, array $meta): array
    {
        $tags = [];
        if (!empty($row->pref)) {
            $tags[] = $row->pref;
        }
        if (!empty($row->city)) {
            $tags[] = $row->city;
        }
        if (isset($row->hourly_wage_regular) && (int) $row->hourly_wage_regular >= 3000) {
            $tags[] = '高時給';
        }
        if (isset($row->noruma_reward) && (int) $row->noruma_reward > 0) {
            $tags[] = 'ボーナスあり';
        }
        $catch = $meta['catch_copy'] ?? '';
        if ($catch !== '') {
            $tags[] = mb_strimwidth(trim($catch), 0, 12, '…');
        }

        return array_slice(array_unique($tags), 0, 5);
    }

    private function getHomeShops(): array
    {
        $latestPostSub = DB::table('shop_posts')
            ->select('shop_id', DB::raw('MAX(id) as latest_id'))
            ->where('type', 2)
            ->groupBy('shop_id');

        $rows = DB::table('shops')
            ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoinSub($latestPostSub, 'sp_latest', 'shops.id', '=', 'sp_latest.shop_id')
            ->leftJoin('shop_posts', 'shop_posts.id', '=', 'sp_latest.latest_id')
            ->select(
                'shops.id',
                'shop_profiles.shop_name',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.main_image_path',
                'shop_posts.body as shop_post_body'
            )
            ->orderBy('shops.id')
            ->limit(20)
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $images = $this->getShopImages($row->id, $row->main_image_path);
            $items[] = [
                'id' => $row->id,
                'name' => $row->shop_name ?: '店舗',
                'age' => null,
                'tags' => $this->buildShopTags($row),
                'like_count' => 0,
                'rating' => 0,
                'images' => $images,
            ];
        }

        if (!empty($items)) {
            return $items;
        }

        return [
            ['id' => 1, 'name' => 'CLUB ETERNITY', 'age' => null, 'tags' => ['高時給', '即日払い'], 'like_count' => 8, 'rating' => 4.5, 'images' => [asset('storage/mock/shops/out-1.png')]],
            ['id' => 2, 'name' => 'THE GOLDSTONE', 'age' => null, 'tags' => ['ノルマなし', '送りあり'], 'like_count' => 12, 'rating' => 4.8, 'images' => [asset('storage/mock/shops/out-2.png')]],
            ['id' => 3, 'name' => 'Club Luxurious', 'age' => null, 'tags' => ['六本木', '高級'], 'like_count' => 5, 'rating' => 4.2, 'images' => [asset('storage/mock/shops/out-1.png')]],
            ['id' => 4, 'name' => 'BAR STELLA', 'age' => null, 'tags' => ['落ち着いた', 'カジュアル'], 'like_count' => 3, 'rating' => 4.0, 'images' => [asset('storage/mock/shops/out-2.png')]],
        ];
    }

    private function getCastImages(string $castId, ?string $mainImagePath): array
    {
        $images = DB::table('cast_images')
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->orderByRaw('is_main DESC')
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->pluck('image_path')
            ->map(fn ($path) => $this->assetPathForStored($path))
            ->filter()
            ->values()
            ->all();

        if (empty($images)) {
            $images[] = asset('assets/images/common/no-image.png');
        }

        return $images;
    }

    private function getShopImages(string $shopId, ?string $mainImagePath): array
    {
        $images = DB::table('shop_images')
            ->where('shop_id', $shopId)
            ->orderByRaw('is_main DESC')
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->pluck('image_path')
            ->map(fn ($path) => $this->assetPathForStored($path))
            ->filter()
            ->values()
            ->all();

        if (empty($images)) {
            $images[] = asset('assets/images/common/no-image.png');
        }

        return $images;
    }

    private function buildCastTags(object $row): array
    {
        $tags = [];
        if (!empty($row->pref)) {
            $tags[] = $row->pref;
        }
        if (!empty($row->city)) {
            $tags[] = $row->city;
        }
        if (!empty($row->pr)) {
            $tags[] = mb_strimwidth(trim((string) $row->pr), 0, 16, '…');
        }

        return !empty($tags) ? array_slice($tags, 0, 3) : ['プロフィール登録中'];
    }

    private function buildShopTags(object $row): array
    {
        $tags = [];
        if (!empty($row->pref)) {
            $tags[] = $row->pref;
        }
        if (!empty($row->city)) {
            $tags[] = $row->city;
        }
        $hitokoto = $row->shop_post_body ?? null;
        if (!empty($hitokoto)) {
            $tags[] = mb_strimwidth(trim((string) $hitokoto), 0, 18, '…');
        }

        return !empty($tags) ? array_slice($tags, 0, 3) : ['店舗情報登録中'];
    }

    private function assetPathForStored(?string $path): string
    {
        if (empty($path)) {
            return asset('assets/images/common/no-image.png');
        }
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }
        if (str_starts_with($path, 'public/')) {
            return asset('storage/' . substr($path, 7));
        }
        return asset(ltrim($path, '/'));
    }

    private function cleanupStaleImageReferences(): void
    {
        DB::table('cast_profiles')
            ->whereNotNull('main_image_path')
            ->get(['cast_id'])
            ->each(function ($row) {
                $hasImages = DB::table('cast_images')
                    ->where('cast_id', $row->cast_id)
                    ->where('type', 1)
                    ->exists();

                if (!$hasImages) {
                    DB::table('cast_profiles')
                        ->where('cast_id', $row->cast_id)
                        ->update([
                            'main_image_path' => null,
                            'updated_at' => now(),
                        ]);
                }
            });

        DB::table('shop_profiles')
            ->whereNotNull('main_image_path')
            ->get(['shop_id'])
            ->each(function ($row) {
                $hasImages = DB::table('shop_images')
                    ->where('shop_id', $row->shop_id)
                    ->exists();

                if (!$hasImages) {
                    DB::table('shop_profiles')
                        ->where('shop_id', $row->shop_id)
                        ->update([
                            'main_image_path' => null,
                            'updated_at' => now(),
                        ]);
                }
            });
    }
}