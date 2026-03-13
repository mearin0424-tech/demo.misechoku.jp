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
        $rows = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->whereNotNull('shop_profiles.catch')
            ->where('shop_profiles.catch', '<>', '')
            ->orderByDesc('shop_profiles.updated_at')
            ->orderByDesc('shops.id')
            ->select(
                'shops.id',
                'shop_profiles.shop_name',
                'shop_profiles.catch',
                'shop_profiles.main_image_path',
                'shop_profiles.updated_at'
            )
            ->limit(20)
            ->get();

        return $rows->map(function ($row) {
            $updatedAt = $row->updated_at ? Carbon::parse($row->updated_at) : null;

            return [
                'name' => (string) ($row->shop_name ?: 'ショップ'),
                'img' => $this->getShopImages((string) $row->id)[0] ?? asset('assets/images/common/no-image.png'),
                'time' => $updatedAt ? $updatedAt->locale('ja')->diffForHumans() : '',
                'text' => (string) $row->catch,
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
        $tagFilters = [
            'salary' => $this->normalizeIdFilters($request->query('salary_tag_ids', [])),
            'howto' => $this->normalizeIdFilters($request->query('howto_tag_ids', [])),
            'merit' => $this->normalizeIdFilters($request->query('merit_tag_ids', [])),
            'feature' => $this->normalizeIdFilters($request->query('feature_tag_ids', [])),
            'facility' => $this->normalizeIdFilters($request->query('facility_tag_ids', [])),
            'atmosphere' => $this->normalizeIdFilters($request->query('atmosphere_tag_ids', [])),
        ];

        $rows = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->select(
                'shops.id',
                'shop_profiles.shop_name',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.catch',
                'shop_profiles.overview',
                'shop_profiles.main_image_path',
                'shop_jobs.hourly_wage_regular',
                'shop_jobs.noruma_reward',
                'shop_jobs.noruma_cond'
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
            ->filter(function ($row) use ($normalizedKeyword, $areas, $hourlyWage, $reward, $tagFilters) {
                if ($normalizedKeyword === '') {
                    $matchesKeyword = true;
                } else {
                    $haystack = implode(' ', array_filter([
                        $row->shop_name,
                        $row->pref,
                        $row->city,
                        $row->catch,
                        $row->overview,
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

                $meta = $this->decodeMeta($row->noruma_cond ?? null);

                return $this->matchesTagFilters($meta['tag_ids'] ?? [], $tagFilters);
            })
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'shop_name' => (string) ($row->shop_name ?: 'ショップ'),
                    'pref' => $row->pref ?? '',
                    'city' => $row->city ?? '',
                    'catch' => (string) ($row->catch ?? ''),
                    'overview' => (string) ($row->overview ?? ''),
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
            'industries' => $profileMasters['industries'] ?? collect(),
            'areas' => $this->fetchAreaOptions(),
            'hourly_wages' => $this->fetchNumericOptions('hourly_wage_regular'),
            'rewards' => $this->fetchNumericOptions('noruma_reward'),
            'salary' => $recruitmentMasters['salary'] ?? collect(),
            'howto' => $recruitmentMasters['howto'] ?? collect(),
            'merit' => $recruitmentMasters['merit'] ?? collect(),
            'feature' => $recruitmentMasters['feature'] ?? collect(),
            'facility' => $recruitmentMasters['facility'] ?? collect(),
            'atmosphere' => $recruitmentMasters['atmosphere'] ?? collect(),
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

    private function matchesTagFilters(array $tagIds, array $filters): bool
    {
        foreach ($filters as $key => $ids) {
            if (empty($ids)) {
                continue;
            }

            $selected = collect($tagIds[$key] ?? [])
                ->map(fn ($value) => (int) $value)
                ->filter()
                ->all();

            if (empty(array_intersect($ids, $selected))) {
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