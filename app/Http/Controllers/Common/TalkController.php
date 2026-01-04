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
        // テストデータ
        $talks = [
            [
                'partner_id' => 1,
                'name' => 'アンナ',
                'avatar' => 'storage/mock/casts/1.png',
                'last_message' => '本日はありがとうございました！またお待ちしております。',
                'last_time' => '10:25',
                'unread_count' => 2,
            ],
            [
                'partner_id' => 2,
                'name' => 'リナ',
                'avatar' => 'storage/mock/casts/2.png',
                'last_message' => '了解いたしました！調整してみますね。',
                'last_time' => '昨日',
                'unread_count' => 0,
            ],
            [
                'partner_id' => 3,
                'name' => '店長 田中',
                'avatar' => 'storage/mock/shops/1.png',
                'last_message' => '明日のシフトの件ですが、19時からで大丈夫でしょうか？',
                'last_time' => '火曜',
                'unread_count' => 0,
            ],
        ];

        return view('common.talk.index', compact('talks'));
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