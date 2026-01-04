<?php
namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TalkController extends Controller
{
    public function index() {
        return view('shops.talk.index', ['talks' => []]);
    }

    public function room($cast_id) {
        return view('shops.talk.room', ['castName' => '美咲', 'messages' => []]);
    }

    // メッセージ送信 (旧 send_message.php の完全移行)
    public function store(Request $request) {
        $request->validate([
            'member_id' => 'required',
            'message' => 'required|string'
        ]);

        // TODO: Message::create([...]) でDB保存
        // 相手への通知処理(ServiceWorker等)もここに集約可能

        return response()->json(['success' => true, 'message' => '送信しました']);
    }
}