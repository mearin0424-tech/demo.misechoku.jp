<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Common\SearchController as BaseSearchController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends BaseSearchController
{
    public function index(Request $request, ?string $tab = 'timeline')
    {
        $timelineData = $this->buildTimelineData();
        $items = $this->buildSearchItems($request);

        $activeTab = 'pane-' . (in_array($tab, ['timeline', 'list', 'ai'], true) ? $tab : 'timeline');

        return $this->renderIndex([
            'guideMessage' => "あなたの希望に合うお店を探そう！\n条件を絞り込んで検索してみてね。",
            'timelineData' => $timelineData,
            'items'        => $items,
            'activeTab'    => $activeTab,
            'searchTab'    => $tab,
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
                'img' => $this->resolveShopImageUrl((string) $row->id, $row->main_image_path),
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

        $rows = DB::table('shops')
            ->join('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->select(
                'shops.id',
                'shop_profiles.shop_name',
                'shop_profiles.pref',
                'shop_profiles.city',
                'shop_profiles.catch',
                'shop_profiles.overview',
                'shop_profiles.main_image_path'
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
            ->filter(function ($row) use ($normalizedKeyword) {
                if ($normalizedKeyword === '') {
                    return true;
                }

                $haystack = implode(' ', array_filter([
                    $row->shop_name,
                    $row->pref,
                    $row->city,
                    $row->catch,
                    $row->overview,
                ]));

                return str_contains($this->normalizeSearchText($haystack), $normalizedKeyword);
            })
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'shop_name' => (string) ($row->shop_name ?: 'ショップ'),
                    'pref' => $row->pref ?? '',
                    'city' => $row->city ?? '',
                    'main_img' => $this->resolveShopImageUrl((string) $row->id, $row->main_image_path),
                ];
            })
            ->values()
            ->all();
    }

    private function resolveShopImageUrl(string $shopId, ?string $mainImagePath): string
    {
        if (!empty($mainImagePath)) {
            return $this->assetPathForStored($mainImagePath);
        }

        $imagePath = DB::table('shop_images')
            ->where('shop_id', $shopId)
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->value('image_path');

        return $this->assetPathForStored($imagePath);
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
}