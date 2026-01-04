<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TalkController extends Controller
{
    /**
     * メッセージ一覧
     */
    public function index()
    {
    // 「やり取り中」のデータ（既に応答があるもの）
    $ongoingTalks = [
        [
            'partner_id' => 1,
            'name' => 'アンナ',
            'avatar' => 'storage/mock/casts/1.png',
            'last_message' => '本日はありがとうございました！またお待ちしております。',
            'last_time' => '10:25',
            'unread_count' => 0,
        ],
    ];

    // 「リクエスト / オファー」のデータ（相手からの初回の未返信メッセージ）
    $requestTalks = [
        [
            'partner_id' => 4,
            'name' => 'サキ',
            'avatar' => 'storage/mock/casts/4.png',
            'last_message' => '初めまして！今夜空いていますか？',
            'last_time' => '1時間前',
            'unread_count' => 1,
        ],
    ];

    return view('common.talk.index', compact('ongoingTalks', 'requestTalks'));
}

    /**
     * トークルーム
     */
    public function room($id)
    {
        // 本来はIDから相手の名前を取得
        $partnerName = "アンナ"; 

        // テスト用メッセージ
        $messages = [
            (object)[
                'content' => 'お疲れ様です！本日はありがとうございました。',
                'is_mine' => false,
                'created_at' => Carbon::now()->subHours(2),
            ],
            (object)[
                'content' => 'こちらこそありがとうございました！楽しかったです。',
                'is_mine' => true,
                'created_at' => Carbon::now()->subHour(),
            ],
            (object)[
                'content' => 'またのご来店をお待ちしておりますね！',
                'is_mine' => false,
                'created_at' => Carbon::now()->subMinutes(10),
            ],
        ];

        return view('common.talk.room', [
            'partnerName' => $partnerName,
            'messages' => $messages,
            'partnerId' => $id
        ]);
    }
}