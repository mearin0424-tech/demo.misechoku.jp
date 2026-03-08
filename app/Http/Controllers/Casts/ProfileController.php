<?php
namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * プロフィール編集画面用モックデータ
     */
    private function getEditMockData(): array
    {
        return [
            'nickname'       => 'Yui',
            'name'           => 'かめわりゆい',
            'birth_year'     => '1994',
            'birth_month'    => '4',
            'birth_day'      => '24',
            'pref'           => '東京都',
            'city'           => '中央区',
            'intro'          => '未経験です！よろしくお願いします！',
            'height'         => '156',
            'weight'         => '44',
            'bust'           => '50',
            'waist'          => '60',
            'hip'            => '70',
            // その他情報
            'desired_job'    => '',
            'my_field'       => '',
            'my_inner_skills'=> '',
            'shift_hope'     => '週1回出勤',
            'work_time'      => 'morning',
            'current_job'    => "都内でITコンサルタントに従事しております。\nこちらは副業で勤務したいと考えています。",
            'night_work_exp' => 'none',
        ];
    }

    /**
     * プロフィール編集画面表示（cast/profile/edit または shop/profile/edit から呼ばれる）
     */
    public function edit()
    {
        $data = $this->getEditMockData();
        $isShop = request()->is('shop/*');
        return view('casts.profile.edit', [
            'pageId'      => 'mypage',
            'profile'     => $data,
            'updateRoute' => $isShop ? 'shop.profile.update' : 'cast.profile.update',
            'editRoute'   => $isShop ? 'shop.profile.edit' : 'cast.profile.edit',
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
        // モックのため保存処理は行わず、リダイレクト先は呼び元（shop/cast）に合わせる
        $editRoute = request()->routeIs('shop.profile.update') ? 'shop.profile.edit' : 'cast.profile.edit';
        return redirect()->route($editRoute)
            ->with('message', 'プロフィールを更新しました')
            ->withInput();
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
}