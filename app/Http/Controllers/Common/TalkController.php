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
        $isCastPortal = request()->is('cast/*');

        if ($isCastPortal) {
            // キャスト側：相手はお店 → お店のプロフィールへ
            $profileRoute = 'cast.shopprofileview.show';
            $ongoingTalks = [
                ['partner_id' => 1, 'name' => 'CLUB ETERNITY', 'avatar' => 'storage/mock/shops/out-1.png', 'last_message' => '本日はありがとうございました！またお待ちしております。', 'last_time' => '10:25', 'sort_key' => Carbon::today()->setHour(10)->setMinute(25), 'unread_count' => 0, 'last_message_by_me' => true, 'is_read' => true],
                ['partner_id' => 2, 'name' => 'THE GOLDSTONE', 'avatar' => 'storage/mock/shops/out-2.png', 'last_message' => '了解いたしました！調整してみますね。', 'last_time' => '昨日', 'sort_key' => Carbon::yesterday(), 'unread_count' => 0, 'last_message_by_me' => true, 'is_read' => false],
            ];
            $requestTalks = [
                ['partner_id' => 1, 'name' => 'CLUB ETERNITY', 'age' => null, 'location' => '六本木', 'avatar' => 'storage/mock/shops/out-1.png', 'last_message' => 'オファーが届きました。週末の出勤いかがですか？', 'last_time' => '1時間前', 'unread_count' => 1],
                ['partner_id' => 2, 'name' => 'THE GOLDSTONE', 'age' => null, 'location' => '中央区', 'avatar' => 'storage/mock/shops/out-2.png', 'last_message' => '急募です。本日21時から可能な方いらっしゃいますか？', 'last_time' => '1時間前', 'unread_count' => 1],
            ];
        } else {
            // お店側：相手はキャスト → キャストのプロフィールへ
            $profileRoute = 'shop.castprofileview.show';
            $ongoingTalks = [
                ['partner_id' => 1, 'name' => 'みさき', 'avatar' => 'storage/mock/casts/1-1.png', 'last_message' => '本日はありがとうございました！またお待ちしております。', 'last_time' => '10:25', 'sort_key' => Carbon::today()->setHour(10)->setMinute(25), 'unread_count' => 0, 'last_message_by_me' => true, 'is_read' => true],
                ['partner_id' => 2, 'name' => '愛華', 'avatar' => 'storage/mock/casts/2-1.png', 'last_message' => '了解いたしました！調整してみますね。', 'last_time' => '昨日', 'sort_key' => Carbon::yesterday(), 'unread_count' => 0, 'last_message_by_me' => true, 'is_read' => false],
            ];
            $requestTalks = [
                ['partner_id' => 1, 'name' => 'みさき', 'age' => 30, 'location' => '六本木', 'avatar' => 'storage/mock/casts/1-1.png', 'last_message' => '初めまして！今夜空いていますか？', 'last_time' => '1時間前', 'unread_count' => 1],
                ['partner_id' => 2, 'name' => '愛華', 'age' => 30, 'location' => '渋谷', 'avatar' => 'storage/mock/casts/2-1.png', 'last_message' => '初めまして！今夜空いていますか？', 'last_time' => '1時間前', 'unread_count' => 1],
                ['partner_id' => 3, 'name' => 'Rena', 'age' => 30, 'location' => '新宿', 'avatar' => 'storage/mock/casts/3-1.png', 'last_message' => '初めまして！今夜空いていますか？', 'last_time' => '1時間前', 'unread_count' => 1],
                ['partner_id' => 4, 'name' => 'Yumi', 'age' => 28, 'location' => '恵比寿', 'avatar' => 'storage/mock/casts/4-1.png', 'last_message' => '初めまして！今夜空いていますか？', 'last_time' => '2時間前', 'unread_count' => 1],
            ];
        }

        $ongoingTalks = collect($ongoingTalks)->sortByDesc('sort_key')->values()->all();
        $requestTalks = collect($requestTalks)->sortByDesc('sort_key')->values()->all();

        return view('common.talk.index', compact('ongoingTalks', 'requestTalks', 'profileRoute'));
    }
    /**
     * トークルーム
     */
    public function room($id)
    {
        $isCastPortal = request()->is('cast/*');
        if ($isCastPortal) {
            $names = [1 => 'CLUB ETERNITY', 2 => 'THE GOLDSTONE', 3 => 'Club Luxurious', 4 => 'BAR STELLA'];
            $avatars = [1 => asset('storage/mock/shops/out-1.png'), 2 => asset('storage/mock/shops/out-2.png'), 3 => asset('storage/mock/shops/out-1.png'), 4 => asset('storage/mock/shops/out-2.png')];
            $partnerName = $names[$id] ?? 'お店';
        } else {
            $names = [1 => '愛華', 2 => 'みさき', 3 => 'Rena', 4 => 'Yumi'];
            $avatars = [1 => asset('storage/mock/casts/1-1.png'), 2 => asset('storage/mock/casts/2-1.png'), 3 => asset('storage/mock/casts/3-1.png'), 4 => asset('storage/mock/casts/4-1.png')];
            $partnerName = $names[$id] ?? 'ゲスト';
        }
        $partnerAvatar = $avatars[$id] ?? asset('assets/images/common/no-image.png');

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
            'partnerAvatar' => $partnerAvatar,
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