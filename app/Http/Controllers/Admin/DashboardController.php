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
        return view('admin.dashboard');
    }
}

