<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Common\SearchController as BaseSearchController;
use App\Services\AdminMasterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SearchController extends BaseSearchController
{
    private const SORT_OPTIONS = [
        'hitokoto' => 'ひとこと最終更新が新しい順',
        'new'      => '新着登録順',
        'name'     => '店舗名（あいうえお順）',
        'wage'     => '時給が高い順',
        'reward'   => '採用報酬が高い順',
    ];

    public function __construct(private readonly AdminMasterService $adminMasterService)
    {
    }

    public function index(Request $request, ?string $tab = 'search')
    {
        $tab = in_array($tab, ['search', 'ai'], true) ? $tab : 'search';
        $activeTab = 'pane-' . $tab;

        $sort = (string) $request->query('sort', 'hitokoto');
        if (!array_key_exists($sort, self::SORT_OPTIONS)) {
            $sort = 'hitokoto';
        }

        $items = $this->buildSearchItems($request, $sort);
        $personalityType = $this->currentCastPersonalityType();

        return $this->renderIndex([
            'guideMessage'        => "あなたの希望に合うお店を探そう！\nひとこと更新が新しい順に並んでいるよ。",
            'items'               => $items,
            'personalityType'     => $personalityType,
            'activeTab'           => $activeTab,
            'searchTab'           => $tab,
            'sort'                => $sort,
            'sortOptions'         => self::SORT_OPTIONS,
            'detailSearchOptions' => $this->buildDetailSearchOptions(),
        ]);
    }

    /**
     * 統合済み検索結果（タイムライン＋一覧）を生成する。
     */
    private function buildSearchItems(Request $request, string $sort): array
    {
        $keyword = $request->query('keyword');
        $keyword = is_string($keyword) ? trim($keyword) : '';
        $normalizedKeyword = $this->normalizeSearchText($keyword);
        $industries = $request->query('industry', []);
        $industries = is_array($industries) ? array_values(array_filter($industries, 'is_string')) : (is_string($industries) ? [$industries] : []);
        $areas = $request->query('area', []);
        $areas = is_array($areas) ? array_values(array_filter($areas, 'is_string')) : (is_string($areas) ? [$areas] : []);
        $hourlyWage = (int) $request->query('hourly_wage', 0);
        $reward = (int) $request->query('reward', 0);
        $jobTagFilters = [
            'work_style' => $this->normalizeIdFilters($request->query('work_style_tag_ids', [])),
            'welcome'    => $this->normalizeIdFilters($request->query('welcome_tag_ids', [])),
            'benefit'    => $this->normalizeIdFilters($request->query('benefit_tag_ids', [])),
        ];
        $shopTagFilters = [
            'atmosphere' => $this->normalizeIdFilters($request->query('atmosphere_tag_ids', [])),
            'facility'   => $this->normalizeIdFilters($request->query('facility_tag_ids', [])),
        ];

        // 各ショップごとに「ひとこと」の最新行（id 最大）を 1 件だけ参照する。
        // shop_posts は (shop_id, type=2) の組み合わせで updateOrInsert されているので
        // 実質ユニークだが、安全のため MAX(id) でサブクエリ化している。
        $latestShopPost = DB::table('shop_posts')
            ->select('shop_id', DB::raw('MAX(id) as latest_id'))
            ->when(
                Schema::hasColumn('shop_posts', 'type'),
                fn ($q) => $q->where('type', 2)
            )
            ->whereNotNull('body')
            ->where('body', '<>', '')
            ->groupBy('shop_id');

        $rows = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->leftJoinSub($latestShopPost, 'latest_post', function ($join) {
                $join->on('shops.id', '=', 'latest_post.shop_id');
            })
            ->leftJoin('shop_posts', 'shop_posts.id', '=', 'latest_post.latest_id');

        $jobSelect = [];
        if (Schema::hasColumn('shop_jobs', 'hourly_wage_regular')) {
            $jobSelect[] = 'shop_jobs.hourly_wage_regular';
        }
        if (Schema::hasColumn('shop_jobs', 'regular_hourly_wage')) {
            $jobSelect[] = 'shop_jobs.regular_hourly_wage';
        }
        if (Schema::hasColumn('shop_jobs', 'noruma_reward')) {
            $jobSelect[] = 'shop_jobs.noruma_reward';
        }
        if (Schema::hasColumn('shop_jobs', 'bonus_reward')) {
            $jobSelect[] = 'shop_jobs.bonus_reward';
        }
        if (Schema::hasColumn('shop_jobs', 'noruma_cond')) {
            $jobSelect[] = 'shop_jobs.noruma_cond';
        }

        $rows = $rows->select(array_merge(
            [
                'shops.id',
                'shop_jobs.id as shop_job_id',
                'shop_profiles.shop_name',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.main_image_path',
                'shop_profiles.updated_at as profile_updated_at',
                'shops.created_at as shop_created_at',
                'shop_posts.body as shop_post_body',
                'shop_posts.updated_at as shop_post_updated_at',
                'shop_posts.created_at as shop_post_created_at',
            ],
            $jobSelect
        ));

        $this->applySort($rows, $sort);

        if (!empty($industries)) {
            if (DB::getSchemaBuilder()->hasTable('industry_shop')) {
                $rows->join('industry_shop', 'shops.id', '=', 'industry_shop.shop_id')
                    ->join('industries', 'industry_shop.industry_id', '=', 'industries.id')
                    ->whereIn('industries.name', $industries)
                    ->distinct();
            } elseif (DB::getSchemaBuilder()->hasTable('shop_industries')) {
                $rows->join('shop_industries', 'shops.id', '=', 'shop_industries.shop_id')
                    ->join('industries', 'shop_industries.industry_id', '=', 'industries.id')
                    ->whereIn('industries.name', $industries)
                    ->distinct();
            }
        }

        return $rows->get()
            ->filter(function ($row) use ($normalizedKeyword, $areas, $hourlyWage, $reward, $jobTagFilters, $shopTagFilters) {
                if ($normalizedKeyword === '') {
                    $matchesKeyword = true;
                } else {
                    $haystack = implode(' ', array_filter([
                        $row->shop_name,
                        $row->pref,
                        $row->city,
                        $row->shop_post_body ?? null,
                    ]));

                    $matchesKeyword = str_contains($this->normalizeSearchText($haystack), $normalizedKeyword);
                }

                if (!$matchesKeyword) {
                    return false;
                }

                $areaLabel = $this->formatAreaLabel($row->pref ?? '', $row->city ?? '');
                if (!empty($areas) && !in_array($areaLabel, $areas, true)) {
                    return false;
                }

                if ($hourlyWage > 0 && $this->searchRowHourlyWage($row) < $hourlyWage) {
                    return false;
                }

                if ($reward > 0 && $this->searchRowReward($row) < $reward) {
                    return false;
                }

                if (!$this->matchesShopJobTagFilters((int) ($row->shop_job_id ?? 0), $jobTagFilters)) {
                    return false;
                }

                if (!$this->matchesShopProfileTagFilters((string) $row->id, $shopTagFilters)) {
                    return false;
                }

                return true;
            })
            ->map(function ($row) {
                $hitokotoUpdatedAt = $row->shop_post_updated_at
                    ? Carbon::parse($row->shop_post_updated_at)
                    : ($row->shop_post_created_at ? Carbon::parse($row->shop_post_created_at) : null);

                return [
                    'id'                  => $row->id,
                    'shop_name'           => (string) ($row->shop_name ?: 'ショップ'),
                    'pref'                => $row->pref ?? '',
                    'city'                => $row->city ?? '',
                    'catch'               => (string) ($row->shop_post_body ?? ''),
                    'overview'            => '',
                    'main_img'            => $this->getShopImages((string) $row->id)[0] ?? asset('assets/images/common/no-image.png'),
                    'hitokoto'            => (string) ($row->shop_post_body ?? ''),
                    'hitokoto_updated_at' => $hitokotoUpdatedAt?->locale('ja')->diffForHumans(),
                    'hourly_wage'         => $this->searchRowHourlyWage($row),
                    'reward'              => $this->searchRowReward($row),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * 並び替え条件をクエリに反映する。
     */
    private function applySort($rows, string $sort): void
    {
        switch ($sort) {
            case 'new':
                $rows->orderByDesc('shops.created_at')->orderByDesc('shops.id');
                break;
            case 'name':
                $rows->orderBy('shop_profiles.shop_name')->orderBy('shops.id');
                break;
            case 'wage':
                $wageCols = [];
                if (Schema::hasColumn('shop_jobs', 'regular_hourly_wage')) {
                    $wageCols[] = 'shop_jobs.regular_hourly_wage';
                }
                if (Schema::hasColumn('shop_jobs', 'hourly_wage_regular')) {
                    $wageCols[] = 'shop_jobs.hourly_wage_regular';
                }
                if ($wageCols !== []) {
                    $rows->orderByRaw('COALESCE(' . implode(', ', $wageCols) . ', 0) DESC')
                        ->orderByDesc('shops.id');
                } else {
                    $rows->orderByDesc('shops.id');
                }
                break;
            case 'reward':
                $rewardCols = [];
                if (Schema::hasColumn('shop_jobs', 'bonus_reward')) {
                    $rewardCols[] = 'shop_jobs.bonus_reward';
                }
                if (Schema::hasColumn('shop_jobs', 'noruma_reward')) {
                    $rewardCols[] = 'shop_jobs.noruma_reward';
                }
                if ($rewardCols !== []) {
                    $rows->orderByRaw('COALESCE(' . implode(', ', $rewardCols) . ', 0) DESC')
                        ->orderByDesc('shops.id');
                } else {
                    $rows->orderByDesc('shops.id');
                }
                break;
            case 'hitokoto':
            default:
                // ひとこと最終更新が新しい順。未投稿のショップは最後に回す。
                $rows->orderByRaw('shop_posts.updated_at IS NULL')
                    ->orderByDesc('shop_posts.updated_at')
                    ->orderByDesc('shop_profiles.updated_at')
                    ->orderByDesc('shops.id');
                break;
        }
    }

    private function buildDetailSearchOptions(): array
    {
        $recruitmentMasters = $this->adminMasterService->getRecruitmentMasters();
        $profileMasters = $this->adminMasterService->getShopProfileMasters();

        return [
            'industries'   => $profileMasters['industries'] ?? collect(),
            'areas'        => $this->fetchAreaOptions(),
            'hourly_wages' => $this->fetchNumericOptions(
                Schema::hasColumn('shop_jobs', 'regular_hourly_wage') ? 'regular_hourly_wage' : 'hourly_wage_regular'
            ),
            'rewards'      => $this->fetchNumericOptions(
                Schema::hasColumn('shop_jobs', 'bonus_reward') ? 'bonus_reward' : 'noruma_reward'
            ),
            'work_style'   => $recruitmentMasters['work_style'] ?? collect(),
            'welcome'      => $recruitmentMasters['welcome'] ?? collect(),
            'benefit'      => $recruitmentMasters['benefit'] ?? collect(),
            'atmosphere'   => $profileMasters['atmosphere'] ?? collect(),
            'facility'     => $profileMasters['facility'] ?? collect(),
        ];
    }

    private function fetchAreaOptions(): Collection
    {
        if (!Schema::hasTable('shop_profiles')) {
            return collect();
        }

        return DB::table('shop_profiles')
            ->select('pref', 'city')
            ->orderBy('pref')
            ->orderBy('city')
            ->get()
            ->map(function ($row) {
                $label = $this->formatAreaLabel($row->pref ?? '', $row->city ?? '');

                return $label === '' ? null : (object) ['name' => $label];
            })
            ->filter()
            ->unique('name')
            ->values();
    }

    private function fetchNumericOptions(string $column): Collection
    {
        if (!Schema::hasTable('shop_jobs') || !Schema::hasColumn('shop_jobs', $column)) {
            return collect();
        }

        return DB::table('shop_jobs')
            ->whereNotNull($column)
            ->pluck($column)
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value > 0)
            ->unique()
            ->sort()
            ->values();
    }

    private function searchRowHourlyWage(object $row): int
    {
        if (property_exists($row, 'regular_hourly_wage') && $row->regular_hourly_wage !== null && $row->regular_hourly_wage !== '') {
            return (int) $row->regular_hourly_wage;
        }

        return (int) ($row->hourly_wage_regular ?? 0);
    }

    private function searchRowReward(object $row): int
    {
        if (property_exists($row, 'bonus_reward') && $row->bonus_reward !== null && $row->bonus_reward !== '') {
            return (int) $row->bonus_reward;
        }

        return (int) ($row->noruma_reward ?? 0);
    }

    private function formatAreaLabel(?string $pref, ?string $city): string
    {
        return trim(implode(' ', array_filter([(string) $pref, (string) $city])));
    }

    private function normalizeIdFilters(array|string|null $values): array
    {
        if (is_string($values)) {
            $values = [$values];
        }

        if (!is_array($values)) {
            return [];
        }

        return collect($values)
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function decodeMeta(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * 求人票のタグ（shop_job_tag_relations）でフィルタリング.
     *
     * @param array<string, array<int,int>> $filters
     */
    private function matchesShopJobTagFilters(int $shopJobId, array $filters): bool
    {
        $hasFilter = false;
        foreach ($filters as $ids) {
            if (!empty($ids)) {
                $hasFilter = true;
                break;
            }
        }
        if (!$hasFilter) {
            return true;
        }

        if ($shopJobId <= 0 || !Schema::hasTable('shop_job_tag_relations')) {
            return false;
        }

        foreach ($filters as $category => $ids) {
            if (empty($ids)) {
                continue;
            }
            $hit = DB::table('shop_job_tag_relations')
                ->where('shop_job_id', $shopJobId)
                ->where('tag_type', $category)
                ->whereIn('tag_id', $ids)
                ->exists();
            if (!$hit) {
                return false;
            }
        }

        return true;
    }

    /**
     * 店舗プロフィールのタグ（shop_tag_relations）でフィルタリング.
     *
     * @param array<string, array<int,int>> $filters
     */
    private function matchesShopProfileTagFilters(string $shopId, array $filters): bool
    {
        $hasFilter = false;
        foreach ($filters as $ids) {
            if (!empty($ids)) {
                $hasFilter = true;
                break;
            }
        }
        if (!$hasFilter) {
            return true;
        }

        if (!Schema::hasTable('shop_tag_relations')) {
            return false;
        }

        foreach ($filters as $category => $ids) {
            if (empty($ids)) {
                continue;
            }
            $hit = DB::table('shop_tag_relations')
                ->where('shop_id', $shopId)
                ->where('tag_type', $category)
                ->whereIn('tag_id', $ids)
                ->exists();
            if (!$hit) {
                return false;
            }
        }

        return true;
    }

    private function getShopImages(string $shopId): array
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

    private function currentCastPersonalityType(): ?string
    {
        $castId = (string) auth()->guard('member')->id();
        if ($castId === '') {
            return null;
        }

        $row = DB::table('cast_profiles')
            ->where('cast_id', $castId)
            ->select(
                Schema::hasColumn('cast_profiles', 'personality_type')
                    ? 'personality_type'
                    : DB::raw('NULL as personality_type'),
                'memo'
            )
            ->first();

        if (!$row) {
            return null;
        }

        $memo = $this->decodeMeta($row->memo ?? null);
        $type = $row->personality_type ?? ($memo['personality_type'] ?? null);

        return is_string($type) && preg_match('/^[LF][CP][IO][HR]$/', $type) ? $type : null;
    }
}
