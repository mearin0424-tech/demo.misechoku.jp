<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;

/**
 * キャスト向け：店舗の求人情報表示
 */
class RecruitmentController extends Controller
{
    /**
     * 求人情報詳細（店舗IDで表示）
     */
    public function show(int $id)
    {
        $recruitData = $this->getMockData($id);
        return view('shops.recruit.show', [
            'pageId' => 'job_info',
            'recruit' => $recruitData,
            'forCast' => true,
        ]);
    }

    /**
     * 求人表示用モックデータ（店舗IDに応じて将来はDB取得に差し替え）
     */
    private function getMockData(int $shopId): array
    {
        return [
            'store_name' => 'KKK',
            'open_date' => '1979年11月11日',
            'address' => '東京都豊島区東池袋1-18-1 Hareza池袋20F',
            'map_embed_src' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3238.388244628906!2d139.7106!3d35.7295!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzXCsDQzJzQ2LjIiTiAxMznCsDQyJzM4LjIiRQ!5e0!3m2!1sja!2sjp!4v1640000000000!5m2!1sja!2sjp',
            'nearest_station' => '池袋駅徒歩5分',
            'hourly_wage_regular' => 5000,
            'trial_hourly_wage' => 4000,
            'salary_text' => "各種バック完備。指名・同伴手当あり。ノルマ達成ボーナスあり。",
            'working_hours' => "20:00 〜 翌1:00",
            'working_days' => "週1日からOK / シフト制",
            'regular_holiday' => "不定休（要相談）",
            'job_content' => "接客・ドリンク提供・お客様との会話が主なお仕事です。未経験者も研修でサポートします。",
            'store_atmosphere' => "落ち着いた雰囲気で、初めての方でも働きやすいお店です。スタッフ同士の仲も良く、アットホームです。",
            'qualification' => "18歳以上（高校生不可）",
            'catch_copy' => "六本木で一番稼げるお店です！",
            'message' => "未経験の方でも安心の研修制度があります。まずは体験入店からお気軽にどうぞ。",
            'benefits' => ['送迎あり', '日払いOK', '衣装貸出あり', '寮完備'],
            'selected_benefits' => ['送迎あり', '日払いOK'],
            'store_features' => [
                '報酬' => ['1ヶ月払い', '15日払い', '10日払い', '1週間払い', '翌日払い'],
                '働き方' => ['週1からOK', '短期OK', '1日1h以内'],
                'お店の雰囲気' => ['アットホーム', '少人数', '未経験歓迎'],
                'メリット' => ['レンタル衣装有り', 'ヘアメイク有り', 'ヘアメイク不要'],
                '特徴' => ['未経験', 'シングルマザーOK', '経験者優遇'],
                '設備' => ['駐車場有り', '車通勤OK', '寮有り'],
            ],
        ];
    }
}
