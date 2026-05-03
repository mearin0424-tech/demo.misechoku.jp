<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Common\SearchController as BaseSearchController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SearchController extends BaseSearchController
{
    private const SORT_OPTIONS = [
        'hitokoto' => 'ひとこと最終更新が新しい順',
        'new'      => '新着登録順',
        'name'     => '名前（あいうえお順）',
        'age_asc'  => '年齢が若い順',
        'age_desc' => '年齢が高い順',
    ];

    public function index(Request $request)
    {
        $sort = (string) $request->query('sort', 'hitokoto');
        if (!array_key_exists($sort, self::SORT_OPTIONS)) {
            $sort = 'hitokoto';
        }

        $items = $this->buildSearchItems($request, $sort);

        return $this->renderIndex([
            'guideMessage' => "気になるキャストを探そう！\nひとこと更新が新しい順に並んでいるよ。",
            'items'        => $items,
            'sort'         => $sort,
            'sortOptions'  => self::SORT_OPTIONS,
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

        $hasCastPostsTable = Schema::hasTable('cast_posts');

        $rows = DB::table('casts')
            ->join('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id');

        if ($hasCastPostsTable) {
            $rows->leftJoin('cast_posts', 'casts.id', '=', 'cast_posts.cast_id');
        }

        $select = [
            'casts.id',
            'casts.created_at as cast_created_at',
            'cast_profiles.nickname',
            'cast_profiles.name',
            'cast_profiles.birthday',
            'cast_profiles.pref',
            'cast_profiles.city',
            'cast_profiles.pr',
            'cast_profiles.main_image_path',
            'cast_profiles.updated_at as profile_updated_at',
        ];

        if ($hasCastPostsTable) {
            $select[] = 'cast_posts.body as hitokoto_body';
            $select[] = 'cast_posts.updated_at as hitokoto_updated_at';
            $select[] = 'cast_posts.created_at as hitokoto_created_at';
        } else {
            $select[] = DB::raw('NULL as hitokoto_body');
            $select[] = DB::raw('NULL as hitokoto_updated_at');
            $select[] = DB::raw('NULL as hitokoto_created_at');
        }

        $rows->select($select);

        $this->applySort($rows, $sort, $hasCastPostsTable);

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
                    $row->hitokoto_body ?? null,
                ]));

                return str_contains($this->normalizeSearchText($haystack), $normalizedKeyword);
            })
            ->map(function ($row) {
                $birthday = $row->birthday ? Carbon::parse($row->birthday) : null;
                $hitokotoUpdatedAt = $row->hitokoto_updated_at
                    ? Carbon::parse($row->hitokoto_updated_at)
                    : ($row->hitokoto_created_at ? Carbon::parse($row->hitokoto_created_at) : null);
                $hitokotoBody = (string) ($row->hitokoto_body ?? '');
                if ($hitokotoBody === '') {
                    $hitokotoBody = (string) ($row->pr ?? '');
                }

                return [
                    'id'                  => $row->id,
                    'name'                => $this->castDisplayName($row),
                    'age'                 => $birthday?->age,
                    'img'                 => $this->getCastImages((string) $row->id)[0] ?? asset('assets/images/common/no-image.png'),
                    'pref'                => $row->pref ?? '',
                    'city'                => $row->city ?? '',
                    'pr'                  => (string) ($row->pr ?? ''),
                    'hitokoto'            => $hitokotoBody,
                    'hitokoto_updated_at' => $hitokotoUpdatedAt?->locale('ja')->diffForHumans(),
                ];
            })
            ->values()
            ->all();
    }

    private function applySort($rows, string $sort, bool $hasCastPostsTable): void
    {
        switch ($sort) {
            case 'new':
                $rows->orderByDesc('casts.created_at')->orderByDesc('casts.id');
                break;
            case 'name':
                $rows->orderByRaw('COALESCE(cast_profiles.nickname, cast_profiles.name)')
                    ->orderBy('casts.id');
                break;
            case 'age_asc':
                $rows->orderByDesc('cast_profiles.birthday')->orderByDesc('casts.id');
                break;
            case 'age_desc':
                $rows->orderBy('cast_profiles.birthday')->orderByDesc('casts.id');
                break;
            case 'hitokoto':
            default:
                if ($hasCastPostsTable) {
                    // ひとこと最終更新が新しい順。未投稿のキャストは最後に回す。
                    $rows->orderByRaw('cast_posts.updated_at IS NULL')
                        ->orderByDesc('cast_posts.updated_at')
                        ->orderByDesc('cast_profiles.updated_at')
                        ->orderByDesc('casts.id');
                } else {
                    $rows->orderByDesc('cast_profiles.updated_at')->orderByDesc('casts.id');
                }
                break;
        }
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
