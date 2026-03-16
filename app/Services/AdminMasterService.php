<?php

namespace App\Services;

use App\Models\Master\AtmosphereTag;
use App\Models\Master\CastLookTag;
use App\Models\Master\CastPersonalityTag;
use App\Models\Master\ColumnCategory;
use App\Models\Master\ColumnTag;
use App\Models\Master\FacilityTag;
use App\Models\Master\FeatureTag;
use App\Models\Master\HowtoTag;
use App\Models\Master\Industry;
use App\Models\Master\MeritTag;
use App\Models\Master\NgWord;
use App\Models\Master\ReviewContent;
use App\Models\Master\SalaryTag;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminMasterService
{
    public function getMasterIndexData(?string $selectedCatalogKey = null, ?int $editingRecordId = null, string $selectedSort = 'created_desc'): array
    {
        try {
            $selectedSort = in_array($selectedSort, ['created_desc', 'name_asc'], true) ? $selectedSort : 'created_desc';
            $catalogs = collect($this->catalogDefinitions())
                ->map(function (array $catalog) {
                    $catalog['count'] = $this->countCatalogRecords($catalog);

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
            $selectedCatalog = $selectedCatalogKey
                ? $catalogs->firstWhere('key', $selectedCatalogKey)
                : $catalogs->first();

            if ($selectedCatalog) {
                $selectedCatalog['records'] = $this->fetchCatalogRecords($selectedCatalog, $selectedSort);
                $selectedCatalog['editing_record'] = $editingRecordId
                    ? $selectedCatalog['records']->firstWhere('id', $editingRecordId)
                    : null;
            }

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
                'selectedCatalog' => $selectedCatalog,
                'selectedSort' => $selectedSort,
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
                'selectedCatalog' => null,
                'selectedSort' => $selectedSort,
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

    public function getCatalogRecord(string $key, int $recordId): ?object
    {
        $catalog = $this->getCatalogDefinition($key);

        if (!$catalog || !Schema::hasTable($catalog['table'])) {
            return null;
        }

        return DB::table($catalog['table'])
            ->where('id', $recordId)
            ->first();
    }

    public function updateCatalogRecord(string $key, int $recordId, array $data): void
    {
        $catalog = $this->getCatalogDefinition($key);

        if (!$catalog) {
            return;
        }

        $payload = [];
        foreach ($catalog['fields'] as $field) {
            $payload[$field['column']] = $data[$field['input']] ?? null;
        }

        $payload['updated_at'] = now();

        DB::table($catalog['table'])
            ->where('id', $recordId)
            ->update($payload);
    }

    /**
     * マスタレコードの論理削除
     *
     * - del_flg カラムがあれば del_flg = 1
     * - なければ is_active カラムがあれば is_active = 0
     * - どちらもなければ物理削除
     */
    public function deleteCatalogRecord(string $key, int $recordId): void
    {
        $catalog = $this->getCatalogDefinition($key);

        if (!$catalog || empty($catalog['model'])) {
            return;
        }

        /** @var class-string<\App\Models\Master\BaseMaster> $modelClass */
        $modelClass = $catalog['model'];
        $record = $modelClass::query()->find($recordId);

        if (!$record) {
            return;
        }

        $record->logicalDelete();
    }

    public function getCastProfileMasters(): array
    {
        return [
            'industries' => Industry::query()->active()->orderBy('id')->get(['id', 'name']),
            'looks' => CastLookTag::query()->active()->orderBy('id')->get(['id', 'name']),
            'personalities' => CastPersonalityTag::query()->active()->orderBy('id')->get(['id', 'name']),
        ];
    }

    public function getShopProfileMasters(): array
    {
        return [
            'industries' => Industry::query()->active()->orderBy('id')->get(['id', 'name']),
        ];
    }

    public function getRecruitmentMasters(): array
    {
        return [
            'salary' => SalaryTag::query()->active()->orderBy('id')->get(['id', 'name']),
            'howto' => HowtoTag::query()->active()->orderBy('id')->get(['id', 'name']),
            'merit' => MeritTag::query()->active()->orderBy('id')->get(['id', 'name']),
            'feature' => FeatureTag::query()->active()->orderBy('id')->get(['id', 'name']),
            'facility' => FacilityTag::query()->active()->orderBy('id')->get(['id', 'name']),
            'atmosphere' => AtmosphereTag::query()->active()->orderBy('id')->get(['id', 'name']),
        ];
    }

    private function catalogDefinitions(): array
    {
        return [
            [
                'key' => 'industries',
                'table' => 'industries',
                'model' => Industry::class,
                'title' => '業種マスタ',
                'description' => 'キャバクラ、ラウンジ、ガールズバー など',
                'group' => 'プロフィール系',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => '業種名', 'placeholder' => '例: キャバクラ'],
                ],
            ],
            [
                'key' => 'review_contents',
                'table' => 'review_contents',
                'model' => ReviewContent::class,
                'title' => 'レビュー設問マスタ',
                'description' => '清潔感、スタッフ対応などの評価項目',
                'group' => 'レビュー系',
                'fields' => [
                    ['input' => 'content', 'column' => $this->reviewContentColumn(), 'label' => '設問内容', 'placeholder' => '例: スタッフの対応は親切ですか？'],
                ],
                'uses_del_flg' => $this->hasColumn('review_contents', 'del_flg'),
                'uses_is_active' => $this->hasColumn('review_contents', 'is_active'),
                'uses_sort_order' => $this->hasColumn('review_contents', 'sort_order'),
            ],
            [
                'key' => 'column_categories',
                'table' => 'column_categories',
                'model' => ColumnCategory::class,
                'title' => 'コラムカテゴリマスタ',
                'description' => 'キャバクラ情報、面接対策 など',
                'group' => 'コンテンツ系',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'カテゴリ名', 'placeholder' => '例: 面接対策'],
                    ['input' => 'directory', 'column' => 'directory', 'label' => 'ディレクトリ', 'placeholder' => '例: interview'],
                ],
                'uses_del_flg' => true,
            ],
            [
                'key' => 'tags_cast_looks',
                'table' => 'tags_cast_looks',
                'model' => CastLookTag::class,
                'title' => 'キャストルックスマスタ',
                'description' => 'スレンダー、ギャル、顔出しOK など',
                'group' => 'プロフィール系',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: スレンダー'],
                ],
            ],
            [
                'key' => 'tags_cast_personality',
                'table' => 'tags_cast_personality',
                'model' => CastPersonalityTag::class,
                'title' => 'キャスト性格マスタ',
                'description' => '社交的、お酒飲める人、連絡マメ など',
                'group' => 'プロフィール系',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 明るい'],
                ],
            ],
            [
                'key' => 'tags_salary',
                'table' => 'tags_salary',
                'model' => SalaryTag::class,
                'title' => '給与・各種バックマスタ',
                'description' => '全額日払い、高額時給、売上バック有り など',
                'group' => '求人系',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 交通費支給'],
                ],
            ],
            [
                'key' => 'tags_howto',
                'table' => 'tags_howto',
                'model' => HowtoTag::class,
                'title' => '店舗タグ: 働き方',
                'description' => '求人の働き方タグです。',
                'group' => '求人系',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 週1からOK'],
                ],
            ],
            [
                'key' => 'tags_merit',
                'table' => 'tags_merit',
                'model' => MeritTag::class,
                'title' => '店舗タグ: メリット・待遇',
                'description' => '求人のメリット・待遇タグです。',
                'group' => '求人系',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 送り有り'],
                ],
            ],
            [
                'key' => 'tags_feature',
                'table' => 'tags_feature',
                'model' => FeatureTag::class,
                'title' => '店舗タグ: 店舗特徴',
                'description' => '求人の店舗特徴タグです。',
                'group' => '求人系',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 未経験'],
                ],
            ],
            [
                'key' => 'tags_facility',
                'table' => 'tags_facility',
                'model' => FacilityTag::class,
                'title' => '店舗タグ: 設備',
                'description' => '求人の設備タグです。',
                'group' => '求人系',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: 駐車場有り'],
                ],
            ],
            [
                'key' => 'tags_atmosphere',
                'table' => 'tags_atmosphere',
                'model' => AtmosphereTag::class,
                'title' => '店舗タグ: お店の雰囲気',
                'description' => '求人のお店の雰囲気タグです。',
                'group' => '求人系',
                'fields' => [
                    ['input' => 'name', 'column' => 'name', 'label' => 'タグ名', 'placeholder' => '例: アットホーム'],
                ],
            ],
        ];
    }

    private function countCatalogRecords(array $catalog): int
    {
        $modelClass = $catalog['model'] ?? null;

        if (!$modelClass) {
            return 0;
        }

        /** @var \App\Models\Master\BaseMaster $modelClass */
        return (int) $modelClass::query()->active()->count();
    }

    private function fetchCatalogRecords(array $catalog, string $sort = 'created_desc'): Collection
    {
        $modelClass = $catalog['model'] ?? null;

        if (!$modelClass) {
            return collect();
        }

        /** @var \App\Models\Master\BaseMaster $modelClass */
        $query = $modelClass::query()
            ->active()
            ->select('id', DB::raw($this->nameExpressionFor($catalog['key']) . ' as name'), 'created_at');

        if (Schema::hasColumn($catalog['table'], 'directory')) {
            $query->addSelect('directory');
        }

        if (Schema::hasColumn($catalog['table'], 'del_flg')) {
            $query->addSelect(DB::raw('CASE WHEN del_flg = 0 THEN 1 ELSE 0 END as is_active'));
        } elseif (Schema::hasColumn($catalog['table'], 'is_active')) {
            $query->addSelect('is_active');
        }

        if ($sort === 'name_asc') {
            return $query
                ->orderBy('name')
                ->orderBy('id')
                ->get();
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    private function fetchNgWords(): Collection
    {
        return NgWord::query()
            ->active()
            ->orderBy('word')
            ->orderByDesc('id')
            ->get(['id', 'word', 'is_active', 'created_at']);
    }

    private function reviewContentColumn(): string
    {
        return $this->hasColumn('review_contents', 'content') ? 'content' : 'name';
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
