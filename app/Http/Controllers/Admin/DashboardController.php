<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * 管理者ダッシュボード
     *
     * ※ 現時点ではダミー情報のみ表示し、後から集計ロジックを差し込める構成にしておく。
     */
    public function index()
    {
        $registrationKpis = [
            [
                'id' => 'cast',
                'title' => '登録キャスト数',
                'value' => '1,452',
                'unit' => '名',
                'trend' => '+12',
                'is_up' => true,
                'icon' => 'fa-users',
            ],
            [
                'id' => 'shop',
                'title' => '登録店舗 (プレミアム)',
                'value' => '215',
                'sub_value' => '48',
                'unit' => '店',
                'trend' => '+3',
                'is_up' => true,
                'icon' => 'fa-building',
            ],
        ];

        $transactionKpis = [
            [
                'id' => 'trx_count',
                'title' => '取引件数 (月)',
                'value' => '4,892',
                'unit' => '件',
                'trend' => '+124',
                'is_up' => true,
                'icon' => 'fa-chart-line',
            ],
            [
                'id' => 'trx_amount',
                'title' => '取引金額 (月)',
                'value' => '18.45',
                'unit' => 'M円',
                'trend' => '+5.2%',
                'is_up' => true,
                'icon' => 'fa-yen-sign',
            ],
        ];

        $chartData = [
            ['month' => '4月', 'cast' => 1200, 'shop' => 180, 'amount' => 12.0, 'count' => 3800],
            ['month' => '5月', 'cast' => 1250, 'shop' => 190, 'amount' => 13.5, 'count' => 4000],
            ['month' => '6月', 'cast' => 1280, 'shop' => 195, 'amount' => 12.8, 'count' => 3900],
            ['month' => '7月', 'cast' => 1320, 'shop' => 200, 'amount' => 15.0, 'count' => 4300],
            ['month' => '8月', 'cast' => 1380, 'shop' => 205, 'amount' => 16.2, 'count' => 4500],
            ['month' => '9月', 'cast' => 1410, 'shop' => 210, 'amount' => 17.5, 'count' => 4700],
            ['month' => '10月', 'cast' => 1452, 'shop' => 215, 'amount' => 18.45, 'count' => 4892],
        ];

        $taskSummary = [
            [
                'id' => 'kyc',
                'title' => '本人確認',
                'count' => 5,
            ],
            [
                'id' => 'doc',
                'title' => '書類審査',
                'count' => 2,
            ],
            [
                'id' => 'deposit',
                'title' => '入金確認',
                'count' => 4,
            ],
            [
                'id' => 'transfer',
                'title' => '振込実行',
                'count' => 8,
            ],
            [
                'id' => 'error',
                'title' => '振込エラー',
                'count' => 1,
            ],
        ];

        $tasks = [
            [
                'id' => 'T-001',
                'category' => '本人確認',
                'target' => '愛華',
                'type' => 'キャスト',
                'status' => '未承認',
                'date' => '今日 10:30',
                'urgency' => 'high',
                'action' => '審査する',
                'cat_id' => 'kyc',
                'amount' => null,
            ],
            [
                'id' => 'T-002',
                'category' => '書類審査',
                'target' => 'CLUB ETERNITY',
                'type' => '店舗',
                'status' => '未承認',
                'date' => '今日 09:15',
                'urgency' => 'normal',
                'action' => '書類確認',
                'cat_id' => 'doc',
                'amount' => null,
            ],
            [
                'id' => 'T-003',
                'category' => '入金照合',
                'target' => 'THE GOLDSTONE',
                'type' => '店舗',
                'status' => '店舗入金確認中',
                'date' => '昨日 18:00',
                'urgency' => 'high',
                'action' => '着金確認',
                'cat_id' => 'deposit',
                'amount' => '¥66,000',
            ],
            [
                'id' => 'T-004',
                'category' => '振込実行',
                'target' => 'みさき',
                'type' => 'キャスト',
                'status' => 'お振込準備中',
                'date' => '昨日 15:45',
                'urgency' => 'normal',
                'action' => '振込実行',
                'cat_id' => 'transfer',
                'amount' => '¥50,000',
            ],
            [
                'id' => 'T-005',
                'category' => '振込エラー',
                'target' => 'リナ',
                'type' => 'キャスト',
                'status' => '口座情報不備',
                'date' => '昨日 11:20',
                'urgency' => 'critical',
                'action' => '口座確認',
                'cat_id' => 'error',
                'amount' => '¥25,000',
            ],
            [
                'id' => 'T-006',
                'category' => '本人確認',
                'target' => 'ユリア',
                'type' => 'キャスト',
                'status' => '未承認',
                'date' => '2日前',
                'urgency' => 'normal',
                'action' => '審査する',
                'cat_id' => 'kyc',
                'amount' => null,
            ],
            [
                'id' => 'T-007',
                'category' => '入金照合',
                'target' => '六本木BAR',
                'type' => '店舗',
                'status' => '店舗入金確認中',
                'date' => '2日前',
                'urgency' => 'normal',
                'action' => '着金確認',
                'cat_id' => 'deposit',
                'amount' => '¥120,000',
            ],
        ];

        return view('admin.dashboard', compact('registrationKpis', 'transactionKpis', 'chartData', 'taskSummary', 'tasks'));
    }
}

