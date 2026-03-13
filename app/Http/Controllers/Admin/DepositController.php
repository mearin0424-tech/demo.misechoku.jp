<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    /**
     * 入金・振込管理一覧
     *
     * 現状は UI のみを提供し、後から Repository を注入して実データに差し替えられるようにする。
     */
    public function index()
    {
        $step = (int) session('deposit_flow_step', 0);
        $flow = $this->buildDepositFlowState($step);

        // ダミーデータ（後で DepositRepository に差し替え）
        $mockDeposits = [
            [
                'id' => 1,
                'shop_name' => 'サンプル店舗A',
                'cast_name' => 'キャストA',
                'status' => '入金依頼済',
                'requested_at' => now()->subDays(2),
                'amount' => 30000,
            ],
            [
                'id' => 2,
                'shop_name' => 'サンプル店舗B',
                'cast_name' => 'キャストB',
                'status' => '店舗入金済',
                'requested_at' => now()->subDays(5),
                'amount' => 45000,
            ],
        ];

        return view('admin.deposit.index', [
            'deposits' => $mockDeposits,
            'depositFlow' => $flow,
        ]);
    }

    /**
     * 運営側：入金額の承認
     */
    public function approve(Request $request)
    {
        $step = (int) session('deposit_flow_step', 0);
        if ($step >= 2 && $step < 3) {
            session(['deposit_flow_step' => 3]);
        }

        return redirect()->route('admin.deposits.index')->with('status', '入金額を承認しました。店舗からの入金をお待ちください。');
    }

    /**
     * 運営側：キャストへの振込実行
     */
    public function payCast(Request $request)
    {
        $step = (int) session('deposit_flow_step', 0);
        if ($step >= 4 && $step < 5) {
            session(['deposit_flow_step' => 5]);
        }

        return redirect()->route('admin.deposits.index')->with('status', 'キャストへの振込手続きを開始しました。キャストからの入金確認をお待ちください。');
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

