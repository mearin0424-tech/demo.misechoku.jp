<?php

namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;

class MypageController extends Controller
{
    /**
     * キャスト用マイページ（cast ログイン時表示）
     */
    public function index()
    {
        $documents = [
            ['name' => '身分証明書', 'status' => 'submitted'],
            ['name' => '履歴書', 'status' => 'pending'],
        ];

        return view('casts.mypage.index', [
            'pageId'    => 'mypage',
            'documents' => $documents,
        ]);
    }
}
