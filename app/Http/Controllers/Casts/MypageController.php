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
        // ギャラリー用：id + url（お店マイページと同じ形式）
        $subImages = [];
        foreach (array_values($cast['images']) as $i => $url) {
            $subImages[] = ['id' => $i + 1, 'url' => is_array($url) ? ($url['url'] ?? '') : $url];
        }
        return view('casts.mypage.index', [
            'pageId'       => 'mypage',
            'cast'         => $castForProfile,
            'isOwn'        => true,
            'review_avg'   => $reviewAvg,
            'review_count' => $reviewCount,
            'subImages'    => $subImages,
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
        $step = (int) session('deposit_flow_step', 0);
        $flow = $this->buildDepositFlowState($step);

        return view('casts.mypage.payment', [
            'pageId' => 'mypage',
            'depositFlow' => $flow,
        ]);
    }

    /**
     * 本人確認画面
     */
    public function identity()
    {
        $status = session('cast_identity_status', 'not_submitted');

        return view('casts.mypage.identity', [
            'pageId' => 'mypage',
            'identityStatus' => $status,
        ]);
    }

    /**
     * 本人確認書類アップロード（デモ用）
     */
    public function uploadIdentity(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:8192',
        ]);

        // 実装ではストレージとDBに保存する想定。デモではステータスのみ更新。
        session(['cast_identity_status' => 'pending']);

        return response()->json([
            'success' => true,
            'message' => '本人確認書類をアップロードしました。運営による確認・承認をお待ちください。',
        ]);
    }

    /**
     * キャスト側：入金申請（ボーナス条件達成後に押す想定）
     */
    public function requestDeposit(\Illuminate\Http\Request $request)
    {
        $step = (int) session('deposit_flow_step', 0);
        if ($step < 1) {
            session(['deposit_flow_step' => 1]);
        }

        return redirect()->route('cast.mypage.payment')->with('status', '入金申請を受け付けました。店舗・運営の確認をお待ちください。');
    }

    /**
     * キャスト側：入金完了確認（最終確認）
     */
    public function confirmDeposit(\Illuminate\Http\Request $request)
    {
        $step = (int) session('deposit_flow_step', 0);
        if ($step >= 5) {
            session(['deposit_flow_step' => 6]);
        }

        return redirect()->route('cast.mypage.payment')->with('status', '入金を確認しました。ありがとうございました。');
    }

    /**
     * 入金フローの現在ステータス（3者分）を組み立てる
     */
    private function buildDepositFlowState(int $step): array
    {
        $map = [
            0 => ['cast' => '未申請',       'shop' => '未稼働',           'admin' => '未稼働'],
            1 => ['cast' => '申請中',       'shop' => '未稼働',           'admin' => '未稼働'],
            2 => ['cast' => '店舗審査中',   'shop' => '店舗審査中',       'admin' => '店舗審査待ち'],
            3 => ['cast' => 'お振込準備中', 'shop' => 'お支払い準備中',   'admin' => '店舗入金依頼中'],
            4 => ['cast' => 'お振込準備中', 'shop' => 'お支払い済み',     'admin' => '店舗入金確認中'],
            5 => ['cast' => 'お振込手続き中', 'shop' => 'お支払い完了', 'admin' => 'キャスト振込済'],
            6 => ['cast' => '完了',         'shop' => '完了',             'admin' => '完了'],
        ];

        return $map[$step] ?? $map[0];
    }

    /**
     * キャスト側の振込先口座情報登録（デモ用）
     */
    public function updateBank(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'bank_name'      => 'required|string|max:100',
            'branch_name'    => 'nullable|string|max:100',
            'account_type'   => 'required|string|max:20',
            'account_number' => 'required|string|max:30',
            'account_name'   => 'required|string|max:100',
        ]);

        return response()->json([
            'success' => true,
            'message' => '口座情報を保存しました。（デモ環境ではDB保存は行っていません）',
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
            'word'             => 'はじめまして！楽しくお話しするのが大好きです。',
            'pr'               => "はじめまして！楽しくお話しするのが大好きです。\nお酒も少し飲めます！よろしくお願いします。",
            'intro'            => "はじめまして！楽しくお話しするのが大好きです。\nお酒も少し飲めます！よろしくお願いします。",
            'desired_job'      => '',
            'my_field'         => 'ナチュラル',
            'my_inner_skills'  => '聞き役・気配り',
            'personality_type' => 'ナチュラル（接客タイプ診断）',
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
