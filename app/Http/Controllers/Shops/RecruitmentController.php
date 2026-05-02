<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use App\Services\AdminMasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecruitmentController extends Controller
{
    public function __construct(private readonly AdminMasterService $adminMasterService)
    {
    }

    /** 採用ステータスラベル（shop_job_applications.status） */
    private const APPLICATION_STATUS_LABELS = [
        1 => '書類選考中',
        2 => '面談設定済',
        3 => '面談予定',
        4 => '採用',
        5 => '不採用',
    ];

    /**
     * 求人ステータス一覧画面
     */
    public function status()
    {
        $shopId = $this->currentShopId();
        $recruitData = $this->getRecruitData($shopId);
        $numericShopId = $this->toNumericShopId($shopId);
        $applications = $this->getApplicationsForShop($shopId);

        return view('shops.recruit.status', [
            'pageId' => 'job_status',
            'recruit' => $recruitData['recruit'],
            'applications' => $applications,
            'applicationStatusLabels' => self::APPLICATION_STATUS_LABELS,
            'previewRoute' => route('shop.jobdescription'),
            'publicPreviewRoute' => $numericShopId ? route('share.recruit.show', ['id' => $numericShopId]) : null,
            'shareUrl' => $numericShopId ? route('share.recruit.show', ['id' => $numericShopId]) : null,
        ]);
    }

    /**
     * 自店舗の求人への応募一覧（マッチしているキャスト）
     */
    private function getApplicationsForShop(string $shopId): array
    {
        $jobIds = DB::table('shop_jobs')
            ->where('shop_id', $shopId)
            ->pluck('id');
        if ($jobIds->isEmpty()) {
            return [];
        }

        return DB::table('shop_job_applications')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->join('cast_profiles', 'shop_job_applications.cast_id', '=', 'cast_profiles.cast_id')
            ->whereIn('shop_job_applications.shop_job_id', $jobIds)
            ->select(
                'shop_job_applications.id',
                'shop_job_applications.cast_id',
                'shop_job_applications.status',
                'shop_job_applications.result_date',
                'shop_job_applications.real_start_date',
                'shop_job_applications.created_at',
                'shop_job_applications.updated_at',
                'cast_profiles.nickname',
                'cast_profiles.name'
            )
            ->orderBy('shop_job_applications.status')
            ->orderByDesc('shop_job_applications.updated_at')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'cast_id' => $row->cast_id,
                    'status' => (int) $row->status,
                    'status_label' => self::APPLICATION_STATUS_LABELS[(int) $row->status] ?? '未設定',
                    'result_date' => $row->result_date ? date('Y/m/d', strtotime($row->result_date)) : null,
                    'real_start_date' => $row->real_start_date ? date('Y/m/d', strtotime($row->real_start_date)) : null,
                    'created_at' => $row->created_at ? date('Y/m/d', strtotime($row->created_at)) : null,
                    'cast_name' => $row->nickname ?: $row->name ?: 'キャスト',
                ];
            })
            ->all();
    }

    /**
     * 求人情報詳細（プレビュー）
     */
    public function show($id = null)
    {
        $shopId = $id ? $this->normalizeShopId($id) : $this->currentShopId();
        $recruitData = $this->getRecruitData($shopId);
        $shareText = trim((string) ($recruitData['recruit']['catch_copy'] ?? $recruitData['recruit']['message'] ?? ''));
        $numericShopId = $this->toNumericShopId($shopId);

        return view('shops.recruit.show', [
            'pageId' => 'job_info', 
            'recruit' => $recruitData['recruit'],
            'shop'   => $recruitData['shop'],
            'shareUrl' => $numericShopId ? route('share.recruit.show', ['id' => $numericShopId]) : null,
            'shareTitle' => (($recruitData['shop']['name'] ?? null) ?: ($recruitData['recruit']['store_name'] ?? '店舗')) . 'の求人情報',
            'shareText' => $shareText !== '' ? mb_strimwidth($shareText, 0, 80, '…') : 'ミセチョクの求人情報です。',
            'isPublicShare' => false,
        ]);
    }

    /**
     * 求人情報編集
     */
    public function edit()
    {
        $type = request()->query('type', 'fulltime'); // fulltime | trial | help
        $shopId = $this->currentShopId();
        $recruitData = $this->getRecruitData($shopId);

        // 編集対象の求人種別に応じて時給などを差し替え
        $recruit = $recruitData['recruit'];
        if ($type === 'trial') {
            $recruit['hourly_wage_regular'] = $recruit['trial_hourly_wage'] ?? null;
        } elseif ($type === 'help') {
            $recruit['hourly_wage_regular'] = $recruit['help_hourly_wage'] ?? null;
        }

        return view('shops.recruit.edit', [
            'pageId' => 'job_edit', 
            'recruit' => $recruit,
            'recruitType' => $type,
            'masters' => $this->adminMasterService->getRecruitmentMasters(),
        ]);
    }

    /**
     * 求人情報更新 (Ajax想定)
     */
    public function update(Request $request) 
    {
        $data = $request->validate([
            'catch_copy' => 'required|string|max:100',
            'message' => 'required|string|max:1000',
            'hourly_wage_regular' => 'required|integer|min:0',
            'trial_hourly_wage' => 'nullable|integer|min:0',
            'help_hourly_wage' => 'nullable|integer|min:0',
            'noruma_reward' => 'nullable|integer|min:0',
            'bonus_condition' => 'nullable|string|max:1000',
            'bonus_total_working_days' => 'nullable|integer|min:0',
            'bonus_total_working_hours' => 'nullable|integer|min:0',
            'bonus_other_conditions' => 'nullable|string|max:1000',
            'salary_text' => 'nullable|string|max:1000',
            'working_hours' => 'required|string|max:255',
            'working_days' => 'required|string|max:255',
            'regular_holiday' => 'nullable|string|max:255',
            'job_content' => 'required|string|max:2000',
            'store_atmosphere' => 'nullable|string|max:2000',
            'qualification' => 'required|string|max:255',
            'salary_tag_ids' => 'nullable|array',
            'salary_tag_ids.*' => 'integer|exists:tags_salary,id',
            'howto_tag_ids' => 'nullable|array',
            'howto_tag_ids.*' => 'integer|exists:tags_shop_working_styles,id',
            'merit_tag_ids' => 'nullable|array',
            'merit_tag_ids.*' => 'integer|exists:tags_shop_benefits,id',
            'feature_tag_ids' => 'nullable|array',
            'feature_tag_ids.*' => 'integer|exists:tags_shop_conditions,id',
            'facility_tag_ids' => 'nullable|array',
            'facility_tag_ids.*' => 'integer|exists:tags_shop_facilities,id',
            'atmosphere_tag_ids' => 'nullable|array',
            'atmosphere_tag_ids.*' => 'integer|exists:tags_shop_atmospheres,id',
        ]);

        $shopId = $this->currentShopId();
        $meta = $this->getRecruitMeta($shopId);
        $currentStatus = $this->getCurrentRecruitStatus($shopId);
        $bonusOther = trim((string) ($request->input('bonus_other_conditions', $data['bonus_condition'] ?? '')));
        $payload = array_merge($meta, [
            'catch_copy' => $data['catch_copy'],
            'message' => $data['message'],
            'bonus_condition' => $bonusOther,
            'bonus_total_working_days' => $request->filled('bonus_total_working_days') ? (int) $request->input('bonus_total_working_days') : null,
            'bonus_total_working_hours' => $request->filled('bonus_total_working_hours') ? (int) $request->input('bonus_total_working_hours') : null,
            'bonus_other_conditions' => $bonusOther,
            'working_days' => $data['working_days'],
            'regular_holiday' => $data['regular_holiday'] ?? '',
            'qualification' => $data['qualification'],
            'tag_ids' => [
                'salary' => array_values(array_map('intval', $request->input('salary_tag_ids', []))),
                'howto' => array_values(array_map('intval', $request->input('howto_tag_ids', []))),
                'merit' => array_values(array_map('intval', $request->input('merit_tag_ids', []))),
                'feature' => array_values(array_map('intval', $request->input('feature_tag_ids', []))),
                'facility' => array_values(array_map('intval', $request->input('facility_tag_ids', []))),
                'atmosphere' => array_values(array_map('intval', $request->input('atmosphere_tag_ids', []))),
            ],
        ]);

        $jobPayload = [
            'shop_id' => $shopId,
            'status' => $currentStatus,
            'hourly_wage_regular' => (string) $data['hourly_wage_regular'],
            'trial_hourly_wage' => $request->filled('trial_hourly_wage') ? (string) $data['trial_hourly_wage'] : null,
            'has_trial' => $request->filled('trial_hourly_wage') ? 1 : 0,
            'help_hourly_wage' => $request->filled('help_hourly_wage') ? (string) $data['help_hourly_wage'] : null,
            'has_help' => $request->boolean('has_help') && $request->filled('help_hourly_wage') ? 1 : 0,
            'noruma_reward' => $request->filled('noruma_reward') ? (string) $data['noruma_reward'] : null,
            'job_description' => $data['job_content'],
            'salary' => $data['salary_text'] ?? '',
            'atmosphere' => $data['store_atmosphere'] ?? '',
            'noruma_cond' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('shop_jobs', 'working_hours')) {
            $jobPayload['working_hours'] = $data['working_hours'];
        }
        if (Schema::hasColumn('shop_jobs', 'working_day')) {
            $jobPayload['working_day'] = $data['working_days'];
        }
        if (Schema::hasColumn('shop_jobs', 'regular_holiday')) {
            $jobPayload['regular_holiday'] = $data['regular_holiday'] ?? '';
        }
        if (Schema::hasColumn('shop_jobs', 'qualification')) {
            $jobPayload['qualification'] = $data['qualification'];
        }

        // 本入求人 (job_type = 1) に対してのみ保存する
        $existing = DB::table('shop_jobs')
            ->where('shop_id', $shopId)
            ->where('job_type', 1)
            ->exists();

        if ($existing) {
            DB::table('shop_jobs')
                ->where('shop_id', $shopId)
                ->where('job_type', 1)
                ->update($jobPayload);
        } else {
            DB::table('shop_jobs')->insert(array_merge($jobPayload, [
                'job_type' => 1,
                'created_at' => now(),
            ]));
        }

        // 店舗タグを中間テーブル shop_tag_relations で同期
        $this->syncShopTags($shopId, 'salary', $request->input('salary_tag_ids', []));
        $this->syncShopTags($shopId, 'howto', $request->input('howto_tag_ids', []));
        $this->syncShopTags($shopId, 'merit', $request->input('merit_tag_ids', []));
        $this->syncShopTags($shopId, 'feature', $request->input('feature_tag_ids', []));
        $this->syncShopTags($shopId, 'facility', $request->input('facility_tag_ids', []));
        $this->syncShopTags($shopId, 'atmosphere', $request->input('atmosphere_tag_ids', []));

        return redirect()
            ->route('shop.recruits.edit')
            ->with('message', '求人情報を保存しました');
    }

    public function toggleStatus(Request $request)
    {
        $shopId = $this->currentShopId();
        $currentStatus = $this->getCurrentRecruitStatus($shopId);
        $nextStatus = $currentStatus === 1 ? 0 : 1;
        $existing = DB::table('shop_jobs')
            ->where('shop_id', $shopId)
            ->where('job_type', 1)
            ->exists();

        if ($existing) {
            DB::table('shop_jobs')
                ->where('shop_id', $shopId)
                ->where('job_type', 1)
                ->update([
                    'status' => $nextStatus,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('shop_jobs')->insert([
                'shop_id' => $shopId,
                'job_type' => 1,
                'status' => $nextStatus,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->back()
            ->with('message', $nextStatus === 1 ? '求人を公開しました' : '求人を非公開にしました');
    }

    private function getRecruitData(string $shopId): array
    {
        // 本入求人 (job_type = 1) をベースとして取得
        $row = DB::table('shops')
            ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('shop_jobs', function ($join) {
                $join->on('shops.id', '=', 'shop_jobs.shop_id')
                     ->where('shop_jobs.job_type', 1);
            })
            ->where('shops.id', $shopId)
            ->select('shops.id', 'shop_profiles.*', 'shop_jobs.*')
            ->first();

        // 体入・ヘルプ用の別レコード（存在しない場合は null）
        $trialRow = DB::table('shop_jobs')
            ->where('shop_id', $shopId)
            ->where('job_type', 2)
            ->first();
        $helpRow = DB::table('shop_jobs')
            ->where('shop_id', $shopId)
            ->where('job_type', 3)
            ->first();

        $industryName = null;
        if (!empty($row?->industry_id)) {
            $industryName = DB::table('industries')
                ->where('id', $row->industry_id)
                ->value('name');
        }
        $shopTagGroups = $this->resolveShopInfoTagGroups($shopId);

        $meta = $this->decodeMeta($row->noruma_cond ?? null);
        $tagMap = $this->resolveRecruitTagNames($meta['tag_ids'] ?? []);
        $subImages = DB::table('shop_images')
            ->where('shop_id', $shopId)
            ->orderByRaw('main_order IS NULL')
            ->orderBy('main_order')
            ->orderBy('id')
            ->pluck('image_path')
            ->map(fn ($path) => $this->assetPathForStored($path))
            ->filter()
            ->values()
            ->all();

        $mainImage = $this->assetPathForStored($row->main_image_path ?? null);
        if (empty($subImages) && $mainImage) {
            $subImages[] = $mainImage;
        }

        $workingHours = Schema::hasColumn('shop_jobs', 'working_hours') ? ($row->working_hours ?? '') : ($meta['working_hours'] ?? '');
        $workingDays = Schema::hasColumn('shop_jobs', 'working_day') ? ($row->working_day ?? '') : ($meta['working_days'] ?? '');
        $regularHoliday = Schema::hasColumn('shop_jobs', 'regular_holiday') ? ($row->regular_holiday ?? '') : ($meta['regular_holiday'] ?? '');
        $qualification = Schema::hasColumn('shop_jobs', 'qualification') ? ($row->qualification ?? '') : ($meta['qualification'] ?? '');

        // 達成条件（編集画面の bonus_* と旧キーの両方を参照）
        $bonusWorkingDaysRaw = $meta['bonus_total_working_days'] ?? $meta['bonus_working_days'] ?? null;
        $bonusWorkingHoursRaw = $meta['bonus_total_working_hours'] ?? $meta['bonus_working_hours'] ?? null;
        $bonusWorkingDays = $bonusWorkingDaysRaw === null || $bonusWorkingDaysRaw === '' ? '' : (string) $bonusWorkingDaysRaw;
        $bonusWorkingHours = $bonusWorkingHoursRaw === null || $bonusWorkingHoursRaw === '' ? '' : (string) $bonusWorkingHoursRaw;
        $bonusExtraCondition = trim((string) ($meta['bonus_other_conditions'] ?? $meta['bonus_condition'] ?? ''));

        return [
            'recruit' => [
                'store_name' => $row->shop_name ?? '店舗',
                'open_date' => !empty($row->opened_on) ? date('Y年n月j日', strtotime($row->opened_on)) : null,
                'address' => trim(implode(' ', array_filter([$row->pref ?? null, $row->city ?? null, $row->addr2 ?? null, $row->addr3 ?? null]))),
                'map_embed_src' => null,
                'nearest_station' => $row->station1 ?? '',
                'hourly_wage_regular' => isset($row->hourly_wage_regular) ? (int) $row->hourly_wage_regular : 0,
                'trial_hourly_wage' =>
                    $trialRow && !empty($trialRow->trial_hourly_wage)
                        ? (int) $trialRow->trial_hourly_wage
                        : null,
                'help_hourly_wage' =>
                    $helpRow && !empty($helpRow->help_hourly_wage)
                        ? (int) $helpRow->help_hourly_wage
                        : null,
                'help_job_content' => $helpRow && Schema::hasColumn('shop_jobs', 'job_description')
                    ? (string) ($helpRow->job_description ?? '')
                    : '',
                'noruma_reward' => isset($row->noruma_reward) ? (int) $row->noruma_reward : 0,
                'bonus_condition' => $bonusExtraCondition,
                'bonus_other_conditions' => $bonusExtraCondition,
                'bonus_total_working_days' => $bonusWorkingDays,
                'bonus_total_working_hours' => $bonusWorkingHours,
                'bonus_working_days' => $bonusWorkingDays,
                'bonus_working_hours' => $bonusWorkingHours,
                'salary_text' => $row->salary ?? '',
                'working_hours' => $workingHours,
                'working_days' => $workingDays,
                'regular_holiday' => $regularHoliday,
                'job_content' => $row->job_description ?? '',
                'store_atmosphere' => $row->atmosphere ?? '',
                'qualification' => $qualification ?: '18歳以上（高校生不可）',
                'catch_copy' => $meta['catch_copy'] ?? ($row->catch ?? ''),
                'message' => $meta['message'] ?? ($row->message ?? ''),
                'selected_benefits' => $tagMap['merit'],
                'store_features' => [
                    '報酬' => $tagMap['salary'],
                    '働き方' => $tagMap['howto'],
                    'メリット' => $tagMap['merit'],
                    '特徴' => $tagMap['feature'],
                    '設備' => $tagMap['facility'],
                    'お店の雰囲気' => $tagMap['atmosphere'],
                ],
                'salary_tag_ids' => $this->normalizeTagIds($meta['tag_ids']['salary'] ?? []),
                'howto_tag_ids' => $this->normalizeTagIds($meta['tag_ids']['howto'] ?? []),
                'merit_tag_ids' => $this->normalizeTagIds($meta['tag_ids']['merit'] ?? []),
                'feature_tag_ids' => $this->normalizeTagIds($meta['tag_ids']['feature'] ?? []),
                'facility_tag_ids' => $this->normalizeTagIds($meta['tag_ids']['facility'] ?? []),
                'atmosphere_tag_ids' => $this->normalizeTagIds($meta['tag_ids']['atmosphere'] ?? []),
                'status' => ((int) ($row->status ?? 1)) === 1 ? 'active' : 'inactive',
                'updated_at' => !empty($row->updated_at) ? date('Y.m.d', strtotime($row->updated_at)) : null,
            ],
            'shop' => [
                'name' => $row->shop_name ?? '店舗',
                'word' => $row->catch ?? '',
                'main_img' => $mainImage,
                'area' => trim(implode(' ', array_filter([$row->pref ?? null, $row->city ?? null]))),
                'concept' => $row->overview ?? '',
                'review_avg' => 0,
                'review_cnt' => 0,
                'sub_images' => $subImages,
                'zip' => $row->zip ?? '',
                'pref' => $row->pref ?? '',
                'city' => $row->city ?? '',
                'addr1' => trim(($row->addr2 ?? '') . ' ' . ($row->addr3 ?? '')),
                'industry_name' => $industryName,
                'nearest_station' => $row->station1 ?? '',
                'tag_groups' => $shopTagGroups,
            ],
        ];
    }

    /**
     * マイページと同様、shop_tag_relations からマスタタグをグループ表示用に取得する。
     *
     * @return array<int, array{label: string, tags: array<int, string>}>
     */
    private function resolveShopInfoTagGroups(string $shopId): array
    {
        $schema = DB::getSchemaBuilder();
        if (!$schema->hasTable('shop_tag_relations')) {
            return [];
        }

        $definitions = [
            ['label' => '給与・支払い', 'types' => ['salary'], 'table' => 'tags_salary'],
            ['label' => '働き方', 'types' => ['howto'], 'table' => 'tags_shop_working_styles'],
            ['label' => '待遇・サポート', 'types' => ['merit'], 'table' => 'tags_shop_benefits'],
            ['label' => '店舗特徴・条件', 'types' => ['feature'], 'table' => 'tags_shop_conditions'],
            ['label' => '設備・空間', 'types' => ['facility'], 'table' => 'tags_shop_facilities'],
            ['label' => 'お店の雰囲気・客層', 'types' => ['atmosphere'], 'table' => 'tags_shop_atmospheres'],
        ];

        $groups = [];
        foreach ($definitions as $def) {
            if (!$schema->hasTable($def['table'])) {
                continue;
            }
            $names = $this->fetchShopTagNamesForRelations($shopId, $def['types'], $def['table']);
            if (!empty($names)) {
                $groups[] = ['label' => $def['label'], 'tags' => $names];
            }
        }

        if ($schema->hasTable('tags_shop_accesses')) {
            $accessNames = $this->fetchShopTagNamesForRelations($shopId, ['access', 'shop_access'], 'tags_shop_accesses');
            if (!empty($accessNames)) {
                $groups[] = ['label' => '通勤・アクセス', 'tags' => $accessNames];
            }
        }

        return $groups;
    }

    private function fetchShopTagNamesForRelations(string $shopId, array $tagTypes, string $masterTable): array
    {
        $tagIds = DB::table('shop_tag_relations')
            ->where('shop_id', $shopId)
            ->whereIn('tag_type', $tagTypes)
            ->pluck('tag_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($tagIds)) {
            return [];
        }

        return DB::table($masterTable)
            ->whereIn('id', $tagIds)
            ->orderBy('id')
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    private function getRecruitMeta(string $shopId): array
    {
        $raw = DB::table('shop_jobs')
            ->where('shop_id', $shopId)
            ->where('job_type', 1)
            ->value('noruma_cond');

        return $this->decodeMeta($raw);
    }

    private function decodeMeta(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function resolveRecruitTagNames(array $tagIds): array
    {
        $tables = [
            'salary'     => 'tags_salary',
            'howto'      => 'tags_shop_working_styles',
            'merit'      => 'tags_shop_benefits',
            'feature'    => 'tags_shop_conditions',
            'facility'   => 'tags_shop_facilities',
            'atmosphere' => 'tags_shop_atmospheres',
        ];

        $resolved = [];
        foreach ($tables as $key => $table) {
            $ids = $this->normalizeTagIds($tagIds[$key] ?? []);
            if (empty($ids) || !Schema::hasTable($table)) {
                $resolved[$key] = [];
                continue;
            }

            $resolved[$key] = DB::table($table)
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->pluck('name')
                ->all();
        }

        return $resolved;
    }

    private function normalizeTagIds(array $ids): array
    {
        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function syncShopTags(string $shopId, string $tagType, array $tagIds): void
    {
        if (!DB::getSchemaBuilder()->hasTable('shop_tag_relations')) {
            return;
        }

        DB::table('shop_tag_relations')
            ->where('shop_id', $shopId)
            ->where('tag_type', $tagType)
            ->delete();

        $rows = collect($tagIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($tagId) => [
                'shop_id'    => $shopId,
                'tag_id'     => $tagId,
                'tag_type'   => $tagType,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if (!empty($rows)) {
            DB::table('shop_tag_relations')->insert($rows);
        }
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

    private function currentShopId(): string
    {
        return (string) auth()->guard('shop')->user()->shop_id;
    }

    private function getCurrentRecruitStatus(string $shopId): int
    {
        $status = DB::table('shop_jobs')
            ->where('shop_id', $shopId)
            ->where('job_type', 1)
            ->value('status');

        return $status === null ? 1 : (int) $status;
    }

    private function normalizeShopId(string|int $value): string
    {
        return str_starts_with((string) $value, 's')
            ? (string) $value
            : 's' . str_pad((string) $value, 8, '0', STR_PAD_LEFT);
    }

    private function toNumericShopId(string $shopId): ?int
    {
        if (!str_starts_with($shopId, 's')) {
            return is_numeric($shopId) ? (int) $shopId : null;
        }

        return (int) ltrim(substr($shopId, 1), '0');
    }
}