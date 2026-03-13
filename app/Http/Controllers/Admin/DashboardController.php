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
        $kpis = [
            [
                'title' => '登録中のキャスト数',
                'value' => '1,452',
                'unit' => '名',
                'trend' => '+12',
                'trend_label' => '前日比',
                'is_up' => true,
            ],
            [
                'title' => '登録中の店舗数',
                'value' => '215',
                'unit' => '店舗',
                'trend' => '+3',
                'trend_label' => '前日比',
                'is_up' => true,
            ],
            [
                'title' => '今月の売上見込',
                'value' => '18,450,000',
                'unit' => '円',
                'trend' => '+5.2%',
                'trend_label' => '前月比',
                'is_up' => true,
            ],
        ];

        $taskSummary = [
            [
                'id' => 'kyc',
                'title' => '本人確認待ち',
                'count' => 5,
                'icon' => 'fa-user-check',
                'tone' => 'info',
            ],
            [
                'id' => 'doc',
                'title' => '書類審査待ち',
                'count' => 2,
                'icon' => 'fa-file-lines',
                'tone' => 'purple',
            ],
            [
                'id' => 'deposit',
                'title' => '店舗入金確認',
                'count' => 4,
                'icon' => 'fa-wallet',
                'tone' => 'success',
            ],
            [
                'id' => 'transfer',
                'title' => 'キャスト振込',
                'count' => 8,
                'icon' => 'fa-right-left',
                'tone' => 'warning',
            ],
            [
                'id' => 'error',
                'title' => '振込エラー',
                'count' => 1,
                'icon' => 'fa-circle-exclamation',
                'tone' => 'danger',
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
        ];

        return view('admin.dashboard', compact('kpis', 'taskSummary', 'tasks'));
    }
}

