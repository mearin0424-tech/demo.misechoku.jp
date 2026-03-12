<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    /**
     * 請求・振込タスク管理一覧（デモ用）
     */
    public function index()
    {
        $tasks = [
            [
                'id' => 1,
                'type' => '店舗請求',
                'target' => 'サンプル店舗A',
                'amount' => 45000,
                'due_date' => now()->addDays(3),
                'status' => '未対応',
            ],
            [
                'id' => 2,
                'type' => 'キャスト振込',
                'target' => 'キャストB',
                'amount' => 30000,
                'due_date' => now()->addDays(1),
                'status' => '対応中',
            ],
        ];

        return view('admin.tasks.index', [
            'tasks' => $tasks,
        ]);
    }
}

