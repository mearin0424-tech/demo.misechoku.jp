<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminMasterService
{
    public function getMasterIndexData(): array
    {
        try {
            $reviewContents = $this->fetchReviewContents();
            $tagGroups = $this->fetchTagGroups();
            $ngWords = $this->fetchNgWords();

            return [
                'summary' => [
                    'review_content_count' => $reviewContents->count(),
                    'tag_type_count' => $tagGroups->count(),
                    'tag_count' => $tagGroups->sum(fn (Collection $tags) => $tags->count()),
                    'ng_word_count' => $ngWords->count(),
                ],
                'reviewContents' => $reviewContents,
                'tagGroups' => $tagGroups,
                'ngWords' => $ngWords,
                'error' => null,
            ];
        } catch (QueryException) {
            return [
                'summary' => [
                    'review_content_count' => 0,
                    'tag_type_count' => 0,
                    'tag_count' => 0,
                    'ng_word_count' => 0,
                ],
                'reviewContents' => collect(),
                'tagGroups' => collect(),
                'ngWords' => collect(),
                'error' => 'データベースに接続できないため、マスタ設定を読み込めませんでした。',
            ];
        }
    }

    public function getNgWordData(): array
    {
        try {
            return [
                'words' => $this->fetchNgWords(),
                'error' => null,
            ];
        } catch (QueryException) {
            return [
                'words' => collect(),
                'error' => 'データベースに接続できないため、NGワードを読み込めませんでした。',
            ];
        }
    }

    public function createReviewContent(array $data): void
    {
        DB::table('review_contents')->insert([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'],
            'is_active' => $data['is_active'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function createTag(array $data): void
    {
        DB::table('tags')->insert([
            'type' => $data['type'],
            'name' => $data['name'],
            'created_at' => now(),
        ]);
    }

    public function createNgWord(array $data): void
    {
        DB::table('ng_words')->insert([
            'word' => $data['word'],
            'is_active' => $data['is_active'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function fetchReviewContents(): Collection
    {
        return DB::table('review_contents as rc')
            ->leftJoin('review_details as rd', 'rd.review_content_id', '=', 'rc.id')
            ->select(
                'rc.id',
                'rc.name',
                'rc.sort_order',
                'rc.is_active',
                DB::raw('COUNT(rd.id) as usage_count')
            )
            ->groupBy('rc.id', 'rc.name', 'rc.sort_order', 'rc.is_active')
            ->orderBy('rc.sort_order')
            ->orderBy('rc.id')
            ->get();
    }

    private function fetchTagGroups(): Collection
    {
        $castUsage = DB::table('cast_tag')
            ->select('tag_id', DB::raw('COUNT(*) as cast_usage'))
            ->groupBy('tag_id');

        $shopUsage = DB::table('shop_tag')
            ->select('tag_id', DB::raw('COUNT(*) as shop_usage'))
            ->groupBy('tag_id');

        return DB::table('tags as t')
            ->leftJoinSub($castUsage, 'ct', fn ($join) => $join->on('ct.tag_id', '=', 't.id'))
            ->leftJoinSub($shopUsage, 'st', fn ($join) => $join->on('st.tag_id', '=', 't.id'))
            ->select(
                't.id',
                't.type',
                't.name',
                DB::raw('COALESCE(ct.cast_usage, 0) as cast_usage_count'),
                DB::raw('COALESCE(st.shop_usage, 0) as shop_usage_count'),
                DB::raw('COALESCE(ct.cast_usage, 0) + COALESCE(st.shop_usage, 0) as usage_count')
            )
            ->orderBy('t.type')
            ->orderBy('t.id')
            ->get()
            ->groupBy('type')
            ->map(function (Collection $items, string $type) {
                return $items->map(function ($item) use ($type) {
                    $item->type_label = $this->resolveTagTypeLabel($type);

                    return $item;
                });
            });
    }

    private function fetchNgWords(): Collection
    {
        return DB::table('ng_words')
            ->select('id', 'word', 'is_active', 'created_at')
            ->orderBy('word')
            ->get();
    }

    private function resolveTagTypeLabel(string $type): string
    {
        return match ($type) {
            'salary' => '待遇タグ',
            'howto' => '働き方タグ',
            'casttag' => 'キャストタグ',
            default => $type,
        };
    }
}
