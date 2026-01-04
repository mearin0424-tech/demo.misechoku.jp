<?php
namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index() {
        $shopData = ['review_avg' => 4.2, 'review_count' => 15];
        $reviews = []; // 本来はDBから取得
        return view('shops.mypage.reviews', ['pageId' => 'review', 'shopData' => $shopData, 'reviews' => $reviews, 'isPaidPlan' => true]);
    }

    // 公開・非表示の切り替え (旧 modify_review.php の完全移行)
    public function updateStatus(Request $request) {
        $id = $request->input('id');
        $release = $request->input('release');

        // TODO: DB更新
        // Review::where('id', $id)->update(['release' => $release]);

        return response()->json(['success' => true, 'message' => 'レビューステータスを更新しました']);
    }
}