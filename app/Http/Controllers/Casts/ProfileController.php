<?php
namespace App\Http\Controllers\Casts;

use App\Consts\CommonConsts;
use App\Http\Controllers\Controller;
use App\Services\AdminMasterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProfileController extends Controller
{
    public function __construct(private readonly AdminMasterService $adminMasterService)
    {
    }

    /**
     * ログイン中キャストのプロフィール編集データ
     */
    private function getEditProfileData(string $castId): array
    {
        $row = DB::table('casts')
            ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->where('casts.id', $castId)
            ->select(
                'cast_profiles.nickname',
                'cast_profiles.name',
                'cast_profiles.birthday',
                'cast_profiles.zip',
                'cast_profiles.pref',
                'cast_profiles.city',
                'cast_profiles.addr1',
                'cast_profiles.pr',
                'cast_profiles.height',
                'cast_profiles.weight',
                'cast_profiles.bust',
                'cast_profiles.waist',
                'cast_profiles.hip',
                'cast_profiles.shift',
                'cast_profiles.profession',
                'cast_profiles.exp',
                Schema::hasColumn('cast_profiles', 'personality_type')
                    ? 'cast_profiles.personality_type'
                    : DB::raw('NULL as personality_type'),
                'cast_profiles.memo'
            )
            ->first();

        if (!$row) {
            return $this->emptyEditProfileData();
        }

        $birthday = $row->birthday ? Carbon::parse($row->birthday) : null;
        $memo = $this->decodeProfileMemo($row->memo ?? null);
        $nightWorkExp = $memo['night_work_exp'] ?? ((int) ($row->exp ?? 0) === 1 ? 'yes' : 'none');

        return [
            'nickname'       => $row->nickname ?? '',
            'name'           => $row->name ?? '',
            'birth_year'     => $birthday ? (string) $birthday->year : (string) date('Y'),
            'birth_month'    => $birthday ? (string) $birthday->month : '1',
            'birth_day'      => $birthday ? (string) $birthday->day : '1',
            'zip'            => $row->zip ?? '',
            'pref'           => $row->pref ?? '東京都',
            'city'           => $row->city ?? '中央区',
            'addr1'          => $row->addr1 ?? '',
            'intro'          => $row->pr ?? '',
            'height'         => $row->height ? (string) $row->height : '',
            'weight'         => $row->weight ? (string) $row->weight : '',
            'bust'           => $row->bust ? (string) $row->bust : '',
            'waist'          => $row->waist ? (string) $row->waist : '',
            'hip'            => $row->hip ? (string) $row->hip : '',
            'desired_job'    => $memo['desired_job'] ?? '',
            'my_field'       => $memo['my_field'] ?? '',
            'my_inner_skills'=> $memo['my_inner_skills'] ?? '',
            'shift_hope'     => $memo['shift_hope'] ?? $this->shiftHopeLabel($row->shift),
            'work_time'      => $memo['work_time'] ?? '',
            'current_job'    => $memo['current_job'] ?? ($row->profession ?? ''),
            'night_work_exp' => $nightWorkExp,
            'industry_ids'   => $this->fetchCastIndustryIds($castId),
            'look_tag_ids'   => collect($memo['look_tag_ids'] ?? [])->map(fn ($id) => (int) $id)->all(),
            'personality_tag_ids' => collect($memo['personality_tag_ids'] ?? [])->map(fn ($id) => (int) $id)->all(),
            'personality_type' => $this->resolvePersonalityType($row->personality_type ?? null, $memo),
        ];
    }

    private function emptyEditProfileData(): array
    {
        return [
            'nickname'       => '',
            'name'           => '',
            'birth_year'     => (string) date('Y'),
            'birth_month'    => '1',
            'birth_day'      => '1',
            'zip'            => '',
            'pref'           => '東京都',
            'city'           => '中央区',
            'addr1'          => '',
            'intro'          => '',
            'height'         => '',
            'weight'         => '',
            'bust'           => '',
            'waist'          => '',
            'hip'            => '',
            'desired_job'    => '',
            'my_field'       => '',
            'my_inner_skills'=> '',
            'shift_hope'     => '週1回出勤',
            'work_time'      => '',
            'current_job'    => '',
            'night_work_exp' => 'none',
            'industry_ids'   => [],
            'look_tag_ids'   => [],
            'personality_tag_ids' => [],
            'personality_type' => '',
        ];
    }

    /**
     * プロフィール編集画面表示（cast/profile/edit または shop/profile/edit から呼ばれる）
     */
    public function edit()
    {
        $data = $this->getEditProfileData($this->currentCastId());
        return view('casts.profile.edit', [
            'pageId'      => 'mypage',
            'profile'     => $data,
            'masters'     => $this->adminMasterService->getCastProfileMasters(),
            'prefOptions' => CommonConsts::PREFS,
            'updateRoute' => 'cast.profile.update',
            'editRoute'   => 'cast.profile.edit',
        ]);
    }

    /**
     * プロフィール更新（モック：保存せず成功のみ返す。全項目を受け取り将来のDB用）
     */
    public function update(Request $request)
    {
        $request->validate([
            'nickname'     => 'required|string|max:100',
            'name'         => 'nullable|string|max:100',
            'birth_year'   => 'required|string|max:4',
            'birth_month'  => 'required|string|max:2',
            'birth_day'    => 'required|string|max:2',
            'zip'          => ['nullable', 'regex:/^\d{3}-?\d{4}$/'],
            'pref'         => 'nullable|string|max:50',
            'city'         => 'nullable|string|max:50',
            'addr1'        => 'nullable|string|max:100',
            'intro'        => 'nullable|string',
            'height'       => 'nullable|string|max:10',
            'weight'       => 'nullable|string|max:10',
            'bust'         => 'nullable|string|max:10',
            'waist'        => 'nullable|string|max:10',
            'hip'          => 'nullable|string|max:10',
            'desired_job'  => 'nullable|string|max:255',
            'my_field'     => 'nullable|string|max:255',
            'my_inner_skills' => 'nullable|string|max:500',
            'shift_hope'   => 'nullable|string|max:50',
            'work_time'    => 'nullable|string|max:20',
            'current_job'  => 'nullable|string',
            'night_work_exp' => 'nullable|string|max:20',
            'industry_ids' => 'nullable|array',
            'industry_ids.*' => 'integer|exists:industries,id',
            'look_tag_ids' => 'nullable|array',
            'look_tag_ids.*' => 'integer|exists:tags_cast_looks,id',
            'personality_tag_ids' => 'nullable|array',
            'personality_tag_ids.*' => 'integer|exists:tags_cast_personality,id',
        ], [
            'zip.regex' => '郵便番号は 7 桁、または 123-4567 形式で入力してください。',
        ]);

        $castId = $this->currentCastId();

        if (!checkdate(
            (int) $request->input('birth_month'),
            (int) $request->input('birth_day'),
            (int) $request->input('birth_year')
        )) {
            return redirect()->back()
                ->withErrors(['birth_day' => '生年月日を正しく入力してください。'])
                ->withInput();
        }

        $imageCount = (int) DB::table('cast_images')
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->count();

        if ($imageCount < 1) {
            return redirect()->back()
                ->withErrors(['images' => 'ホーム表示用の画像を1枚以上登録してください。'])
                ->withInput();
        }

        $memo = $this->decodeProfileMemo(DB::table('cast_profiles')->where('cast_id', $castId)->value('memo'));

        DB::table('cast_profiles')->updateOrInsert(
            ['cast_id' => $castId],
            [
                'nickname' => $request->input('nickname'),
                'name' => $request->input('name'),
                'birthday' => sprintf(
                    '%04d-%02d-%02d',
                    (int) $request->input('birth_year'),
                    (int) $request->input('birth_month'),
                    (int) $request->input('birth_day')
                ),
                'zip' => $this->normalizeZip($request->input('zip')),
                'pref' => $request->input('pref'),
                'city' => $request->input('city'),
                'addr1' => $request->input('addr1'),
                'pr' => $request->input('intro'),
                'height' => $request->filled('height') ? (int) $request->input('height') : null,
                'weight' => $request->filled('weight') ? (int) $request->input('weight') : null,
                'bust' => $request->filled('bust') ? (int) $request->input('bust') : null,
                'waist' => $request->filled('waist') ? (int) $request->input('waist') : null,
                'hip' => $request->filled('hip') ? (int) $request->input('hip') : null,
                'shift' => $this->shiftCode($request->input('shift_hope')),
                'profession' => $request->input('current_job'),
                'exp' => $request->input('night_work_exp') === 'yes' ? 1 : 0,
                'memo' => json_encode(array_merge($memo, [
                    'desired_job' => $request->input('desired_job'),
                    'my_field' => $request->input('my_field'),
                    'my_inner_skills' => $request->input('my_inner_skills'),
                    'shift_hope' => $request->input('shift_hope'),
                    'work_time' => $request->input('work_time'),
                    'current_job' => $request->input('current_job'),
                    'night_work_exp' => $request->input('night_work_exp'),
                    'look_tag_ids' => array_values(array_map('intval', $request->input('look_tag_ids', []))),
                    'personality_tag_ids' => array_values(array_map('intval', $request->input('personality_tag_ids', []))),
                ]), JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->syncCastIndustries($castId, $request->input('industry_ids', []));

        return redirect()->route('cast.profile.edit')
            ->with('message', 'プロフィールを更新しました')
            ->withInput([]);
    }

    public function updatePersonalityType(Request $request)
    {
        $validated = $request->validate([
            'personality_type' => ['required', 'regex:/^[LF][CP][IO][HR]$/'],
        ]);

        $castId = $this->currentCastId();
        $existingMemo = DB::table('cast_profiles')
            ->where('cast_id', $castId)
            ->value('memo');

        $memo = $this->decodeProfileMemo($existingMemo);
        $memo['personality_type'] = $validated['personality_type'];

        $payload = [
            'memo' => json_encode($memo, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('cast_profiles', 'personality_type')) {
            $payload['personality_type'] = $validated['personality_type'];
        }

        $exists = DB::table('cast_profiles')
            ->where('cast_id', $castId)
            ->exists();

        if ($exists) {
            DB::table('cast_profiles')
                ->where('cast_id', $castId)
                ->update($payload);
        } else {
            $payload['cast_id'] = $castId;
            $payload['created_at'] = now();
            DB::table('cast_profiles')->insert($payload);
        }

        return response()->json([
            'success' => true,
            'personality_type' => $validated['personality_type'],
        ]);
    }

    /**
     * プロフィール詳細表示
     * - cast/* から呼ばれた場合: お店のプロフィール（キャストがお店を閲覧）
     * - shop/* から呼ばれた場合: キャストのプロフィール（お店がキャストを閲覧）
     */
    public function show($id = null) {
        $id = $id ?? 1;

        if (request()->is('cast/*')) {
            // キャスト側 → お店の情報を表示
            $shop = $this->getShopMock($id);
            return view('shops.profile.show', [
                'pageId' => 'shop_info',
                'shop'   => $shop,
                'isOwn'  => false,
            ]);
        }

        // お店側 → キャストの情報を表示
        return view('casts.profile.show', $this->buildCastProfileViewData((string) $id, false));
    }

    /**
     * SNS共有用の公開キャストプロフィール
     */
    public function publicShow(string $id)
    {
        return view('casts.profile.show', $this->buildCastProfileViewData($id, true));
    }

    /** お店モック（キャストがお店を閲覧する用） */
    private function getShopMock(int $id): array
    {
        $shops = [
            1 => ['name' => 'CLUB ETERNITY', 'word' => '最高の一夜を。', 'main_img' => asset('storage/mock/shops/out-1.png'), 'area' => '東京都港区六本木', 'concept' => "六本木駅から徒歩3分。\n急募・即日払い対応です。", 'review_avg' => 4.5, 'review_cnt' => 80, 'sub_images' => [asset('storage/mock/shops/inside-1.png'), asset('storage/mock/shops/inside-2.png'), asset('storage/mock/shops/inside-3.png')]],
            2 => ['name' => 'THE GOLDSTONE', 'word' => '週末イベント大募集！', 'main_img' => asset('storage/mock/shops/out-2.png'), 'area' => '東京都中央区', 'concept' => "ノルマなし・送りあり。\n働きやすい環境です。", 'review_avg' => 4.8, 'review_cnt' => 124, 'sub_images' => [asset('storage/mock/shops/inside-1.png'), asset('storage/mock/shops/inside-2.png'), asset('storage/mock/shops/inside-3.png')]],
            3 => ['name' => 'Club Luxurious', 'word' => '最高級の空間で、最高の出会いを。', 'main_img' => asset('storage/mock/shops/out-1.png'), 'area' => '東京都港区六本木', 'concept' => "六本木駅から徒歩3分。落ち着いた雰囲気の高級ラウンジです。\n選び抜かれたキャストと共に、至福のひとときを提供いたします。", 'review_avg' => 4.8, 'review_cnt' => 124, 'sub_images' => [asset('storage/mock/shops/inside-1.png'), asset('storage/mock/shops/inside-2.png'), asset('storage/mock/shops/inside-3.png')]],
            4 => ['name' => 'BAR STELLA', 'word' => '落ち着いた大人の空間', 'main_img' => asset('storage/mock/shops/out-2.png'), 'area' => '東京都渋谷区', 'concept' => "カジュアルな雰囲気でリラックスして働けます。", 'review_avg' => 4.3, 'review_cnt' => 56, 'sub_images' => [asset('storage/mock/shops/inside-1.png'), asset('storage/mock/shops/inside-2.png'), asset('storage/mock/shops/inside-3.png')]],
        ];
        return $shops[$id] ?? $shops[1];
    }

    private function currentCastId(): string
    {
        return (string) auth()->guard('member')->id();
    }

    private function buildCastDetailData(string $castId): array
    {
        $this->cleanupStaleMainImagePath($castId);

        $row = DB::table('casts')
            ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->where('casts.id', $castId)
            ->select(
                'casts.id',
                'cast_profiles.nickname',
                'cast_profiles.name',
                'cast_profiles.birthday',
                'cast_profiles.pref',
                'cast_profiles.city',
                'cast_profiles.height',
                'cast_profiles.weight',
                'cast_profiles.bust',
                'cast_profiles.waist',
                'cast_profiles.hip',
                'cast_profiles.shift',
                'cast_profiles.profession',
                'cast_profiles.exp',
                'cast_profiles.pr',
                Schema::hasColumn('cast_profiles', 'personality_type')
                    ? 'cast_profiles.personality_type'
                    : DB::raw('NULL as personality_type'),
                'cast_profiles.memo'
            )
            ->first();

        if (!$row) {
            return $this->buildFallbackCastDetail($castId);
        }

        $birthday = $row->birthday ? Carbon::parse($row->birthday) : null;
        $memo = $this->decodeProfileMemo($row->memo ?? null);
        $nightWorkExp = $memo['night_work_exp'] ?? ((int) ($row->exp ?? 0) === 1 ? 'yes' : 'none');

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

        $reviewRows = DB::table('reviews')
            ->leftJoin('review_details', 'reviews.id', '=', 'review_details.review_id')
            ->where('reviews.cast_id', $castId)
            ->groupBy('reviews.id', 'reviews.contents', 'reviews.created_at')
            ->select(
                'reviews.id',
                'reviews.contents',
                DB::raw('AVG(review_details.score) as avg_score')
            )
            ->get();

        $reviews = [];
        foreach ($reviewRows as $review) {
            $reviews[] = [
                'score' => $review->avg_score !== null ? (float) $review->avg_score : 0.0,
                'text' => $review->contents ?? '',
            ];
        }

        return [
            'id' => $castId,
            'nickname' => $row->nickname ?? '',
            'name' => $row->name ?? '',
            'age' => $birthday ? $birthday->age : null,
            'birth_year' => $birthday ? (string) $birthday->year : null,
            'birth_month' => $birthday ? (string) $birthday->month : null,
            'birth_day' => $birthday ? (string) $birthday->day : null,
            'images' => $images,
            'img' => $images[0] ?? asset('assets/images/common/no-image.png'),
            'is_applied' => true,
            'is_kept' => true,
            'like_cnt' => 0,
            'pref' => $row->pref ?? '',
            'city' => $row->city ?? '',
            'height' => $row->height,
            'weight' => $row->weight,
            'bust' => $row->bust,
            'waist' => $row->waist,
            'hip' => $row->hip,
            'pr' => $row->pr ?? '',
            'intro' => $row->pr ?? '',
            'desired_job' => $memo['desired_job'] ?? '',
            'my_field' => $memo['my_field'] ?? '',
            'my_inner_skills' => $memo['my_inner_skills'] ?? '',
            'personality_type' => $this->resolvePersonalityType($row->personality_type ?? null, $memo),
            'shift_hope' => $memo['shift_hope'] ?? $this->shiftHopeLabel($row->shift),
            'work_time' => $memo['work_time'] ?? '',
            'work_time_label' => $this->workTimeLabel($memo['work_time'] ?? ''),
            'current_job' => $memo['current_job'] ?? ($row->profession ?? ''),
            'night_work_exp' => $nightWorkExp,
            'night_work_label' => $nightWorkExp === 'yes' ? '有り' : '無し',
            'reviews' => $reviews,
        ];
    }

    private function buildCastProfileViewData(string $castId, bool $isPublicShare): array
    {
        abort_unless($this->castExists($castId), 404);

        $cast = $this->buildCastDetailData($castId);
        $displayName = trim((string) ($cast['nickname'] ?? $cast['name'] ?? 'キャスト'));
        $shareText = trim((string) ($cast['intro'] ?? $cast['pr'] ?? ''));

        if ($shareText === '') {
            $shareText = 'ミセチョクのキャストプロフィールです。';
        }

        return [
            'pageId' => 'cast_detail',
            'cast' => $cast,
            'isPublicShare' => $isPublicShare,
            'showInteractionActions' => !$isPublicShare,
            'shareUrl' => route('share.cast.show', ['id' => $castId]),
            'shareTitle' => $displayName . 'のプロフィール',
            'shareText' => mb_strimwidth($shareText, 0, 80, '…'),
        ];
    }

    private function buildFallbackCastDetail(string $castId): array
    {
        $images = [asset('assets/images/common/no-image.png')];

        return [
            'id' => $castId,
            'nickname' => 'ゲスト',
            'name' => '',
            'age' => null,
            'birth_year' => null,
            'birth_month' => null,
            'birth_day' => null,
            'images' => $images,
            'img' => $images[0],
            'is_applied' => false,
            'is_kept' => false,
            'like_cnt' => 0,
            'pref' => '',
            'city' => '',
            'height' => null,
            'weight' => null,
            'bust' => null,
            'waist' => null,
            'hip' => null,
            'pr' => '',
            'intro' => '',
            'desired_job' => '',
            'my_field' => '',
            'my_inner_skills' => '',
            'personality_type' => '',
            'shift_hope' => '',
            'work_time' => '',
            'work_time_label' => '',
            'current_job' => '',
            'night_work_exp' => 'none',
            'night_work_label' => '無し',
            'reviews' => [],
        ];
    }

    private function cleanupStaleMainImagePath(string $castId): void
    {
        $hasImages = DB::table('cast_images')
            ->where('cast_id', $castId)
            ->where('type', 1)
            ->exists();

        if (!$hasImages) {
            DB::table('cast_profiles')
                ->where('cast_id', $castId)
                ->whereNotNull('main_image_path')
                ->update([
                    'main_image_path' => null,
                    'updated_at' => now(),
                ]);
        }
    }

    private function castExists(string $castId): bool
    {
        return DB::table('casts')
            ->where('id', $castId)
            ->exists();
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

    private function decodeProfileMemo(?string $memo): array
    {
        if (empty($memo)) {
            return [];
        }

        $decoded = json_decode($memo, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function resolvePersonalityType(?string $columnType, array $memo): string
    {
        $type = $columnType ?? ($memo['personality_type'] ?? '');

        return is_string($type) && preg_match('/^[LF][CP][IO][HR]$/', $type) ? $type : '';
    }

    private function fetchCastIndustryIds(string $castId): array
    {
        if (!DB::getSchemaBuilder()->hasTable('cast_industry')) {
            return [];
        }

        return DB::table('cast_industry')
            ->where('cast_id', $castId)
            ->orderBy('industry_id')
            ->pluck('industry_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function syncCastIndustries(string $castId, array $industryIds): void
    {
        if (!DB::getSchemaBuilder()->hasTable('cast_industry')) {
            return;
        }

        DB::table('cast_industry')->where('cast_id', $castId)->delete();

        $rows = collect($industryIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($industryId) => [
                'cast_id' => $castId,
                'industry_id' => $industryId,
            ])
            ->all();

        if (!empty($rows)) {
            DB::table('cast_industry')->insert($rows);
        }
    }

    private function shiftHopeLabel($shift): string
    {
        return match ((int) ($shift ?? 0)) {
            1 => '週1回出勤',
            2 => '週2回出勤',
            3 => '週3回以上',
            default => '週1回出勤',
        };
    }

    private function shiftCode(?string $shiftHope): ?int
    {
        return match ($shiftHope) {
            '週1回出勤' => 1,
            '週2回出勤' => 2,
            '週3回以上' => 3,
            default => null,
        };
    }

    private function workTimeLabel(string $workTime): string
    {
        return match ($workTime) {
            'morning' => '朝',
            'day_night' => '昼or夜',
            default => '',
        };
    }

    private function normalizeZip(?string $zip): ?string
    {
        if ($zip === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $zip);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (strlen($digits) !== 7) {
            return trim($zip);
        }

        return substr($digits, 0, 3) . '-' . substr($digits, 3);
    }
}