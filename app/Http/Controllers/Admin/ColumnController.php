<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ColumnController extends Controller
{
    /**
     * コラム管理一覧
     *
     * 今はダミー一覧のみを表示し、後から ColumnRepository を用いて実データ化する。
     */
    public function index()
    {
        $mockColumns = [
            [
                'id' => 1,
                'title' => 'サンプルコラム 1',
                'category' => '運営ノウハウ',
                'status' => '公開',
                'posted_at' => now()->subDays(3),
            ],
            [
                'id' => 2,
                'title' => 'サンプルコラム 2',
                'category' => '採用ノウハウ',
                'status' => '下書き',
                'posted_at' => null,
            ],
        ];

        return view('admin.column.index', [
            'columns' => $mockColumns,
        ]);
    }
}

