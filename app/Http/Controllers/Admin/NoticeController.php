<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class NoticeController extends Controller
{
    /**
     * お知らせ管理一覧（デモ用）
     */
    public function index()
    {
        $notices = [
            [
                'id' => 1,
                'title' => 'メンテナンスのお知らせ',
                'target' => '全ユーザー',
                'status' => '公開中',
                'published_at' => now()->subDays(1),
            ],
            [
                'id' => 2,
                'title' => '新機能リリース',
                'target' => '店舗',
                'status' => '下書き',
                'published_at' => null,
            ],
        ];

        return view('admin.notices.index', [
            'notices' => $notices,
        ]);
    }
}

