<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    /**
     * 店舗管理一覧（デモ用）
     */
    public function index()
    {
        $shops = [
            [
                'id' => 1,
                'name' => 'サンプル店舗A',
                'plan' => 'スタンダード',
                'fee' => 30000,
                'published_at' => now()->subDays(10),
                'document_status' => '確認済み',
                'job_status' => '公開中',
            ],
            [
                'id' => 2,
                'name' => 'サンプル店舗B',
                'plan' => 'ライト',
                'fee' => 15000,
                'published_at' => now()->subDays(5),
                'document_status' => '未提出',
                'job_status' => '下書き',
            ],
        ];

        return view('admin.shops.index', [
            'shops' => $shops,
        ]);
    }
}

