<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminMockInquiries;

class InquiryController extends Controller
{
    /**
     * 問い合わせ管理一覧
     *
     * 実際の問い合わせテーブル定義が未確定のため、ひとまずダミー表示のみ行う。
     */
    public function index()
    {
        return view('admin.inquiry.index', [
            'inquiries' => AdminMockInquiries::all(),
        ]);
    }
}

