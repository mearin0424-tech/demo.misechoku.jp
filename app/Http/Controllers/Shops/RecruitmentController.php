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
        // 以下のeditで使用しているデータをリスト形式のモックとして定義
        $recruits = [
            [
                'id' => 1,
                'title' => 'レギュラーキャスト募集',
                'status' => 'active', // ON AIR
                'hourly_wage_regular' => 5000,
                'updated_at' => '2026.01.06',
            ],
            [
                'id' => 2,
                'title' => '週末・体験入店',
                'status' => 'inactive', // PAUSED
                'hourly_wage_regular' => 4000,
                'updated_at' => '2026.01.01',
            ]
        ];

        return view('shops.recruits.status', [
            'pageId' => 'job_status',
            'recruits' => $recruits
        ]);
    }

    /**
     * 求人情報詳細（プレビュー）
     */
    public function show($id = null)
    {
        // editと同じモックデータを使用して詳細を表示
        $recruitData = $this->getMockData();
        return view('shops.recruits.show', [
            'pageId' => 'job_info', 
            'recruit' => $recruitData
        ]);
    }

    /**
     * 求人情報編集
     */
    public function edit()
    {
        $recruitData = $this->getMockData();
        return view('shops.recruits.edit', [
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
     * 共通のモックデータ取得用（プライベートメソッド）
     */
    private function getMockData()
    {
        return [
            'hourly_wage_regular' => 5000,
            'trial_hourly_wage' => 4000,
            'salary_text' => "各種バック完備。指名・同伴手当あり。",
            'working_hours' => "20:00 〜 翌1:00",
            'working_days' => "週1日からOK / シフト制",
            'qualification' => "18歳以上（高校生不可）",
            'catch_copy' => "六本木で一番稼げるお店です！",
            'message' => "未経験の方でも安心の研修制度があります。まずは体験入店からお気軽にどうぞ。",
            'benefits' => ['送迎あり', '日払いOK', '衣装貸出あり', '寮完備'],
            'selected_benefits' => ['送迎あり', '日払いOK']
        ];
    }
}