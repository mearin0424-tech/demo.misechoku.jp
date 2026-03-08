<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MypageController extends Controller
{
    public function index()
    {
        // 1. 店舗基本データ（モック）
        $shopData = [
            'shop_name'    => 'Club Luxurious',
            'word'         => '最高級の空間で、最高の出会いを。',
            'review_avg'   => 4.8,
            'review_count' => 124,
            'pref'         => '東京都',
            'city'         => '港区',
            'addr1'        => '六本木 1-2-3',
            'overview'     => "六本木駅から徒歩3分。\n落ち着いた雰囲気の高級ラウンジです。",
            'approval'     => 1 // 承認済みフラグ
        ];

        // 2. ギャラリー画像（モック：id + url で削除API用）
        $subImages = [
            ['id' => 1, 'url' => asset('storage/mock/shops/inside-1.png')],
            ['id' => 2, 'url' => asset('storage/mock/shops/inside-2.png')],
            ['id' => 3, 'url' => asset('storage/mock/shops/inside-3.png')],
            ['id' => 4, 'url' => asset('storage/mock/shops/out-1.png')],
            ['id' => 5, 'url' => asset('storage/mock/shops/out-2.png')],
        ];

        // 3. 書類管理（旧data.phpのロジックをモック化）
        $documents = [
            ['name' => '営業許可証', 'status' => ($shopData['approval'] == 1 ? 'submitted' : 'pending')],
            ['name' => '身分証明書', 'status' => 'pending'],
        ];

        return view('shops.mypage.index', [
            'pageId'    => 'mypage',
            'shopData'  => $shopData,
            'subImages' => $subImages,
            'documents' => $documents
        ]);
    }

    public function payment()
    {
        // 開発用モックデータ
        $invoices = [
            ['id' => 101, 'title' => '2024年12月分 請求', 'amount' => 85000, 'status' => 'paid', 'date' => '2025/01/01'],
            ['id' => 102, 'title' => '2025年1月分 概算', 'amount' => 120000, 'status' => 'pending', 'date' => '2025/02/01'],
        ];

        return view('shops.mypage.payment', [
            'pageId' => 'manage',
            'invoices' => $invoices
        ]);
    }
}