<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class InquiryController extends Controller
{
    /**
     * 問い合わせ管理一覧
     *
     * 実際の問い合わせテーブル定義が未確定のため、ひとまずダミー表示のみ行う。
     */
    public function index()
    {
        $mockInquiries = [
            [
                'id' => 1,
                'from_type' => '店舗',
                'from_name' => 'サンプル店舗A',
                'subject' => '請求内容の確認について',
                'status' => '未対応',
                'created_at' => now()->subDay(),
            ],
            [
                'id' => 2,
                'from_type' => 'キャスト',
                'from_name' => 'キャストB',
                'subject' => 'ログインできない',
                'status' => '対応中',
                'created_at' => now()->subDays(2),
            ],
        ];

        return view('admin.inquiry.index', [
            'inquiries' => $mockInquiries,
        ]);
    }
}

