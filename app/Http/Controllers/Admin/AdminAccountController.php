<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminAccountController extends Controller
{
    /**
     * 運営（管理者）アカウント管理一覧（デモ用）
     */
    public function index()
    {
        $admins = [
            [
                'id' => 1,
                'name' => '管理者A',
                'email' => 'admin-a@example.com',
                'role' => 'スーパー管理者',
                'last_login_at' => now()->subDays(1),
            ],
            [
                'id' => 2,
                'name' => '管理者B',
                'email' => 'admin-b@example.com',
                'role' => 'オペレーター',
                'last_login_at' => now()->subDays(3),
            ],
        ];

        return view('admin.admin_accounts.index', [
            'admins' => $admins,
        ]);
    }
}

