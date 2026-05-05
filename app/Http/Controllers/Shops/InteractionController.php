<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InteractionController extends Controller
{
    public function index()
    {
        $isCastPortal = request()->is('cast/*');

        if ($isCastPortal) {
            // キャスト側：お店のキープ・ライク・足あと
            $castUser = Auth::guard('member')->user();
            $castId = $castUser ? (string) $castUser->id : null;

            $keepCasts = [];
            $receivedLikeCasts = [];
            $sentLikeCasts = [];
            $footprintCasts = [];

            if ($castId && Schema::hasTable('favorites')) {
                // 自分がキープした店舗（cast -> shop, action_type=1）
                $keepRows = DB::table('favorites')
                    ->join('shops', 'favorites.shop_id', '=', 'shops.id')
                    ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
                    ->where('favorites.cast_id', $castId)
                    ->where('favorites.action_type', 1)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'shops.id',
                        'shop_profiles.shop_name as name',
                        'shop_profiles.pref',
                        'shop_profiles.city',
                        DB::raw("(SELECT si.image_path FROM shop_images si WHERE si.shop_id = shops.id ORDER BY si.is_main DESC, si.main_order IS NULL, si.main_order, si.id LIMIT 1) as main_image_path"),
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($keepRows as $row) {
                    $keepCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: '店舗',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $row->main_image_path ? asset($row->main_image_path) : asset('storage/mock/shops/out-1.png'),
                        'updated_at' => optional($row->created_at)->format('Y-m-d H:i'),
                    ];
                }

                // 自分が送ったLIKE（cast -> shop, action_type=3）
                $sentRows = DB::table('favorites')
                    ->join('shops', 'favorites.shop_id', '=', 'shops.id')
                    ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
                    ->where('favorites.cast_id', $castId)
                    ->where('favorites.action_type', 3)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'shops.id',
                        'shop_profiles.shop_name as name',
                        'shop_profiles.pref',
                        'shop_profiles.city',
                        DB::raw("(SELECT si.image_path FROM shop_images si WHERE si.shop_id = shops.id ORDER BY si.is_main DESC, si.main_order IS NULL, si.main_order, si.id LIMIT 1) as main_image_path"),
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($sentRows as $row) {
                    $sentLikeCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: '店舗',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $row->main_image_path ? asset($row->main_image_path) : asset('storage/mock/shops/out-1.png'),
                        'created_at' => optional($row->created_at)->format('Y-m-d H:i'),
                        'is_match' => false,
                    ];
                }

                // 受け取ったLIKE（shop -> cast, action_type=3）
                $receivedRows = DB::table('favorites')
                    ->join('shops', 'favorites.shop_id', '=', 'shops.id')
                    ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
                    ->where('favorites.cast_id', $castId)
                    ->where('favorites.action_type', 3)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'shops.id',
                        'shop_profiles.shop_name as name',
                        'shop_profiles.pref',
                        'shop_profiles.city',
                        DB::raw("(SELECT si.image_path FROM shop_images si WHERE si.shop_id = shops.id ORDER BY si.is_main DESC, si.main_order IS NULL, si.main_order, si.id LIMIT 1) as main_image_path"),
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($receivedRows as $row) {
                    $receivedLikeCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: '店舗',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $row->main_image_path ? asset($row->main_image_path) : asset('storage/mock/shops/out-2.png'),
                        'created_at' => optional($row->created_at)->format('Y-m-d H:i'),
                        'is_match' => false,
                    ];
                }
            }

            // 足あと（cast -> shop）は旧実装に依存していたため、ここでは未実装のまま（今後 Footprints テーブルと連携）
            $footprintCasts = [];

            $profileRoute = 'cast.shopprofileview.show';
        } else {
            // お店側：キャストのキープ・ライク・足あと（キャスト画像は storage/mock/casts/{id}-1.png、存在しない場合はビュー側でデフォルト表示）
            $shopUser = Auth::guard('shop')->user();
            $shopId = $shopUser ? (string) $shopUser->shop_id : null;

            $keepCasts = [];
            $receivedLikeCasts = [];
            $sentLikeCasts = [];
            $footprintCasts = [];

            if ($shopId && Schema::hasTable('favorites')) {
                // 店舗がキープしたキャスト（shop -> cast, action_type=1）
                $keepRows = DB::table('favorites')
                    ->join('casts', 'favorites.cast_id', '=', 'casts.id')
                    ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
                    ->where('favorites.shop_id', $shopId)
                    ->where('favorites.action_type', 1)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'casts.id',
                        'cast_profiles.nickname as name',
                        'cast_profiles.birthday',
                        'cast_profiles.pref',
                        'cast_profiles.city',
                        'cast_profiles.profession',
                        'cast_profiles.main_image_path',
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($keepRows as $row) {
                    $age = $row->birthday ? \Carbon\Carbon::parse($row->birthday)->age : null;
                    $keepCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: 'ゲスト',
                        'age' => $age,
                        'profession' => $row->profession ?? '',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $row->main_image_path ? asset($row->main_image_path) : asset('storage/mock/casts/'.$row->id.'-1.png'),
                        'updated_at' => optional($row->created_at)->format('Y-m-d H:i'),
                    ];
                }

                // 店舗が送ったLIKE（shop -> cast, action_type=3）
                $sentRows = DB::table('favorites')
                    ->join('casts', 'favorites.cast_id', '=', 'casts.id')
                    ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
                    ->where('favorites.shop_id', $shopId)
                    ->where('favorites.action_type', 3)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'casts.id',
                        'cast_profiles.nickname as name',
                        'cast_profiles.birthday',
                        'cast_profiles.pref',
                        'cast_profiles.city',
                        'cast_profiles.profession',
                        'cast_profiles.main_image_path',
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($sentRows as $row) {
                    $age = $row->birthday ? \Carbon\Carbon::parse($row->birthday)->age : null;
                    $sentLikeCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: 'ゲスト',
                        'age' => $age,
                        'profession' => $row->profession ?? '',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $row->main_image_path ? asset($row->main_image_path) : asset('storage/mock/casts/'.$row->id.'-1.png'),
                        'created_at' => optional($row->created_at)->format('Y-m-d H:i'),
                        'is_match' => false,
                    ];
                }

                // キャストから受け取ったLIKE（cast -> shop, action_type=3）
                $receivedRows = DB::table('favorites')
                    ->join('casts', 'favorites.cast_id', '=', 'casts.id')
                    ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
                    ->where('favorites.shop_id', $shopId)
                    ->where('favorites.action_type', 3)
                    ->orderByDesc('favorites.created_at')
                    ->select(
                        'casts.id',
                        'cast_profiles.nickname as name',
                        'cast_profiles.birthday',
                        'cast_profiles.pref',
                        'cast_profiles.city',
                        'cast_profiles.profession',
                        'cast_profiles.main_image_path',
                        'favorites.created_at'
                    )
                    ->get();
                foreach ($receivedRows as $row) {
                    $age = $row->birthday ? \Carbon\Carbon::parse($row->birthday)->age : null;
                    $receivedLikeCasts[] = [
                        'id' => $row->id,
                        'name' => $row->name ?: 'ゲスト',
                        'age' => $age,
                        'profession' => $row->profession ?? '',
                        'pref' => $row->pref ?? '',
                        'city' => $row->city ?? '',
                        'img' => $row->main_image_path ? asset($row->main_image_path) : asset('storage/mock/casts/'.$row->id.'-1.png'),
                        'created_at' => optional($row->created_at)->format('Y-m-d H:i'),
                        'is_match' => false,
                    ];
                }
            }

            // 足あと（shop -> cast）は旧実装に依存していたため、ここでは一旦空配列（今後 FootprintsRepository を移植）
            $footprintCasts = [];

            $profileRoute = 'shop.castprofileview.show';
        }

        return view('shops.interaction.index', [
            'pageId' => 'connection',
            'keepCasts' => $keepCasts,
            'receivedLikeCasts' => $receivedLikeCasts,
            'sentLikeCasts' => $sentLikeCasts,
            'footprintCasts' => $footprintCasts,
            'profileRoute' => $profileRoute,
        ]);
    }
}