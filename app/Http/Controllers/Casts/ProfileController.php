<?php
namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
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
                'cast_profiles.pref',
                'cast_profiles.city',
                'cast_profiles.pr',
                'cast_profiles.height',
                'cast_profiles.weight',
                'cast_profiles.bust',
                'cast_profiles.waist',
                'cast_profiles.hip',
                'cast_profiles.shift',
                'cast_profiles.profession',
                'cast_profiles.exp',
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
            'pref'           => $row->pref ?? '東京都',
            'city'           => $row->city ?? '中央区',
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
            'pref'           => '東京都',
            'city'           => '中央区',
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
            'pref'         => 'nullable|string|max:50',
            'city'         => 'nullable|string|max:50',
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
                'pref' => $request->input('pref'),
                'city' => $request->input('city'),
                'pr' => $request->input('intro'),
                'height' => $request->filled('height') ? (int) $request->input('height') : null,
                'weight' => $request->filled('weight') ? (int) $request->input('weight') : null,
                'bust' => $request->filled('bust') ? (int) $request->input('bust') : null,
                'waist' => $request->filled('waist') ? (int) $request->input('waist') : null,
                'hip' => $request->filled('hip') ? (int) $request->input('hip') : null,
                'shift' => $this->shiftCode($request->input('shift_hope')),
                'profession' => $request->input('current_job'),
                'exp' => $request->input('night_work_exp') === 'yes' ? 1 : 0,
                'memo' => json_encode([
                    'desired_job' => $request->input('desired_job'),
                    'my_field' => $request->input('my_field'),
                    'my_inner_skills' => $request->input('my_inner_skills'),
                    'shift_hope' => $request->input('shift_hope'),
                    'work_time' => $request->input('work_time'),
                    'current_job' => $request->input('current_job'),
                    'night_work_exp' => $request->input('night_work_exp'),
                ], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->route('cast.profile.edit')
            ->with('message', 'プロフィールを更新しました')
            ->withInput([]);
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
        $castId = $id;
        $images = [];
        for ($i = 1; $i <= 6; $i++) {
            $images[] = asset("storage/mock/casts/{$castId}-{$i}.png");
        }
        $cast = [
            'id'             => $castId,
            'nickname'       => '愛華',
            'name'           => 'かめわりゆい',
            'age'            => 24,
            'birth_year'     => '1994',
            'birth_month'    => '4',
            'birth_day'      => '24',
            'images'         => $images,
            'img'            => $images[0],
            'is_applied'     => true,
            'is_kept'        => true,
            'like_cnt'       => 12,
            'pref'           => '東京都',
            'city'           => '中央区',
            'height'         => 165,
            'weight'         => 48,
            'bust'           => 85,
            'waist'          => 58,
            'hip'            => 86,
            'pr'             => "はじめまして！楽しくお話しするのが大好きです。\nお酒も少し飲めます！よろしくお願いします。",
            'intro'          => "はじめまして！楽しくお話しするのが大好きです。\nお酒も少し飲めます！よろしくお願いします。",
            'desired_job'    => '',
            'my_field'       => '',
            'my_inner_skills'=> '',
            'shift_hope'     => '週1回出勤',
            'work_time'      => 'morning',
            'work_time_label'=> '朝',
            'current_job'    => "都内でITコンサルタントに従事しております。\nこちらは副業で勤務したいと考えています。",
            'night_work_exp' => 'none',
            'night_work_label' => '無し',
            'reviews'        => [
                ['score' => 5, 'text' => '大変礼儀正しく、お酒の作り方も完璧でした。'],
                ['score' => 4, 'text' => '笑顔が素敵で、お客様からも好評でした。'],
            ],
        ];

        return view('casts.profile.show', [
            'pageId' => 'cast_detail',
            'cast'   => $cast
        ]);
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

    private function decodeProfileMemo(?string $memo): array
    {
        if (empty($memo)) {
            return [];
        }

        $decoded = json_decode($memo, true);

        return is_array($decoded) ? $decoded : [];
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
}