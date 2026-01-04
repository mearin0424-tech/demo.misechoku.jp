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
        // 「やり取り中」のテストデータ
        $ongoingTalks = [
            [
                'partner_id' => 1,
                'name' => 'アンナ',
                'avatar' => 'storage/mock/casts/1.png',
                'last_message' => '本日はありがとうございました！またお待ちしております。',
                'last_time' => '10:25',
                'sort_key' => Carbon::today()->setHour(10)->setMinute(25),
                'unread_count' => 0,
            ],
            [
                'partner_id' => 2,
                'name' => 'リナ',
                'avatar' => 'storage/mock/casts/2.png',
                'last_message' => '了解いたしました！調整してみますね。',
                'last_time' => '昨日',
                'sort_key' => Carbon::yesterday(),
                'unread_count' => 0,
            ],
        ];

        // 「リクエスト / オファー」のテストデータ
        $requestTalks = [
            [
                'partner_id' => 4,
                'name' => 'サキ',
                'avatar' => 'storage/mock/casts/4.png',
                'last_message' => '初めまして！今夜空いていますか？',
                'last_time' => '1時間前',
                'sort_key' => Carbon::now()->subHour(),
                'unread_count' => 1,
            ],
            [
                'partner_id' => 5,
                'name' => 'エミ',
                'avatar' => 'storage/mock/casts/5.png',
                'last_message' => 'プロフィール拝見しました！',
                'last_time' => '10分前',
                'sort_key' => Carbon::now()->subMinutes(10),
                'unread_count' => 1,
            ],
        ];

        // 最新のメッセージが届いた順に並び替え (sortByDesc)
        $ongoingTalks = collect($ongoingTalks)->sortByDesc('sort_key')->values()->all();
        $requestTalks = collect($requestTalks)->sortByDesc('sort_key')->values()->all();

        return view('common.talk.index', compact('ongoingTalks', 'requestTalks'));
    }

    /**
     * トークルーム
     */
    public function room($id)
    {
        // 本来はIDから相手の名前を取得（モックでは仮定）
        $partnerName = ($id == 1) ? "アンナ" : "ゲスト"; 

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

    /**
     * メッセージ送信 (Shops/TalkController より統合)
     */
    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required', // member_id から名称を汎用的に変更
            'message' => 'required|string'
        ]);

        // TODO: Message::create([...]) でDB保存
        // 相手への通知処理(ServiceWorker等)もここに集約可能

        return response()->json([
            'success' => true, 
            'message' => '送信しました',
            'data' => [
                'content' => $request->message,
                'time' => Carbon::now()->format('H:i')
            ]
        ]);
    }
}