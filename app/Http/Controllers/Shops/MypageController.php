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
        // 開発用モックデータ（請求サマリー）
        $summary = [
            'unpaid_total'   => 120000,
            'next_settlement'=> '2025/02/05',
        ];

        // 開発用モックデータ（請求履歴）
        $invoices = [
            ['id' => 101, 'title' => '2024年12月分 請求', 'amount' => 85000, 'status' => 'paid', 'date' => '2025/01/01'],
            ['id' => 102, 'title' => '2025年1月分 概算', 'amount' => 120000, 'status' => 'pending', 'date' => '2025/02/01'],
        ];

        // やり取り中のキャストの採用ステータス（モック）
        $candidates = [
            [
                'id'            => 1,
                'name'          => '愛華',
                'age'           => 24,
                'job_type'      => '本入店',
                'status_label'  => '面談日調整中',
                'status_tag'    => 'interview_pending',
                'next_step'     => '候補日の返信待ち',
                'interview_at'  => '2025-02-03 20:00',
                'deadline_at'   => '2025-02-10',
                'last_message'  => '来週の水曜か金曜でお願いできますか？',
            ],
            [
                'id'            => 2,
                'name'          => 'みさき',
                'age'           => 22,
                'job_type'      => '体験入店',
                'status_label'  => '面談日確定',
                'status_tag'    => 'interview_fixed',
                'next_step'     => '当日の来店フォロー',
                'interview_at'  => '2025-02-01 21:00',
                'deadline_at'   => '2025-02-15',
                'last_message'  => '当日は19時ごろに一度お電話いたします。',
            ],
            [
                'id'            => 3,
                'name'          => 'Rena',
                'age'           => 26,
                'job_type'      => 'ヘルプ',
                'status_label'  => '入店決定・入金待ち',
                'status_tag'    => 'deposit_pending',
                'next_step'     => '店舗から運営へ入金',
                'interview_at'  => '2025-01-28 20:30',
                'deadline_at'   => '2025-02-07',
                'last_message'  => '本採用ありがとうございます。初出勤楽しみにしています。',
            ],
        ];

        // 面談日・振込期限・入金フローなどを 1 本のカレンダーデータにまとめる（モック）
        $calendarEvents = [
            [
                'date'  => '2025-02-01',
                'time'  => '21:00',
                'type'  => 'interview',
                'actor' => 'shop',
                'label' => 'みさき さん面談（体験入店）',
            ],
            [
                'date'  => '2025-02-03',
                'time'  => '20:00',
                'type'  => 'interview',
                'actor' => 'shop',
                'label' => '愛華 さん面談候補（本入店）',
            ],
            [
                'date'  => '2025-02-05',
                'time'  => null,
                'type'  => 'deadline',
                'actor' => 'shop',
                'label' => 'ミセチョク利用料の決済予定日',
            ],
            [
                'date'  => '2025-02-07',
                'time'  => null,
                'type'  => 'deadline',
                'actor' => 'shop',
                'label' => 'Rena さん入金締切（ヘルプ）',
            ],
            [
                'date'  => '2025-02-08',
                'time'  => null,
                'type'  => 'deposit',
                'actor' => 'admin',
                'label' => '運営 → キャスト振込予定',
            ],
        ];

        $step = (int) session('deposit_flow_step', 0);
        $flow = $this->buildDepositFlowState($step);

        return view('shops.mypage.payment', [
            'pageId' => 'manage',
            'invoices' => $invoices,
             'summary'  => $summary,
             'candidates' => $candidates,
             'calendarEvents' => $calendarEvents,
            'depositFlow' => $flow,
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

    /**
     * 店舗側：ノルマ達成・店舗審査完了
     */
    public function approveDeposit(Request $request)
    {
        $step = (int) session('deposit_flow_step', 0);
        if ($step >= 1 && $step < 2) {
            session(['deposit_flow_step' => 2]);
        }

        return redirect()->route('shop.mypage.payment.index')->with('status', 'ノルマ達成・店舗審査を完了しました。運営の確認をお待ちください。');
    }

    /**
     * 店舗側：運営へ入金完了
     */
    public function payToPlatform(Request $request)
    {
        $step = (int) session('deposit_flow_step', 0);
        if ($step >= 3 && $step < 4) {
            session(['deposit_flow_step' => 4]);
        }

        return redirect()->route('shop.mypage.payment.index')->with('status', '運営へのお振込が完了しました。運営の入金確認をお待ちください。');
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
}