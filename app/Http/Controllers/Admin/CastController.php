<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class CastController extends Controller
{
    /**
     * キャスト管理一覧（デモ用）
     */
    public function index()
    {
        $casts = [
            [
                'id' => 1,
                'name' => 'キャストA',
                'fee' => 5000,
                'published_at' => now()->subDays(3),
                'identity_status' => '確認済み',
            ],
            [
                'id' => 2,
                'name' => 'キャストB',
                'fee' => 0,
                'published_at' => null,
                'identity_status' => '未確認',
            ],
        ];

        return view('admin.casts.index', [
            'casts' => $casts,
        ]);
    }
}

