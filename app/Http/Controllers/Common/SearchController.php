<?php
// prj/routes/web.php

use Illuminate\Support\Facades\Route;

// コントローラーのインポート
use App\Http\Controllers\Common\PageController;
use App\Http\Controllers\Common\SettingController;
use App\Http\Controllers\Auth\Cast\LoginController as CastLogin;
use App\Http\Controllers\Auth\Shop\LoginController as ShopLogin;

// 共通機能コントローラー
use App\Http\Controllers\Common\TalkController as TalkController;

// 店舗側コントローラー
use App\Http\Controllers\Shops\HomeController as ShopHome;
use App\Http\Controllers\Shops\SearchController as ShopSearch; // 新しい共通化ベース
use App\Http\Controllers\Shops\MypageController as ShopMypage;
use App\Http\Controllers\Shops\ProfileController as ShopProfile;
use App\Http\Controllers\Shops\RecruitmentController as ShopRecruit;
use App\Http\Controllers\Shops\ReviewController as ShopReview;
use App\Http\Controllers\Shops\InteractionController as ShopInteraction;

// キャスト側コントローラー
use App\Http\Controllers\Casts\ProfileController as CastProfile;
use App\Http\Controllers\Casts\SearchController as CastSearch; // 追加：共通化ベース

/*
|--------------------------------------------------------------------------
| リダイレクト
|--------------------------------------------------------------------------
*/

Route::redirect('/shop', '/shop/home');
Route::redirect('/cast', '/cast/home'); 

/*
|--------------------------------------------------------------------------
| 1. Public & Guest Routes (LP・認証)
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/shop/home');

// LP・サポート系
Route::name('pages.')->group(function () {
    Route::get('/about', [PageController::class, 'about'])->name('official.about');
    Route::get('/terms', [PageController::class, 'terms'])->name('official.terms');
    Route::get('/privacy', [PageController::class, 'privacy'])->name('official.privacy');
    Route::get('/support/column', [PageController::class, 'column'])->name('support.column');
});

// 共通認証
Route::get('/logout', [CastLogin::class, 'logout'])->name('auth.logout');

// 店舗認証
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/login', [ShopLogin::class, 'showLoginForm'])->name('login');
    Route::post('/login', [ShopLogin::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Shop Portal (店舗専用)
|--------------------------------------------------------------------------
*/

Route::prefix('shop')->name('shop.')->group(function () {
    
    // ホーム・検索
    Route::get('/home', [ShopHome::class, 'index'])->name('home');
    Route::get('/search', [ShopSearch::class, 'index'])->name('search.index');

    // トーク・メッセージ
    Route::prefix('talk')->name('talk.')->group(function () {
        Route::get('/', [TalkController::class, 'index'])->name('index');
        Route::get('/room/{id}', [TalkController::class, 'room'])->name('room');
        Route::post('/send', [TalkController::class, 'store'])->name('send');
    });

    // つながり (Interaction)
    Route::prefix('interaction')->name('interaction.')->group(function () {
        Route::get('/', [ShopInteraction::class, 'index'])->name('index');
        Route::get('/keep', [ShopInteraction::class, 'keep'])->name('keep');
        Route::get('/like', [ShopInteraction::class, 'like'])->name('like');
    });

    // プロフィール・ギャラリー管理
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ShopProfile::class, 'show'])->name('show');
        Route::get('/edit', [ShopProfile::class, 'edit'])->name('edit');
        Route::post('/update', [ShopProfile::class, 'update'])->name('update');
        
        // ギャラリー操作
        Route::get('/gallery', [ShopProfile::class, 'gallery'])->name('gallery.edit');
        Route::post('/gallery/upload', [ShopProfile::class, 'uploadImage'])->name('gallery.upload');
        Route::post('/gallery/order', [ShopProfile::class, 'updateOrder'])->name('gallery.order');
        Route::delete('/gallery/{id}', [ShopProfile::class, 'deleteImage'])->name('gallery.delete');
    });

    // 求人票 (Recruits)
    Route::prefix('recruits')->name('recruits.')->group(function () {
        Route::get('/', [ShopRecruit::class, 'show'])->name('show');
        Route::get('/edit', [ShopRecruit::class, 'edit'])->name('edit');
        Route::put('/update', [ShopRecruit::class, 'update'])->name('update');
        Route::get('/status', [ShopRecruit::class, 'status'])->name('status'); 
    });

    // マイページ・管理系
    Route::prefix('mypage')->name('mypage.')->group(function () {
        Route::get('/', [ShopMypage::class, 'index'])->name('index');
        Route::get('/payment', [ShopMypage::class, 'payment'])->name('payment.index');
        Route::get('/subscription', [ShopMypage::class, 'subscription'])->name('subscription');
        
        // レビュー管理
        Route::get('/reviews', [ShopReview::class, 'index'])->name('review.index');
        Route::post('/reviews/update-status', [ShopReview::class, 'updateStatus'])->name('review.update');
    });
});

/*
|--------------------------------------------------------------------------
| Cast Portal (キャスト専用)
|--------------------------------------------------------------------------
*/
Route::prefix('cast')->name('cast.')->group(function () {
    // ホーム・プロフィール
    Route::get('/home', [ShopHome::class, 'index'])->name('home'); 
    Route::get('/profile/{id}', [CastProfile::class, 'show'])->name('profile.show');

    // 検索（追加：これで cast.search.index が有効になります）
    Route::get('/search', [CastSearch::class, 'index'])->name('search.index');

    // トーク・メッセージ
    Route::prefix('talk')->name('talk.')->group(function () {
        Route::get('/', [TalkController::class, 'index'])->name('index');
        Route::get('/room/{id}', [TalkController::class, 'room'])->name('room');
        Route::post('/send', [TalkController::class, 'store'])->name('send');
    });
});

/*
|--------------------------------------------------------------------------
| 3. Common Protected Settings (共通設定)
|--------------------------------------------------------------------------
*/

Route::group([], function () {
    Route::get('/setting/notification', [SettingController::class, 'notification'])->name('common.setting.notification');
    Route::get('/setting/account', [SettingController::class, 'account'])->name('common.settings.account');
});

/* 内部キャッシュクリア用 */
Route::get('/clear-route', function() {
    \Artisan::call('route:clear');
    return "Route cache cleared!";
});