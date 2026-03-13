<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminMasterService
{
    public function getMasterIndexData(array $sorts = []): array
    {
        try {
            $catalogs = collect($this->catalogDefinitions())
                ->map(function (array $catalog) {
                    $catalog['records'] = $this->fetchCatalogRecords($catalog);
                    $catalog['count'] = $catalog['records']->count();

                    return $catalog;
                });
            $ngWords = $this->fetchNgWords();
            $profileCatalogKeys = collect([
                'industries',
                'tags_cast_looks',
                'tags_cast_personality',
            ]);
            $recruitCatalogKeys = collect([
                'tags_salary',
                'tags_howto',
                'tags_merit',
                'tags_feature',
                'tags_facility',
                'tags_atmosphere',
            ]);

            return [
                'summary' => [
                    'catalog_count' => $catalogs->count(),
                    'record_count' => $catalogs->sum('count'),
                    'profile_master_count' => $catalogs
                        ->whereIn('key', $profileCatalogKeys)
                        ->sum('count'),
                    'recruit_master_count' => $catalogs
                        ->whereIn('key', $recruitCatalogKeys)
                        ->sum('count'),
                    'ng_word_count' => $ngWords->count(),
                ],
                'catalogs' => $catalogs,
                'ngWords' => $ngWords,
                'error' => null,
            ];
        } catch (QueryException) {
            return [
                'summary' => [
                    'catalog_count' => 0,
                    'record_count' => 0,
                    'profile_master_count' => 0,
                    'recruit_master_count' => 0,
                    'ng_word_count' => 0,
                ],
                'catalogs' => collect(),
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

    public function getCatalogDefinition(string $key): ?array
    {
        return collect($this->catalogDefinitions())
            ->firstWhere('key', $key);
    }

    public function createCatalogRecord(string $key, array $data): void
    {
        $catalog = $this->getCatalogDefinition($key);

        if (!$catalog) {
            return;
        }

        $payload = [];
        foreach ($catalog['fields'] as $field) {
            $payload[$field['column']] = $data[$field['input']] ?? null;
        }

        if (!empty($catalog['uses_del_flg'])) {
            $payload['del_flg'] = 0;
        }
        if (!empty($catalog['uses_is_active'])) {
            $payload['is_active'] = 1;
        }
        if (!empty($catalog['uses_sort_order'])) {
            $max = (int) DB::table($catalog['table'])->max('id');
            $payload['sort_order'] = $max + 1;
        }

        $payload['created_at'] = now();
        $payload['updated_at'] = now();

        DB::table($catalog['table'])->insert($payload);
    }

    public function getCastProfileMasters(): array
    {
        return [
            'industries' => $this->fetchSimpleOptions('industries'),
            'looks' => $this->fetchSimpleOptions('tags_cast_looks'),
            'personalities' => $this->fetchSimpleOptions('tags_cast_personality'),
        ];
    }

    public function getShopProfileMasters(): array
    {
        return [
            'industries' => $this->fetchSimpleOptions('industries'),
        ];
    }

    private function catalogDefinitions(): array
    {
        return [
            [
                'key' => 'industries',
                'table' => 'industries',
                'title' => '業種マスタ',
                'description' => '店舗・キャストの業種選択で使うマスタです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => '業種名', 'placeholder' => '例: キャバクラ'],
                ],
            ],
            [
                'key' => 'review_contents',
                'table' => 'review_contents',
                'title' => 'レビュー設問マスタ',
                'description' => 'レビュー投稿時に参照する設問です。',
                'fields' => [
                    ['input' => 'content', 'column' => $this->reviewContentColumn(), 'label' => '設問内容', 'placeholder' => '例: スタッフの対応は親切ですか？'],
                ],
                'uses_del_flg' => $this->reviewContentColumn() === 'content',
                'uses_is_active' => $this->reviewContentColumn() === 'name',
                'uses_sort_order' => $this->reviewContentColumn() === 'name',
            ],
            [
                'key' => 'column_categories',
                'table' => 'column_categories',
                'title' => 'お役立ち情報カテゴリ',
                'description' => 'コラムのカテゴリです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'カテゴリ名', 'placeholder' => '例: 面接対策'],
                    ['input' => 'directory', 'column' => 'directory', 'label' => 'ディレクトリ', 'placeholder' => '例: interview'],
                ],
                'uses_del_flg' => true,
            ],
            [
                'key' => 'column_tags',
                'table' => 'column_tags',
                'title' => 'お役立ち情報タグ',
                'description' => 'コラムのタグです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 面接'],
                    ['input' => 'directory', 'column' => 'directory', 'label' => 'ディレクトリ', 'placeholder' => '例: interview'],
                ],
                'uses_del_flg' => true,
            ],
            [
                'key' => 'tags_cast_looks',
                'table' => 'tags_cast_looks',
                'title' => 'キャストタグ: ルックス・属性',
                'description' => 'キャストプロフィールで使う見た目・属性タグです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: スレンダー'],
                ],
            ],
            [
                'key' => 'tags_cast_personality',
                'table' => 'tags_cast_personality',
                'title' => 'キャストタグ: 性格・タイプ',
                'description' => 'キャストプロフィールで使う性格タグです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 明るい'],
                ],
            ],
            [
                'key' => 'tags_salary',
                'table' => 'tags_salary',
                'title' => '店舗タグ: 給与・待遇',
                'description' => '求人の給与・待遇タグです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 交通費支給'],
                ],
            ],
            [
                'key' => 'tags_howto',
                'table' => 'tags_howto',
                'title' => '店舗タグ: 働き方',
                'description' => '求人の働き方タグです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 週1からOK'],
                ],
            ],
            [
                'key' => 'tags_merit',
                'table' => 'tags_merit',
                'title' => '店舗タグ: メリット・待遇',
                'description' => '求人のメリット・待遇タグです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 送り有り'],
                ],
            ],
            [
                'key' => 'tags_feature',
                'table' => 'tags_feature',
                'title' => '店舗タグ: 店舗特徴',
                'description' => '求人の店舗特徴タグです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 未経験'],
                ],
            ],
            [
                'key' => 'tags_facility',
                'table' => 'tags_facility',
                'title' => '店舗タグ: 設備',
                'description' => '求人の設備タグです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 駐車場有り'],
                ],
            ],
            [
                'key' => 'tags_atmosphere',
                'table' => 'tags_atmosphere',
                'title' => '店舗タグ: お店の雰囲気',
                'description' => '求人のお店の雰囲気タグです。',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: アットホーム'],
                ],
            ],
        ];
    }

    private function fetchCatalogRecords(array $catalog): Collection
    {
        if (!Schema::hasTable($catalog['table'])) {
            return collect();
        }

        $query = DB::table($catalog['table'])->select(
            'id',
            DB::raw($this->nameExpressionFor($catalog['key']) . ' as name'),
            'created_at'
        );

        if ($this->hasColumn($catalog['table'], 'directory')) {
            $query->addSelect('directory');
        }

        if ($this->hasColumn($catalog['table'], 'del_flg')) {
            $query->addSelect(DB::raw('CASE WHEN del_flg = 0 THEN 1 ELSE 0 END as is_active'));
        } elseif ($this->hasColumn($catalog['table'], 'is_active')) {
            $query->addSelect('is_active');
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    private function fetchNgWords(): Collection
    {
        if (!Schema::hasTable('ng_words')) {
            return collect();
        }

        $wordColumn = $this->ngWordColumn();
        $query = DB::table('ng_words')
            ->select('id', DB::raw($wordColumn . ' as word'), 'created_at');

        if ($this->hasColumn('ng_words', 'is_active')) {
            $query->addSelect('is_active');
        } else {
            $query->addSelect(DB::raw('1 as is_active'));
        }

        return $query
            ->orderBy('word')
            ->orderByDesc('id')
            ->get();
    }

    private function fetchSimpleOptions(string $table): Collection
    {
        if (!Schema::hasTable($table)) {
            return collect();
        }

        return DB::table($table)
            ->select('id', 'name')
            ->orderBy('id')
            ->get();
    }

    private function reviewContentColumn(): string
    {
        return $this->hasColumn('review_contents', 'content') ? 'content' : 'name';
    }

    private function ngWordColumn(): string
    {
        return $this->hasColumn('ng_words', 'word') ? 'word' : 'content';
    }

    private function nameExpressionFor(string $catalogKey): string
    {
        return match ($catalogKey) {
            'review_contents' => $this->reviewContentColumn(),
            default => 'name',
        };
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }
}
