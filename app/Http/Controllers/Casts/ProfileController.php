<?php
namespace App\Http\Controllers\Casts;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show($id = null) {
        // 旧cast_detail.phpのロジックをここに完全統合
        $cast = [
            'name'         => 'アンナ',
            'age'          => 24,
            'img'          => asset('storage/mock/casts/1.png'),
            'is_applied'   => true,
            'is_kept'      => true,
            'keep_cnt'     => 128,
            'height'       => 165,
            'weight'       => 48,
            'b' => 85, 'w' => 58, 'h' => 86,
            'pr'           => "はじめまして！楽しくお話しするのが大好きです。\nお酒も少し飲めます！よろしくお願いします。",
            'reviews'      => [
                ['score' => 5, 'text' => '大変礼儀正しく、お酒の作り方も完璧でした。'],
                ['score' => 4, 'text' => '笑顔が素敵で、お客様からも好評でした。'],
            ]
        ];

        return view('casts.profile.show', [
            'pageId' => 'cast_detail',
            'cast'   => $cast
        ]);
    }
}