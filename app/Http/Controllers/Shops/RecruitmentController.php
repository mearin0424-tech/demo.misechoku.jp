<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    /**
     * 求人ステータス一覧画面
     */
    public function status()
    {
        $recruits = [
            [
                'id' => 1,
                'title' => 'レギュラーキャスト募集',
                'status' => 'active',
                'hourly_wage_regular' => 5000,
                'updated_at' => '2026.01.06',
            ],
            [
                'id' => 2,
                'title' => '週末・体験入店',
                'status' => 'inactive',
                'hourly_wage_regular' => 4000,
                'updated_at' => '2026.01.01',
            ]
        ];

        // 表示用の求人詳細（お店からのひとこと・本入店/体験/ヘルプの3種別）
        $recruitDetail = $this->getRecruitDetailForStatus();

        return view('shops.recruit.status', [
            'pageId' => 'job_status',
            'recruits' => $recruits,
            'recruitDetail' => $recruitDetail,
        ]);
    }

    /**
     * 求人情報詳細（プレビュー）
     */
    public function show($id = null)
    {
        // editと同じモックデータを使用して詳細を表示
        $recruitData = $this->getMockData();
        // プロファイル画面と同等の店舗情報もあわせて渡し、求人票側でお店の写真・情報を連携表示する
        $shop = $this->getMockShopProfile();

        return view('shops.recruit.show', [
            'pageId' => 'job_info', 
            'recruit' => $recruitData,
            'shop'   => $shop,
        ]);
    }

    /**
     * 求人情報編集
     */
    public function edit()
    {
        $recruitData = $this->getMockData();
        return view('shops.recruit.edit', [
            'pageId' => 'job_edit', 
            'recruit' => $recruitData
        ]);
    }

    /**
     * 求人情報更新 (Ajax想定)
     */
    public function update(Request $request) 
    {
        // TODO: バリデーションとDB更新ロジック
        return response()->json(['success' => true, 'message' => '求人情報を保存しました']);
    }

    /**
     * ステータスページ用の求人詳細（お店からのひとこと・3種別カード）
     */
    private function getRecruitDetailForStatus()
    {
        return [
            'store_message' => "店長MESSAGE 未経験の方でも安心の研修制度があります。まずは体験入店からお気軽にどうぞ。一緒に楽しく働きましょう！",
            'job_types' => [
                'regular' => [
                    'label' => '本入店',
                    'hourly_wage' => 3000,
                    'bonus' => 'ノルマ達成ボーナス報酬',
                    'work_reward' => '5日勤務で 35,000〜200,000円',
                    'daily_hours' => 6,
                    'notes' => '本入店ノルマ達成時は別途ボーナス。交通費支給あり。',
                ],
                'trial' => [
                    'label' => '体験入店',
                    'hourly_wage' => 2500,
                    'bonus' => 'ノルマ達成ボーナス報酬',
                    'work_reward' => '3日勤務で 10,000〜50,000円',
                    'daily_hours' => 4,
                    'notes' => '体験入店ノルマ達成で本入店への道も。まずはお試しから。',
                ],
                'help' => [
                    'label' => 'ヘルプ採用',
                    'hourly_wage' => 3000,
                    'bonus' => 'ノルマ達成ボーナス報酬',
                    'work_reward' => '5,000〜10,000円',
                    'daily_hours' => 4,
                    'notes' => 'ヘルプ求人ノルマ達成時は優遇。単発・短期OK。',
                ],
            ],
        ];
    }

    /**
     * 共通のモックデータ取得用（プレビュー・編集用）
     */
    private function getMockData()
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

    /**
     * 店舗プロフィール画面と同等のモック店舗情報
     * プロファイルと求人票の見た目・内容を揃えるために使用
     */
    private function getMockShopProfile()
    {
        return [
            'name'       => 'Club Luxurious',
            'word'       => '最高級の空間で、最高の出会いを。',
            'main_img'   => asset('storage/mock/shops/out-1.png'),
            'area'       => '東京都港区六本木',
            'concept'    => "六本木駅から徒歩3分。落ち着いた雰囲気の高級ラウンジです。\n選び抜かれたキャストと共に、至福のひとときを提供いたします。",
            'review_avg' => 4.8,
            'review_cnt' => 124,
            'sub_images' => [
                asset('storage/mock/shops/inside-1.png'),
                asset('storage/mock/shops/inside-2.png'),
                asset('storage/mock/shops/inside-3.png'),
            ],
        ];
    }
}