<?php
namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    public function show($id = null) {
        return view('shops.recruits.show', ['pageId' => 'job_info']);
    }

    public function edit() {
        $recruitData = [
            'hourly_wage_regular' => 5000,
            'trial_hourly_wage' => 4000,
            'salary_text' => "各種バック完備。",
            'working_hours' => "20:00 〜 翌1:00",
            'working_days' => "週1日からOK",
            'qualification' => "18歳以上",
            'catch_copy' => "六本木で一番稼げるお店です！",
            'message' => "研修制度あり。",
            'benefits' => ['送迎あり', '日払いOK'],
            'selected_benefits' => ['送迎あり']
        ];
        return view('shops.recruits.edit', ['pageId' => 'job_edit', 'recruit' => $recruitData]);
    }

    // 求人情報更新
    public function update(UpdateRecruitRequest $request) 
    {
        $validated = $request->validated(); // バリデーション済みデータの取得

        // TODO: Recruitment モデル等を使用して DB 更新
        // Recruit::updateOrCreate(['shop_id' => auth()->id()], $validated);

        return response()->json(['success' => true, 'message' => '求人情報を保存しました']);
    }
}