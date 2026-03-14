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

    private function currentShopId(): string
    {
        return (string) auth()->guard('shop')->user()->shop_id;
    }
}