<?php
namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Shops\UploadImageRequest; 
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
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
     * 画像アップロード
     * (旧 api/upload_image.php の機能を統合)
     */
    public function uploadImage(UploadImageRequest $request) {
        // UploadImageRequest で拡張子チェック(jpg,png等)とサイズチェックは完了済み
        if ($request->hasFile('image')) {
            // ストレージへ保存
            $path = $request->file('image')->store('public/shops/gallery');
            
            // 本来はここでDB(shop_sub_images)にレコード作成
            return response()->json([
                'success' => true, 
                'path' => Storage::url($path),
                'id' => rand(100, 999) // モック用ID
            ]);
        }
        return response()->json(['success' => false, 'message' => 'ファイルが見つかりません'], 400);
    }

    /**
     * 画像削除
     * (旧 api/delete_image.php の機能を統合)
     */
    public function deleteImage(Request $request, $id) {
        // 本来はここでDBからパスを取得し Storage::delete($path) を実行
        // その後 DB レコードを削除
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