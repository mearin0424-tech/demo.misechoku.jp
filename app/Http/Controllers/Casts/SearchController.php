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
    public function __construct(private readonly AdminMasterService $adminMasterService)
    {
    }

    public function index(Request $request, ?string $tab = 'timeline')
    {
        $timelineData = $this->buildTimelineData();
        $items = $this->buildSearchItems($request);
        $personalityType = $this->currentCastPersonalityType();

        $activeTab = 'pane-' . (in_array($tab, ['timeline', 'list', 'ai'], true) ? $tab : 'timeline');

        return $this->renderIndex([
            'guideMessage' => "あなたの希望に合うお店を探そう！\n条件を絞り込んで検索してみてね。",
            'timelineData' => $timelineData,
            'items'        => $items,
            'personalityType' => $personalityType,
            'activeTab'    => $activeTab,
            'searchTab'    => $tab,
            'detailSearchOptions' => $this->buildDetailSearchOptions(),
        ]);
    }

    private function buildTimelineData(): array
    {
        $rows = DB::table('shop_posts')
            ->join('shops', 'shops.id', '=', 'shop_posts.shop_id')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->when(
                Schema::hasColumn('shop_posts', 'type'),
                fn ($q) => $q->where('shop_posts.type', 2)
            )
            ->whereNotNull('shop_posts.body')
            ->where('shop_posts.body', '<>', '')
            ->orderByDesc('shop_posts.created_at')
            ->orderByDesc('shop_posts.id')
            ->select(
                'shops.id',
                'shop_profiles.shop_name',
                'shop_posts.body',
                'shop_posts.created_at',
                'shop_profiles.main_image_path'
            )
            ->limit(20)
            ->get();

        return $rows->map(function ($row) {
            $createdAt = $row->created_at ? Carbon::parse($row->created_at) : null;

            return [
                'name' => (string) ($row->shop_name ?: 'ショップ'),
                'img' => $this->getShopImages((string) $row->id)[0] ?? asset('assets/images/common/no-image.png'),
                'time' => $createdAt ? $createdAt->locale('ja')->diffForHumans() : '',
                'text' => (string) $row->body,
            ];
        })->all();
    }

    private function buildSearchItems(Request $request): array
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

        $rows = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->leftJoin('shop_posts', function ($join) {
                $join->on('shops.id', '=', 'shop_posts.shop_id');
                if (Schema::hasColumn('shop_posts', 'type')) {
                    $join->where('shop_posts.type', 2);
                }
            })
            ->select(
                'shops.id',
                'shop_jobs.id as shop_job_id',
                'shop_profiles.shop_name',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.main_image_path',
                'shop_jobs.hourly_wage_regular',
                'shop_jobs.noruma_reward',
                'shop_jobs.noruma_cond',
                'shop_posts.body as shop_post_body'
            )
            ->orderByDesc('shop_profiles.updated_at')
            ->orderByDesc('shops.id');

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

                if ($hourlyWage > 0 && (int) ($row->hourly_wage_regular ?? 0) < $hourlyWage) {
                    return false;
                }

                if ($reward > 0 && (int) ($row->noruma_reward ?? 0) < $reward) {
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
                return [
                    'id' => $row->id,
                    'shop_name' => (string) ($row->shop_name ?: 'ショップ'),
                    'pref' => $row->pref ?? '',
                    'city' => $row->city ?? '',
                    'catch' => (string) ($row->shop_post_body ?? ''),
                    'overview' => '',
                    'main_img' => $this->getShopImages((string) $row->id)[0] ?? asset('assets/images/common/no-image.png'),
                ];
            })
            ->values()
            ->all();
    }

    private function buildDetailSearchOptions(): array
    {
        $recruitmentMasters = $this->adminMasterService->getRecruitmentMasters();
        $profileMasters = $this->adminMasterService->getShopProfileMasters();

        return [
            'industries'   => $profileMasters['industries'] ?? collect(),
            'areas'        => $this->fetchAreaOptions(),
            'hourly_wages' => $this->fetchNumericOptions('hourly_wage_regular'),
            'rewards'      => $this->fetchNumericOptions('noruma_reward'),
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