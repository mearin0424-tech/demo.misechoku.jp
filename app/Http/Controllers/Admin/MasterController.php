<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class MasterController extends Controller
{
    /**
     * マスタ設定 管理トップ
     *
     * レビュー項目・検索タグなどへのリンク集をまとめる。
     */
    public function index()
    {
        return view('admin.master.index');
    }
}

