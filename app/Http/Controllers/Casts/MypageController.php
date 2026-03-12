<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MypageController extends Controller
{
    /**
     * キャスト用マイページ（プロフィール確認＝shop/castprofileview と同じ内容）
     */
    public function index()
    {
        // TODO: 将来的にはログイン中キャストIDを利用する
        $cast = $this->getCastFromDatabase('c00000001');
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
        $cast = $this->getCastFromDatabase('c00000001');
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
     * キャスト・プロフィール・レビューをDBから取得し、画面用配列に整形
     */
    private function getCastFromDatabase(string $castId): array
    {
        $castRow = DB::table('casts')
            ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->where('casts.id', $castId)
            ->select(
                'casts.id',
                'casts.email',
                'casts.status',
                'casts.identity_status',
                'casts.last_login_at',
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
                'cast_profiles.pr'
            )
            ->first();

        if (!$castRow) {
            // データ不在時は空のモック相当を返す
            return $this->buildEmptyCast();
        }

        $birthday = $castRow->birthday ? Carbon::parse($castRow->birthday) : null;
        $age = $birthday ? $birthday->age : null;

        // 画像は当面モックのストレージを利用
        $images = [];
        $numericId = 1;
        for ($i = 1; $i <= 6; $i++) {
            $images[] = asset("storage/mock/casts/{$numericId}-{$i}.png");
        }

        // レビュー（レビュー本文＋平均スコア）
        $reviewRows = DB::table('reviews')
            ->leftJoin('review_details', 'reviews.id', '=', 'review_details.review_id')
            ->where('reviews.cast_id', $castId)
            ->groupBy('reviews.id', 'reviews.contents', 'reviews.created_at')
            ->select(
                'reviews.id',
                'reviews.contents',
                'reviews.created_at',
                DB::raw('AVG(review_details.score) as avg_score')
            )
            ->get();

        $reviews = [];
        foreach ($reviewRows as $r) {
            $reviews[] = [
                'score' => $r->avg_score !== null ? (float) $r->avg_score : 0.0,
                'text'  => $r->contents ?? '',
            ];
        }

        return [
            'id'               => $castRow->id,
            'nickname'         => $castRow->nickname ?? '',
            'name'             => $castRow->name ?? '',
            'age'              => $age,
            'birth_year'       => $birthday ? (string) $birthday->year : null,
            'birth_month'      => $birthday ? (string) $birthday->month : null,
            'birth_day'        => $birthday ? (string) $birthday->day : null,
            'images'           => $images,
            'img'              => $images[0] ?? null,
            'is_applied'       => true,
            'is_kept'          => true,
            'like_cnt'         => 0,
            'pref'             => $castRow->pref ?? '',
            'city'             => $castRow->city ?? '',
            'height'           => $castRow->height,
            'weight'           => $castRow->weight,
            'bust'             => $castRow->bust,
            'waist'            => $castRow->waist,
            'hip'              => $castRow->hip,
            'word'             => $castRow->pr ? mb_strimwidth($castRow->pr, 0, 80, '...') : '',
            'pr'               => $castRow->pr ?? '',
            'intro'            => $castRow->pr ?? '',
            'desired_job'      => '',
            'my_field'         => '',
            'my_inner_skills'  => '',
            'personality_type' => '',
            'shift_hope'       => '',
            'work_time'        => '',
            'work_time_label'  => '',
            'current_job'      => '',
            'night_work_exp'   => '',
            'night_work_label' => '',
            'reviews'          => $reviews,
        ];
    }

    private function buildEmptyCast(): array
    {
        $images = [];
        for ($i = 1; $i <= 6; $i++) {
            $images[] = asset("storage/mock/casts/1-{$i}.png");
        }
        return [
            'id'               => null,
            'nickname'         => '',
            'name'             => '',
            'age'              => null,
            'birth_year'       => null,
            'birth_month'      => null,
            'birth_day'        => null,
            'images'           => $images,
            'img'              => $images[0] ?? null,
            'is_applied'       => false,
            'is_kept'          => false,
            'like_cnt'         => 0,
            'pref'             => '',
            'city'             => '',
            'height'           => null,
            'weight'           => null,
            'bust'             => null,
            'waist'            => null,
            'hip'              => null,
            'word'             => '',
            'pr'               => '',
            'intro'            => '',
            'desired_job'      => '',
            'my_field'         => '',
            'my_inner_skills'  => '',
            'personality_type' => '',
            'shift_hope'       => '',
            'work_time'        => '',
            'work_time_label'  => '',
            'current_job'      => '',
            'night_work_exp'   => '',
            'night_work_label' => '',
            'reviews'          => [],
        ];
    }
}
