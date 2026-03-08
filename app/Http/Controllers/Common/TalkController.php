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
        $isCast = request()->is('cast/*');
    
        // 相手のプロフィールを表示するためのルート名
        // キャストがログイン中ならユーザー詳細へ、店舗/ユーザーならキャスト詳細へ
        $profileRoute = $isCast ? 'cast.user.show' : 'shop.cast.show';
        
        // 「やり取り中」のテストデータ
        $ongoingTalks = [
        [
            'partner_id' => 1,
            'name' => 'みさき',
            'avatar' => 'storage/mock/casts/1-1.png',
            'last_message' => '本日はありがとうございました！またお待ちしております。',
            'last_time' => '10:25',
            'sort_key' => Carbon::today()->setHour(10)->setMinute(25),
            'unread_count' => 0,
            'last_message_by_me' => true, // 自分が送った
            'is_read' => true,           // 既読
        ],
        [
            'partner_id' => 2,
            'name' => '愛華',
            'avatar' => 'storage/mock/casts/2-1.png',
            'last_message' => '了解いたしました！調整してみますね。',
            'last_time' => '昨日',
            'sort_key' => Carbon::yesterday(),
            'unread_count' => 0,
            'last_message_by_me' => true, // 自分が送った
            'is_read' => false,          // まだ未読
        ],
        ];

        // 「リクエスト / オファー」のデータ (ID 1〜4)
    $requestTalks = [
        [
            'partner_id' => 1,
            'name' => 'みさき',
            'age' => 30,
            'location' => '六本木',
            'avatar' => 'storage/mock/casts/1-1.png',
            'last_message' => '初めまして！今夜空いていますか？',
            'last_time' => '1時間前',
            'unread_count' => 1,
        ],
        [
            'partner_id' => 2,
            'name' => '愛華',
            'age' => 30,
            'location' => '渋谷',
            'avatar' => 'storage/mock/casts/2-1.png',
            'last_message' => '初めまして！今夜空いていますか？',
            'last_time' => '1時間前',
            'unread_count' => 1,
        ],
        [
            'partner_id' => 3,
            'name' => 'Rena',
            'age' => 30,
            'location' => '新宿',
            'avatar' => 'storage/mock/casts/3-1.png',
            'last_message' => '初めまして！今夜空いていますか？',
            'last_time' => '1時間前',
            'unread_count' => 1,
        ],
        [
            'partner_id' => 4,
            'name' => 'Yumi',
            'age' => 28,
            'location' => '恵比寿',
            'avatar' => 'storage/mock/casts/4-1.png',
            'last_message' => '初めまして！今夜空いていますか？',
            'last_time' => '2時間前',
            'unread_count' => 1,
        ],
    ];

    $ongoingTalks = collect($ongoingTalks)->sortByDesc('sort_key')->values()->all();
    $requestTalks = collect($requestTalks)->sortByDesc('sort_key')->values()->all();

    return view('common.talk.index', compact('ongoingTalks', 'requestTalks', 'profileRoute'));
    }
    /**
     * トークルーム
     */
    public function room($id)
    {
        // 本来はIDから相手の名前を取得（モックでは仮定）
        $partnerName = ($id == 1) ? "愛華" : "ゲスト"; 

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
                'content' => 'またよろしくお願いいたします！',
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
                'content' => trim($request->message),
                'time' => Carbon::now()->format('H:i')
            ]
        ]);
    }
}