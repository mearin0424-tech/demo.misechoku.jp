<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;

class MypageController extends Controller
{
    /**
     * キャスト用マイページ（プロフィール確認＝shop/castprofileview と同じ内容）
     */
    public function index()
    {
        $cast = $this->getCastMockData();
        $reviewCount = count($cast['reviews']);
        $reviewAvg = $reviewCount > 0
            ? round(array_sum(array_column($cast['reviews'], 'score')) / $reviewCount, 1)
            : 0;
        // プロフィール画面にはレビュー本文を出さず、★カードから一覧へ遷移
        $castForProfile = $cast;
        $castForProfile['reviews'] = [];
        return view('casts.mypage.index', [
            'pageId'       => 'mypage',
            'cast'         => $castForProfile,
            'isOwn'        => true,
            'review_avg'   => $reviewAvg,
            'review_count' => $reviewCount,
        ]);
    }

    /**
     * 採用状況
     */
    public function employment()
    {
        return view('casts.mypage.employment', [
            'pageId' => 'mypage',
        ]);
    }

    /**
     * 請求・入金管理
     */
    public function payment()
    {
        return view('casts.mypage.payment', [
            'pageId' => 'mypage',
        ]);
    }

    /**
     * レビュー一覧（お店の mypage/reviews と同様）
     */
    public function reviews()
    {
        $cast = $this->getCastMockData();
        $castData = [
            'review_avg'   => 4.5,
            'review_count' => count($cast['reviews']),
        ];
        return view('casts.mypage.reviews', [
            'pageId'    => 'mypage',
            'castData'  => $castData,
            'reviews'   => $cast['reviews'],
        ]);
    }

    /**
     * プロフィール表示用モック（shop/castprofileview と同一構造）
     */
    private function getCastMockData(): array
    {
        $castId = 1;
        $images = [];
        for ($i = 1; $i <= 6; $i++) {
            $images[] = asset("storage/mock/casts/{$castId}-{$i}.png");
        }
        return [
            'id'               => $castId,
            'nickname'         => '愛華',
            'name'             => 'かめわりゆい',
            'age'              => 24,
            'birth_year'       => '1994',
            'birth_month'      => '4',
            'birth_day'        => '24',
            'images'           => $images,
            'img'              => $images[0],
            'is_applied'       => true,
            'is_kept'          => true,
            'like_cnt'         => 12,
            'pref'             => '東京都',
            'city'             => '中央区',
            'height'           => 165,
            'weight'           => 48,
            'bust'             => 85,
            'waist'            => 58,
            'hip'              => 86,
            'pr'               => "はじめまして！楽しくお話しするのが大好きです。\nお酒も少し飲めます！よろしくお願いします。",
            'intro'            => "はじめまして！楽しくお話しするのが大好きです。\nお酒も少し飲めます！よろしくお願いします。",
            'desired_job'      => '',
            'my_field'         => '',
            'my_inner_skills'  => '',
            'shift_hope'       => '週1回出勤',
            'work_time'        => 'morning',
            'work_time_label'  => '朝',
            'current_job'      => "都内でITコンサルタントに従事しております。\nこちらは副業で勤務したいと考えています。",
            'night_work_exp'   => 'none',
            'night_work_label' => '無し',
            'reviews'          => [
                ['score' => 5, 'text' => '大変礼儀正しく、お酒の作り方も完璧でした。'],
                ['score' => 4, 'text' => '笑顔が素敵で、お客様からも好評でした。'],
            ],
        ];
    }
}
