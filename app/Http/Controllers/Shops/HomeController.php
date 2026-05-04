<?php
// prj/app/Http/Controllers/Shops/HomeController.php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use App\Support\RecruitCatchOverlay;
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
        $horizontal = Schema::hasTable('shop_jobs') && Schema::hasColumn('shop_jobs', 'regular_status');
        $useJobType = Schema::hasColumn('shop_jobs', 'job_type') && !$horizontal;
        $hasReviews = Schema::hasTable('reviews');

        $q = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->join('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->leftJoin('industries', 'industries.id', '=', 'shop_profiles.industry_id');

        if ($horizontal) {
            $q->where('shop_jobs.regular_status', 1);
        } else {
            $q->where('shop_jobs.status', 1);
            if ($useJobType) {
                $q->where('shop_jobs.job_type', 1);
            }
        }

        if ($hasReviews) {
            $q->leftJoin(
                DB::raw('(SELECT shop_id, ROUND(AVG(eva), 1) AS avg_rating, COUNT(*) AS review_count FROM reviews GROUP BY shop_id) AS shop_reviews'),
                'shop_reviews.shop_id', '=', 'shops.id'
            );
        }

        $selectFields = [
            'shops.id',
            'shop_jobs.id as shop_job_id',
            'shop_profiles.shop_name',
            'shop_profiles.pref',
            'shop_profiles.city',
            'shop_profiles.main_image_path',
            'industries.name as industry_name',
        ];
        if (Schema::hasColumn('shop_jobs', 'hourly_wage_regular')) {
            $selectFields[] = 'shop_jobs.hourly_wage_regular';
        }
        if (Schema::hasColumn('shop_jobs', 'regular_hourly_wage')) {
            $selectFields[] = 'shop_jobs.regular_hourly_wage';
        }
        if (Schema::hasColumn('shop_jobs', 'has_trial')) {
            $selectFields[] = 'shop_jobs.has_trial';
        }
        if (Schema::hasColumn('shop_jobs', 'has_help')) {
            $selectFields[] = 'shop_jobs.has_help';
        }
        if (Schema::hasColumn('shop_jobs', 'trial_hourly_wage')) {
            $selectFields[] = 'shop_jobs.trial_hourly_wage';
        }
        if (Schema::hasColumn('shop_jobs', 'help_hourly_wage')) {
            $selectFields[] = 'shop_jobs.help_hourly_wage';
        }
        if (Schema::hasColumn('shop_jobs', 'trial_status')) {
            $selectFields[] = 'shop_jobs.trial_status';
        }
        if (Schema::hasColumn('shop_jobs', 'help_status')) {
            $selectFields[] = 'shop_jobs.help_status';
        }
        if (Schema::hasColumn('shop_jobs', 'noruma_reward')) {
            $selectFields[] = 'shop_jobs.noruma_reward';
        }
        if (Schema::hasColumn('shop_jobs', 'bonus_reward')) {
            $selectFields[] = 'shop_jobs.bonus_reward';
        }
        if (Schema::hasColumn('shop_jobs', 'noruma_cond')) {
            $selectFields[] = 'shop_jobs.noruma_cond';
        }
        if (Schema::hasColumn('shop_jobs', 'catch_copy')) {
            $selectFields[] = 'shop_jobs.catch_copy';
        }
        if (Schema::hasColumn('shop_jobs', 'bonus_condition')) {
            $selectFields[] = 'shop_jobs.bonus_condition';
        }
        if ($hasReviews) {
            $selectFields[] = DB::raw('COALESCE(shop_reviews.avg_rating, 0) AS avg_rating');
            $selectFields[] = DB::raw('COALESCE(shop_reviews.review_count, 0) AS review_count');
        }
        if (Schema::hasColumn('shop_jobs', 'pr')) {
            $selectFields[] = 'shop_jobs.pr';
        }

        $rows = $q->select($selectFields)
            ->orderBy('shops.id')
            ->limit(20)
            ->get();

        $shopIds = $rows->pluck('id')->unique()->values()->all();
        $trialByShop = collect();
        $helpByShop = collect();

        // 優良店バッヂ：過去3ヶ月の請求がすべて確認済みかつ10日以内に入金された店舗
        $premiumShopIds = [];
        if ($shopIds !== [] && Schema::hasTable('application_deposits') && Schema::hasTable('shop_job_applications')) {
            $threeMonthsAgo = now()->subMonths(3);
            $disqualified = DB::table('application_deposits')
                ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
                ->join('shop_jobs as sj_badge', 'shop_job_applications.shop_job_id', '=', 'sj_badge.id')
                ->whereIn('sj_badge.shop_id', $shopIds)
                ->where('application_deposits.created_at', '>=', $threeMonthsAgo)
                ->where('application_deposits.status', '<', 5)
                ->pluck('sj_badge.shop_id')
                ->unique()
                ->flip()
                ->all();
            $confirmed = DB::table('application_deposits')
                ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
                ->join('shop_jobs as sj_badge', 'shop_job_applications.shop_job_id', '=', 'sj_badge.id')
                ->whereIn('sj_badge.shop_id', $shopIds)
                ->where('application_deposits.created_at', '>=', $threeMonthsAgo)
                ->where('application_deposits.status', '>=', 5)
                ->pluck('sj_badge.shop_id')
                ->unique()
                ->flip()
                ->all();
            foreach ($confirmed as $sid => $v) {
                if (!isset($disqualified[$sid])) {
                    $premiumShopIds[$sid] = true;
                }
            }
        }

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
            $norumaRaw = Schema::hasColumn('shop_jobs', 'noruma_cond') ? ($row->noruma_cond ?? null) : null;
            $meta = $this->decodeRecruitMeta($norumaRaw);
            if (Schema::hasColumn('shop_jobs', 'catch_copy') && trim((string) ($row->catch_copy ?? '')) !== '') {
                $meta['catch_copy'] = trim((string) $row->catch_copy);
            }
            if (Schema::hasColumn('shop_jobs', 'bonus_condition') && trim((string) ($row->bonus_condition ?? '')) !== '') {
                $meta['bonus_condition'] = trim((string) $row->bonus_condition);
            }

            $trialRow = $useJobType ? ($trialByShop[$row->id] ?? null) : null;
            $helpRow = $useJobType ? ($helpByShop[$row->id] ?? null) : null;

            if ($horizontal) {
                // 店舗プレビューと同様：trial_status / help_status が 0 でも登録時給は表示（スワイプ用）
                $trialHourly = null;
                if (isset($row->trial_hourly_wage) && $row->trial_hourly_wage !== null && $row->trial_hourly_wage !== '' && (int) $row->trial_hourly_wage > 0) {
                    $trialHourly = (int) $row->trial_hourly_wage;
                }
                $helpHourly = null;
                if (isset($row->help_hourly_wage) && $row->help_hourly_wage !== null && $row->help_hourly_wage !== '' && (int) $row->help_hourly_wage > 0) {
                    $helpHourly = (int) $row->help_hourly_wage;
                }
            } else {
                $trialHourly = $trialRow && !empty($trialRow->status) && !empty($trialRow->trial_hourly_wage)
                    ? (int) $trialRow->trial_hourly_wage
                    : (!empty($row->has_trial) && isset($row->trial_hourly_wage) ? (int) $row->trial_hourly_wage : null);

                $helpHourly = $helpRow && !empty($helpRow->status) && !empty($helpRow->help_hourly_wage)
                    ? (int) $helpRow->help_hourly_wage
                    : (!empty($row->has_help) && isset($row->help_hourly_wage) ? (int) $row->help_hourly_wage : null);
            }

            $mainBonus = 0;
            if (isset($row->bonus_reward) && $row->bonus_reward !== null && $row->bonus_reward !== '') {
                $mainBonus = (int) $row->bonus_reward;
            } elseif (isset($row->noruma_reward) && $row->noruma_reward !== null && $row->noruma_reward !== '') {
                $mainBonus = (int) $row->noruma_reward;
            }

            if ($horizontal) {
                $bonusTrial = $mainBonus;
                $bonusHelp = $mainBonus;
            } else {
                $bonusTrial = ($trialRow && isset($trialRow->noruma_reward) && (int) $trialRow->noruma_reward > 0)
                    ? (int) $trialRow->noruma_reward
                    : $mainBonus;
                $bonusHelp = ($helpRow && isset($helpRow->noruma_reward) && (int) $helpRow->noruma_reward > 0)
                    ? (int) $helpRow->noruma_reward
                    : $mainBonus;
            }

            $regularWage = 0;
            if (isset($row->regular_hourly_wage) && $row->regular_hourly_wage !== null && $row->regular_hourly_wage !== '') {
                $regularWage = (int) $row->regular_hourly_wage;
            } elseif (isset($row->hourly_wage_regular) && $row->hourly_wage_regular !== null && $row->hourly_wage_regular !== '') {
                $regularWage = (int) $row->hourly_wage_regular;
            }

            $offerFulltime = $regularWage > 0;
            $offerTrial = $trialHourly !== null && $trialHourly > 0;
            $offerHelp = $helpHourly !== null && $helpHourly > 0;

            $shopJobId = isset($row->shop_job_id) ? (int) $row->shop_job_id : 0;
            $managerOverlay = $this->buildManagerImageOverlay($meta, $mainBonus);

            $bonusLines = [
                ['label' => '体入', 'amount' => $bonusTrial, 'offered' => $offerTrial],
                ['label' => 'ヘルプ', 'amount' => $bonusHelp, 'offered' => $offerHelp],
                ['label' => '本入', 'amount' => $mainBonus, 'offered' => $offerFulltime],
            ];

            $items[] = [
                // ルート用には文字列ID（例: s00000001）をそのまま渡す
                'id' => $row->id,
                // 必要に応じて数値IDを併用したい場合に備えて保持
                'numeric_id' => $numericId,
                'shop_job_id' => $shopJobId,
                'name' => $row->shop_name ?: '店舗',
                'images' => $images,
                'hourly_wage_regular' => $regularWage,
                'trial_hourly_wage' => $trialHourly,
                'help_hourly_wage' => $helpHourly,
                'noruma_reward' => $mainBonus,
                'bonus_condition' => $meta['bonus_condition'] ?? '',
                'catch_copy' => $meta['catch_copy'] ?? '',
                'tags' => $this->buildHomeRecruitDiscoveryTags($row->id, $shopJobId),
                'pref' => $row->pref ?? '',
                'city' => $row->city ?? '',
                'like_count' => $likeCounts[$row->id] ?? 0,
                'industry_name' => $row->industry_name ?? null,
                'rating' => $hasReviews ? (float) ($row->avg_rating ?? 0) : 0.0,
                'review_count' => $hasReviews ? (int) ($row->review_count ?? 0) : 0,
                'is_premium' => isset($premiumShopIds[$row->id]),
                'recruit_bonus_lines' => $bonusLines,
                'signup_bonus_range' => $this->discoverySignupBonusRange($bonusLines),
                'trial_hourly_range' => $this->discoveryHourlyPair($trialHourly, $meta, 'trial'),
                'help_hourly_range' => $this->discoveryHourlyPair($helpHourly, $meta, 'help'),
                'manager_overlay' => $managerOverlay,
            ];
        }

        if (!empty($items)) {
            return $items;
        }

        $mockRows = [
            [
                'id' => 1,
                'numeric_id' => 1,
                'shop_job_id' => 0,
                'name' => 'CLUB ETERNITY',
                'images' => [asset('storage/mock/shops/out-1.png')],
                'hourly_wage_regular' => 3500,
                'trial_hourly_wage' => 3000,
                'help_hourly_wage' => 2800,
                'noruma_reward' => 50000,
                'bonus_condition' => '💰 全額日払い / 帰りに3万円保証',
                'catch_copy' => "ノルマ・罰金**一切なし**！\n未経験だけのゆるふわ店 🎀",
                'tags' => ['高時給', 'ボーナスあり', '六本木'],
                'pref' => '東京都',
                'city' => '港区',
                'like_count' => 0,
                'industry_name' => 'キャバクラ',
                'rating' => 4.5,
                'review_count' => 12,
                'is_premium' => true,
                'recruit_bonus_lines' => [
                    ['label' => '体入', 'amount' => 35000, 'offered' => true],
                    ['label' => 'ヘルプ', 'amount' => 50000, 'offered' => true],
                    ['label' => '本入', 'amount' => 200000, 'offered' => true],
                ],
                'trial_hourly_min' => 4500,
                'trial_hourly_max' => 5000,
                'help_hourly_min' => 4000,
                'help_hourly_max' => 4200,
            ],
            [
                'id' => 2,
                'numeric_id' => 2,
                'shop_job_id' => 0,
                'name' => 'THE GOLDSTONE',
                'images' => [asset('storage/mock/shops/out-2.png')],
                'hourly_wage_regular' => 3200,
                'trial_hourly_wage' => null,
                'help_hourly_wage' => null,
                'noruma_reward' => 0,
                'bonus_condition' => '',
                'catch_copy' => 'ノルマなし',
                'tags' => ['送りあり', '六本木'],
                'pref' => '東京都',
                'city' => '港区',
                'like_count' => 0,
                'industry_name' => 'クラブ',
                'rating' => 4.2,
                'review_count' => 5,
                'is_premium' => false,
                'recruit_bonus_lines' => [
                    ['label' => '体入', 'amount' => 0, 'offered' => false],
                    ['label' => 'ヘルプ', 'amount' => 0, 'offered' => false],
                    ['label' => '本入', 'amount' => 0, 'offered' => true],
                ],
            ],
        ];

        return array_map(function (array $it) {
            $meta = [
                'catch_copy' => $it['catch_copy'] ?? '',
                'bonus_condition' => $it['bonus_condition'] ?? '',
                'bonus_other_conditions' => $it['bonus_other_conditions'] ?? '',
            ];
            foreach (['trial_hourly_min', 'trial_hourly_max', 'help_hourly_min', 'help_hourly_max'] as $wk) {
                if (array_key_exists($wk, $it) && $it[$wk] !== null && $it[$wk] !== '') {
                    $meta[$wk] = $it[$wk];
                }
            }
            $trialW = isset($it['trial_hourly_wage']) && $it['trial_hourly_wage'] !== null && $it['trial_hourly_wage'] !== ''
                ? (int) $it['trial_hourly_wage'] : null;
            $helpW = isset($it['help_hourly_wage']) && $it['help_hourly_wage'] !== null && $it['help_hourly_wage'] !== ''
                ? (int) $it['help_hourly_wage'] : null;
            $it['trial_hourly_range'] = $this->discoveryHourlyPair($trialW, $meta, 'trial');
            $it['help_hourly_range'] = $this->discoveryHourlyPair($helpW, $meta, 'help');
            $it['signup_bonus_range'] = $this->discoverySignupBonusRange($it['recruit_bonus_lines'] ?? []);
            $it['manager_overlay'] = $this->buildManagerImageOverlay(
                $meta,
                (int) ($it['noruma_reward'] ?? 0)
            );

            return $it;
        }, $mockRows);
    }

    /**
     * 求人スワイプ用：体入／ヘルプ時給の表示レンジ（noruma_cond JSON に trial_hourly_min 等があれば優先）
     *
     * @return array{lo: int, hi: int}|null
     */
    private function discoveryHourlyPair(?int $baseWage, array $meta, string $role): ?array
    {
        if ($baseWage === null || $baseWage <= 0) {
            return null;
        }
        $minKey = $role . '_hourly_min';
        $maxKey = $role . '_hourly_max';
        $lo = (isset($meta[$minKey]) && (int) $meta[$minKey] > 0) ? (int) $meta[$minKey] : $baseWage;
        $hi = (isset($meta[$maxKey]) && (int) $meta[$maxKey] > 0) ? (int) $meta[$maxKey] : $baseWage;
        if ($hi < $lo) {
            [$lo, $hi] = [$hi, $lo];
        }

        return ['lo' => $lo, 'hi' => $hi];
    }

    /**
     * 入店祝い金レンジ（体入・ヘルプ・本入で提示している金額の min〜max）
     *
     * @param array<int, array{label: string, amount: int, offered: bool}> $lines
     *
     * @return array{lo: int, hi: int}|null
     */
    private function discoverySignupBonusRange(array $lines): ?array
    {
        $amounts = [];
        foreach ($lines as $ln) {
            if (!empty($ln['offered']) && (int) ($ln['amount'] ?? 0) > 0) {
                $amounts[] = (int) $ln['amount'];
            }
        }
        if ($amounts === []) {
            return null;
        }
        $lo = min($amounts);
        $hi = max($amounts);

        return ['lo' => $lo, 'hi' => $hi];
    }

    /**
     * 求人カード画像上のキャッチ表示（ディスカバリー用：catch_copy のみ。pr・ボーナス条件バッジは出さない）
     *
     * @return array{show: bool, line1_html: string, line2: string, badge: string}
     */
    private function buildManagerImageOverlay(array $meta, int $norumaReward): array
    {
        return RecruitCatchOverlay::buildFromMeta($meta, $norumaReward, true);
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

    /**
     * 求人タグ・店舗タグ（DB登録の shop_tags 系）だけを並べる。一覧で最大 {@see $max} 件まで。
     */
    private function buildHomeRecruitDiscoveryTags(string $shopId, int $shopJobId, int $max = 6): array
    {
        $merged = [];

        if ($shopJobId > 0 && Schema::hasTable('shop_job_tag_relations') && Schema::hasTable('shop_tags')) {
            $qj = DB::table('shop_job_tag_relations as r')
                ->join('shop_tags as t', 'r.tag_id', '=', 't.id')
                ->where('r.shop_job_id', $shopJobId)
                ->where('t.target', 'job')
                ->whereIn('t.category', ['work_style', 'welcome', 'benefit'])
                ->orderBy('t.sort_order')
                ->orderBy('t.id');
            if (Schema::hasColumn('shop_tags', 'del_flg')) {
                $qj->where('t.del_flg', 0);
            }
            foreach ($qj->pluck('t.name') as $n) {
                $this->pushUniqueDiscoveryTag($merged, (string) $n, $max);
                if (count($merged) >= $max) {
                    return $merged;
                }
            }
        }

        if (Schema::hasTable('shop_tag_relations') && Schema::hasTable('shop_tags')) {
            $qs = DB::table('shop_tag_relations as r')
                ->join('shop_tags as t', 'r.tag_id', '=', 't.id')
                ->where('r.shop_id', $shopId)
                ->where('t.target', 'shop')
                ->whereIn('t.category', ['atmosphere', 'facility'])
                ->orderBy('t.sort_order')
                ->orderBy('t.id');
            if (Schema::hasColumn('shop_tags', 'del_flg')) {
                $qs->where('t.del_flg', 0);
            }
            foreach ($qs->pluck('t.name') as $n) {
                $this->pushUniqueDiscoveryTag($merged, (string) $n, $max);
                if (count($merged) >= $max) {
                    return $merged;
                }
            }
        }

        return $merged;
    }

    /** @param list<string> $merged */
    private function pushUniqueDiscoveryTag(array &$merged, string $name, int $max): void
    {
        $t = trim($name);
        if ($t === '' || count($merged) >= $max) {
            return;
        }
        if (in_array($t, $merged, true)) {
            return;
        }
        $merged[] = $t;
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
        $wage = 0;
        if (isset($row->regular_hourly_wage) && $row->regular_hourly_wage !== null && $row->regular_hourly_wage !== '') {
            $wage = (int) $row->regular_hourly_wage;
        } elseif (isset($row->hourly_wage_regular) && $row->hourly_wage_regular !== null && $row->hourly_wage_regular !== '') {
            $wage = (int) $row->hourly_wage_regular;
        }
        if ($wage >= 3000) {
            $tags[] = '高時給';
        }
        $bonus = 0;
        if (isset($row->bonus_reward) && $row->bonus_reward !== null && $row->bonus_reward !== '') {
            $bonus = (int) $row->bonus_reward;
        } elseif (isset($row->noruma_reward) && $row->noruma_reward !== null && $row->noruma_reward !== '') {
            $bonus = (int) $row->noruma_reward;
        }
        if ($bonus > 0) {
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
            ->when(
                Schema::hasColumn('shop_posts', 'type'),
                fn ($q) => $q->where('type', 2)
            )
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