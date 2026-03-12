<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class NgWordController extends Controller
{
    /**
     * NGワード管理一覧（デモ用）
     */
    public function index()
    {
        $words = [
            ['id' => 1, 'word' => 'NGワード例1', 'created_at' => now()->subDays(1)],
            ['id' => 2, 'word' => 'NGワード例2', 'created_at' => now()->subDays(2)],
        ];

        return view('admin.ngwords.index', [
            'words' => $words,
        ]);
    }
}

