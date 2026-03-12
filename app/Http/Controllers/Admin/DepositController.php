<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DepositController extends Controller
{
    /**
     * 入金・振込管理一覧
     *
     * 現状は UI のみを提供し、後から Repository を注入して実データに差し替えられるようにする。
     */
    public function index()
    {
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
        ]);
    }
}

