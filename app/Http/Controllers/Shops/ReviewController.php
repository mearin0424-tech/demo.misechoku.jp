<?php
namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReviewPortalService;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewPortalService $reviewPortalService)
    {
    }

    public function index() {
        $pageData = $this->reviewPortalService->getShopReviewPageData($this->currentShopId());

        return view('shops.mypage.reviews', [
            'pageId' => 'review',
            'shopData' => $pageData['shopData'],
            'reviews' => $pageData['reviews'],
            'isPaidPlan' => $pageData['supports_release'],
        ]);
    }

    // 公開・非表示の切り替え (旧 modify_review.php の完全移行)
    public function updateStatus(Request $request) {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'release' => ['required', 'integer', 'in:0,1'],
        ]);

        $updated = $this->reviewPortalService->updateShopReviewStatus(
            $this->currentShopId(),
            (int) $data['id'],
            (int) $data['release']
        );

        return response()->json([
            'success' => $updated,
            'message' => $updated
                ? 'レビューステータスを更新しました'
                : 'レビュー公開設定はこの環境では変更できません',
        ], $updated ? 200 : 422);
    }

    /**
     * 店舗からレビューへの返信投稿・更新・削除。
     * 1 レビューに対して 1 件の返信のみ（reviews.reply_body / reply_at を直接更新）。
     * 空文字が来たら削除扱い。
     */
    public function reply(Request $request)
    {
        $data = $request->validate([
            'id'         => ['required', 'integer'],
            'reply_body' => ['nullable', 'string', 'max:1000'],
        ]);

        $reply = trim((string) ($data['reply_body'] ?? ''));

        $affected = \Illuminate\Support\Facades\DB::table('reviews')
            ->where('id', $data['id'])
            ->where('shop_id', $this->currentShopId())
            ->update([
                'reply_body' => $reply !== '' ? $reply : null,
                'reply_at'   => $reply !== '' ? now() : null,
                'updated_at' => now(),
            ]);

        if ($affected === 0) {
            return response()->json([
                'success' => false,
                'message' => 'このレビューへの返信権限がありません。',
            ], 403);
        }

        return response()->json([
            'success'   => true,
            'message'   => $reply !== '' ? '返信を投稿しました。' : '返信を削除しました。',
            'reply_body' => $reply !== '' ? $reply : null,
            'reply_at'  => $reply !== '' ? now()->format('Y/m/d H:i') : null,
        ]);
    }

    private function currentShopId(): string
    {
        return (string) auth()->guard('shop')->user()->shop_id;
    }
}