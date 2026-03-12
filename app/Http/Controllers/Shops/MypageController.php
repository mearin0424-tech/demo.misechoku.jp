<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MypageController extends Controller
{
    public function index()
    {
        // 書類提出ステータス（セッションベースの簡易フロー）
        $docStatus = session('shop_documents_status', [
            'business_license' => 'not_submitted',
            'adult_entertainment_license' => 'not_submitted',
        ]);

        // 1. 店舗基本データ（モック）
        $shopData = [
            'shop_name'    => 'Club Luxurious',
            'word'         => '最高級の空間で、最高の出会いを。',
            'review_avg'   => 4.8,
            'review_count' => 124,
            'pref'         => '東京都',
            'city'         => '港区',
            'addr1'        => '六本木 1-2-3',
            'overview'     => "六本木駅から徒歩3分。\n落ち着いた雰囲気の高級ラウンジです。",
            // 承認フラグ（営業許可証・風営許可証が双方とも承認済みの場合に 1）
            'approval'     => collect($docStatus)->every(fn ($s) => $s === 'approved') ? 1 : 0,
        ];

        // 2. ギャラリー画像（モック：id + url で削除API用）
        $subImages = [
            ['id' => 1, 'url' => asset('storage/mock/shops/inside-1.png')],
            ['id' => 2, 'url' => asset('storage/mock/shops/inside-2.png')],
            ['id' => 3, 'url' => asset('storage/mock/shops/inside-3.png')],
            ['id' => 4, 'url' => asset('storage/mock/shops/out-1.png')],
            ['id' => 5, 'url' => asset('storage/mock/shops/out-2.png')],
        ];

        // 3. 書類管理（営業許可証／風営許可証）
        //    セッションに保存されたステータスから現在の状態を表示する
        $documents = [
            [
                'key'    => 'business_license',
                'name'   => '営業許可証',
                'status' => $docStatus['business_license'] ?? 'not_submitted',
            ],
            [
                'key'    => 'adult_entertainment_license',
                'name'   => '風営許可証',
                'status' => $docStatus['adult_entertainment_license'] ?? 'not_submitted',
            ],
        ];

        $allDocumentsApproved = collect($documents)->every(function ($doc) {
            return $doc['status'] === 'approved';
        });

        return view('shops.mypage.index', [
            'pageId'    => 'mypage',
            'shopData'  => $shopData,
            'subImages' => $subImages,
            'documents' => $documents,
            'allDocumentsApproved' => $allDocumentsApproved,
        ]);
    }

    public function payment()
    {
        // 開発用モックデータ
        $invoices = [
            ['id' => 101, 'title' => '2024年12月分 請求', 'amount' => 85000, 'status' => 'paid', 'date' => '2025/01/01'],
            ['id' => 102, 'title' => '2025年1月分 概算', 'amount' => 120000, 'status' => 'pending', 'date' => '2025/02/01'],
        ];

        return view('shops.mypage.payment', [
            'pageId' => 'manage',
            'invoices' => $invoices
        ]);
    }

    /**
     * 店舗側の振込先口座情報登録（デモ用）
     */
    public function updateBank(Request $request)
    {
        $request->validate([
            'bank_name'      => 'required|string|max:100',
            'branch_name'    => 'nullable|string|max:100',
            'account_type'   => 'required|string|max:20',
            'account_number' => 'required|string|max:30',
            'account_name'   => 'required|string|max:100',
        ]);

        // 本番ではここでログイン中店舗のIDを取得し、BankAccountRepository 経由で保存する想定
        // デモ環境のため、今回は実保存は行わずフロント側からの確認用レスポンスのみ返す
        return response()->json([
            'success' => true,
            'message' => '口座情報を保存しました。（デモ環境ではDB保存は行っていません）',
        ]);
    }

    /**
     * 営業許可証・風営許可証のアップロード
     * ※ 現段階ではモックとしてストレージに保存し、審査・承認は別途運営画面で行う想定
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:business_license,adult_entertainment_license',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:8192',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('public/shops/documents');

            // ステータスを「提出済み（未承認）」に更新
            $type = $request->input('type');
            $statuses = session('shop_documents_status', [
                'business_license' => 'not_submitted',
                'adult_entertainment_license' => 'not_submitted',
            ]);
            if (isset($statuses[$type])) {
                $statuses[$type] = 'pending';
                session(['shop_documents_status' => $statuses]);
            }

            return response()->json([
                'success' => true,
                'message' => '書類をアップロードしました。運営による確認・承認をお待ちください。',
                'type'    => $type,
                'path'    => Storage::url($path),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'ファイルが選択されていません。',
        ], 400);
    }
}