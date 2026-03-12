<?php
namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Shops\UploadImageRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /** デモ用固定店舗ID */
    private const DEMO_SHOP_ID = 's00000001';
    /**
     * プロフィール表示（閲覧・プレビュー）
     */
    public function show($id = null) {
        $shop = [
            'name'       => 'Club Luxurious',
            'word'       => '最高級の空間で、最高の出会いを。',
            'main_img'   => asset('storage/mock/shops/out-1.png'),
            'area'       => '東京都港区六本木',
            'concept'    => "六本木駅から徒歩3分。落ち着いた雰囲気の高級ラウンジです。\n選び抜かれたキャストと共に、至福のひとときを提供いたします。",
            'review_avg' => 4.8,
            'review_cnt' => 124,
            'sub_images' => [
                asset('storage/mock/shops/inside-1.png'),
                asset('storage/mock/shops/inside-2.png'),
                asset('storage/mock/shops/inside-3.png'),
            ]
        ];

        return view('shops.profile.show', [
            'pageId' => 'shop_info',
            'shop'   => $shop,
            'isOwn'  => is_null($id)
        ]);
    }

    /**
     * 編集画面表示
     */
    public function edit() {
        return view('shops.profile.edit', ['pageId' => 'mypage']);
    }

    /**
     * プロフィール基本情報の更新
     * (旧 api/update_mypage.php, api/update_shop.php の機能を統合)
     */
    public function update(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100',
            'overview' => 'nullable|string',
            'word' => 'nullable|string|max:50',
        ]);

        // モックなので現在は保存成功レスポンスのみ
        return response()->json(['success' => true, 'message' => 'プロフィールを更新しました']);
    }

    /**
     * 画像アップロード（DB: shop_images に保存）
     */
    public function uploadImage(UploadImageRequest $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['success' => false, 'message' => 'ファイルが見つかりません'], 400);
        }

        $path = $request->file('image')->store('public/shops/gallery');
        $slotIndex = (int) $request->input('slot_index', -1);

        $maxOrder = DB::table('shop_images')->where('shop_id', self::DEMO_SHOP_ID)->max('main_order');
        $mainOrder = $maxOrder !== null ? $maxOrder + 1 : 0;
        $isMain = $slotIndex === 0 ? 1 : 0;

        $id = DB::table('shop_images')->insertGetId([
            'shop_id'    => self::DEMO_SHOP_ID,
            'image_path' => $path,
            'type'       => null,
            'is_main'    => $isMain,
            'main_order' => $mainOrder,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($isMain) {
            DB::table('shop_profiles')->where('shop_id', self::DEMO_SHOP_ID)->update([
                'main_image_path' => $path,
                'updated_at'      => now(),
            ]);
        }

        $url = asset(ltrim(Storage::url($path), '/'));
        return response()->json([
            'success' => true,
            'path'    => $url,
            'id'      => $id,
        ]);
    }

    /**
     * 画像削除（DB: shop_images から削除しストレージも削除）
     */
    public function deleteImage(Request $request, $id)
    {
        $row = DB::table('shop_images')
            ->where('id', $id)
            ->where('shop_id', self::DEMO_SHOP_ID)
            ->first();

        if (!$row) {
            return response()->json(['success' => false, 'message' => '画像が見つかりません'], 404);
        }

        Storage::delete($row->image_path);
        DB::table('shop_images')->where('id', $id)->delete();

        if (!empty($row->is_main)) {
            $next = DB::table('shop_images')
                ->where('shop_id', self::DEMO_SHOP_ID)
                ->orderBy('main_order')
                ->orderBy('id')
                ->first();
            $mainPath = $next ? $next->image_path : null;
            DB::table('shop_profiles')->where('shop_id', self::DEMO_SHOP_ID)->update([
                'main_image_path' => $mainPath,
                'updated_at'      => now(),
            ]);
            if ($next) {
                DB::table('shop_images')->where('id', $next->id)->update(['is_main' => 1, 'updated_at' => now()]);
            }
        }

        return response()->json(['success' => true, 'message' => '画像を削除しました']);
    }

    /**
     * 画像の並び替え順序の保存
     * (旧 api/update_image_order.php の機能を統合)
     */
    public function updateOrder(Request $request) {
        // $request->input('images') で送られてくる ID の配列に基づいて main_order を更新
        $imageOrder = $request->input('images');

        if (!is_array($imageOrder)) {
            return response()->json(['success' => false, 'message' => 'データが不正です'], 400);
        }

        // 本来は foreach で DB の順番を更新
        return response()->json(['success' => true, 'message' => '並び順を保存しました']);
    }
}