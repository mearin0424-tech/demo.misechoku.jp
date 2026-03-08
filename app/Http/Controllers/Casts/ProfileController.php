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
     * プロフィール編集画面表示
     */
    public function edit()
    {
        $data = $this->getEditMockData();
        return view('casts.profile.edit', [
            'pageId'   => 'mypage',
            'profile'  => $data,
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
        // モックのため保存処理は行わず、リダイレクト後も入力値を表示
        return redirect()->route('cast.profile.edit')
            ->with('message', 'プロフィールを更新しました')
            ->withInput();
    }

    public function show($id = null) {
        $castId = $id ?? 1;
        // ホームのスワイプと同じ画像パス（1枚目〜6枚目まで。最大6枚登録）
        $images = [];
        for ($i = 1; $i <= 6; $i++) {
            $images[] = asset("storage/mock/casts/{$castId}-{$i}.png");
        }
        // 店舗側から見るキャスト詳細（編集フォームと同じ項目をモックで表示）
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
}