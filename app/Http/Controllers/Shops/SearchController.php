<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Common\SearchController as BaseSearchController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends BaseSearchController
{
    public function index(Request $request, ?string $tab = 'timeline')
    {
        $searchTab = in_array($tab, ['timeline', 'list'], true) ? $tab : 'timeline';
        $timelineData = $this->buildTimelineData();
        $items = $this->buildSearchItems($request);
        $activeTab = 'pane-' . $searchTab;
        $guideMessage = $searchTab === 'list'
            ? "ここでは気になるキャストを条件で絞り込んで探せるよ！\n詳細検索でぴったりの相手を見つけてみてね。"
            : "ここでは新着のキャストをチェックできるよ！\n気になる相手を見つけたら詳細も見てみてね。";

        return $this->renderIndex([
            'guideMessage' => $guideMessage,
            'timelineData' => $timelineData,
            'items'        => $items,
            'activeTab'    => $activeTab,
            'searchTab'    => $searchTab,
        ]);
    }

    private function buildTimelineData(): array
    {
        $rows = DB::table('casts')
            ->join('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->whereNotNull('cast_profiles.pr')
            ->where('cast_profiles.pr', '<>', '')
            ->orderByDesc('cast_profiles.updated_at')
            ->orderByDesc('casts.id')
            ->select(
                'casts.id',
                'cast_profiles.nickname',
                'cast_profiles.name',
                'cast_profiles.pr',
                'cast_profiles.main_image_path',
                'cast_profiles.updated_at'
            )
            ->limit(20)
            ->get();

        return $rows->map(function ($row) {
            $updatedAt = $row->updated_at ? Carbon::parse($row->updated_at) : null;

            return [
                'name' => $this->castDisplayName($row),
                'img' => $this->getCastImages((string) $row->id)[0] ?? asset('assets/images/common/no-image.png'),
                'time' => $updatedAt ? $updatedAt->locale('ja')->diffForHumans() : '',
                'text' => (string) $row->pr,
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

        $rows = DB::table('casts')
            ->join('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
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
            ->orderByDesc('cast_profiles.updated_at')
            ->orderByDesc('casts.id');

        if (!empty($industries) && DB::getSchemaBuilder()->hasTable('cast_industry')) {
            $rows->join('cast_industry', 'casts.id', '=', 'cast_industry.cast_id')
                ->join('industries', 'cast_industry.industry_id', '=', 'industries.id')
                ->whereIn('industries.name', $industries)
                ->distinct();
        }

        return $rows->get()
            ->filter(function ($row) use ($normalizedKeyword) {
                if ($normalizedKeyword === '') {
                    return true;
                }

                $haystack = implode(' ', array_filter([
                    $row->nickname,
                    $row->name,
                    $row->pref,
                    $row->city,
                    $row->pr,
                ]));

                return str_contains($this->normalizeSearchText($haystack), $normalizedKeyword);
            })
            ->map(function ($row) {
                $birthday = $row->birthday ? Carbon::parse($row->birthday) : null;

                return [
                    'id' => $row->id,
                    'name' => $this->castDisplayName($row),
                    'age' => $birthday?->age,
                    'img' => $this->getCastImages((string) $row->id)[0] ?? asset('assets/images/common/no-image.png'),
                    'pref' => $row->pref ?? '',
                    'city' => $row->city ?? '',
                    'pr' => (string) ($row->pr ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    private function getCastImages(string $castId): array
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

    private function castDisplayName(object $row): string
    {
        return (string) ($row->nickname ?: $row->name ?: 'キャスト');
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