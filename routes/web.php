<?php
// prj/routes/web.php

use Illuminate\Support\Facades\Route;

// 共通・認証系
use App\Http\Controllers\Common\PageController;
use App\Http\Controllers\Common\SettingController;
use App\Http\Controllers\Auth\Cast\LoginController as CastLogin;
use App\Http\Controllers\Auth\Shop\LoginController as ShopLogin;
use App\Http\Controllers\Common\TalkController as TalkController;

// 店舗側
use App\Http\Controllers\Shops\HomeController as ShopHome;
use App\Http\Controllers\Shops\SearchController as ShopSearch;
use App\Http\Controllers\Shops\MypageController as ShopMypage;
use App\Http\Controllers\Shops\ProfileController as ShopProfile;
use App\Http\Controllers\Shops\RecruitmentController as ShopRecruit;
use App\Http\Controllers\Shops\ReviewController as ShopReview;
use App\Http\Controllers\Shops\InteractionController as ShopInteraction;

// キャスト側
use App\Http\Controllers\Casts\ProfileController as CastProfile;
use App\Http\Controllers\Casts\MypageController as CastMypage;
use App\Http\Controllers\Casts\SearchController as CastSearch;
use App\Http\Controllers\Casts\RecruitmentController as CastRecruit;

/*
|--------------------------------------------------------------------------
| ファビコン（404防止：アイコンは layout の link で指定）
|--------------------------------------------------------------------------
*/
Route::get('/favicon.ico', function () {
    return response('', 204);
})->name('favicon');

/*
|--------------------------------------------------------------------------
| PWA Manifest（スマホのインストール判定用：MIME と絶対URL）
|--------------------------------------------------------------------------
*/
Route::get('/manifest.json', function () {
    $base = rtrim(request()->getSchemeAndHttpHost(), '/');
    $manifest = [
        'name' => 'ミセチョク',
        'short_name' => 'ミセチョク',
        'description' => 'ミセチョク - デモ',
        'start_url' => $base . '/shop/home',
        'scope' => $base . '/',
        'id' => $base . '/',
        'display' => 'standalone',
        'display_override' => ['standalone', 'minimal-ui', 'browser'],
        'orientation' => 'portrait-primary',
        'theme_color' => '#190509',
        'background_color' => '#190509',
        'lang' => 'ja',
        'prefer_related_applications' => false,
        'icons' => [
            ['src' => $base . '/assets/images/pwa/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => $base . '/assets/images/pwa/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
            ['src' => $base . '/assets/images/pwa/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => $base . '/assets/images/pwa/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ],
    ];
    return response()->json($manifest, 200, [
        'Content-Type' => 'application/manifest+json',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('manifest');

/*
|--------------------------------------------------------------------------
| リダイレクト設定
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/shop/home');
Route::redirect('/shop', '/shop/home');
Route::redirect('/cast', '/cast/home');

/*
|--------------------------------------------------------------------------
| 1. Public & Guest Routes (LP・認証)
|--------------------------------------------------------------------------
*/
Route::name('pages.')->group(function () {
    Route::get('/about', [PageController::class, 'about'])->name('official.about');
    Route::get('/terms', [PageController::class, 'terms'])->name('official.terms');
    Route::get('/privacy', [PageController::class, 'privacy'])->name('official.privacy');
    Route::get('/support/column', [PageController::class, 'column'])->name('support.column');
    Route::get('/support/form', [PageController::class, 'supportForm'])->name('support.form');
});

// 設定系（共通）
Route::prefix('setting')->name('setting.')->group(function () {
    Route::get('/notification', [SettingController::class, 'notification'])->name('notification');
    Route::get('/account/email', [SettingController::class, 'accountEmail'])->name('account.email');
    Route::get('/account/password', [SettingController::class, 'accountPassword'])->name('account.password');
    Route::get('/account/withdraw', [SettingController::class, 'accountWithdraw'])->name('account.withdraw');
});

// プラン設定（店舗専用・デモ用）
Route::get('/subscription', [SettingController::class, 'subscription'])->name('subscription');

// 未実装画面・機能用（maintenance-screen.png を表示）
Route::get('/maintenance', function () {
    return view('common.maintenance');
})->name('maintenance');

Route::get('/logout', [CastLogin::class, 'logout'])->name('auth.logout');

/*
|--------------------------------------------------------------------------
| PWA Push 通知 API（同一オリジン・CSRF あり）
|--------------------------------------------------------------------------
*/
Route::prefix('api/push')->name('push.')->group(function () {
    Route::get('vapid-public-key', [\App\Http\Controllers\Api\PushController::class, 'vapidPublicKey'])->name('vapid');
    Route::post('subscribe', [\App\Http\Controllers\Api\PushController::class, 'subscribe'])->name('subscribe');
    Route::post('send-test', [\App\Http\Controllers\Api\PushController::class, 'sendTest'])->name('send-test');
});

Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/login', [ShopLogin::class, 'showLoginForm'])->name('login');
    Route::post('/login', [ShopLogin::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| 2. Shop Portal (店舗専用)
|--------------------------------------------------------------------------
*/
Route::prefix('shop')->name('shop.')->group(function () {

    Route::get('/home', [ShopHome::class, 'index'])->name('home');
    Route::get('/search', fn () => redirect()->route('shop.search.index', ['tab' => 'timeline']));
    Route::get('/search/{tab}', [ShopSearch::class, 'index'])->name('search.index')->where('tab', 'timeline|list|ai');

    // トーク
    Route::prefix('talk')->name('talk.')->group(function () {
        Route::get('/', [TalkController::class, 'index'])->name('index');
        Route::get('/room/{id}', [TalkController::class, 'room'])->name('room');
        Route::post('/send', [TalkController::class, 'store'])->name('send');
    });

    // つながり
    Route::prefix('interaction')->name('interaction.')->group(function () {
        Route::get('/', [ShopInteraction::class, 'index'])->name('index');
        Route::get('/keep', [ShopInteraction::class, 'keep'])->name('keep');
        Route::get('/like', [ShopInteraction::class, 'like'])->name('like');
    });

    // キャストのプロフィール閲覧（店舗から見る）
    Route::get('/castprofileview/{id}', [CastProfile::class, 'show'])->name('castprofileview.show');

    // プロフィール（キャスト用編集＝shop/profile/edit、店舗用は store サブパス）
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ShopProfile::class, 'show'])->name('show');
        Route::get('/edit', [CastProfile::class, 'edit'])->name('edit');
        Route::post('/update', [CastProfile::class, 'update'])->name('update');
        Route::get('/store/edit', [ShopProfile::class, 'edit'])->name('store.edit');
        Route::post('/store/update', [ShopProfile::class, 'update'])->name('store.update');
        Route::post('/upload-image', [ShopProfile::class, 'uploadImage'])->name('upload.image');
        Route::delete('/image/{id}', [ShopProfile::class, 'deleteImage'])->name('image.delete');
    });

    // ★ 求人票 (Recruits)
    Route::prefix('recruits')->name('recruits.')->group(function () {
        Route::get('/status', [ShopRecruit::class, 'status'])->name('status'); 
        Route::get('/edit', [ShopRecruit::class, 'edit'])->name('edit');
        Route::get('/show/{id?}', [ShopRecruit::class, 'show'])->name('show');
        Route::put('/update', [ShopRecruit::class, 'update'])->name('update');
    });

    // マイページ
    Route::prefix('mypage')->name('mypage.')->group(function () {
        Route::get('/', [ShopMypage::class, 'index'])->name('index');
        Route::get('/payment', [ShopMypage::class, 'payment'])->name('payment.index');
        Route::get('/reviews', [ShopReview::class, 'index'])->name('review.index');
    });

    // レビュー公開・非表示の切り替え（reviews 画面の JS から使用）
    Route::post('/mypage/review/update', [ShopReview::class, 'updateStatus'])->name('review.update');

    // SUPPORT ページ（店舗向け）
    Route::get('/feature', [PageController::class, 'feature'])->name('feature');
    Route::get('/htu', [PageController::class, 'htu'])->name('htu');
    Route::get('/faq', [PageController::class, 'faq'])->name('faq');
});

/*
|--------------------------------------------------------------------------
| 3. Cast Portal (キャスト専用)
|--------------------------------------------------------------------------
*/
Route::prefix('cast')->name('cast.')->group(function () {
    Route::get('/home', [ShopHome::class, 'index'])->name('home');
    Route::get('/profile/edit', [CastProfile::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [CastProfile::class, 'update'])->name('profile.update');
    Route::get('/shopprofileview/{id}', [CastProfile::class, 'show'])->name('shopprofileview.show');
    Route::redirect('/profile/{id}', '/cast/shopprofileview/{id}')->name('profile.show.redirect');
    Route::get('/search', fn () => redirect()->route('cast.search.index', ['tab' => 'timeline']));
    Route::get('/search/{tab}', [CastSearch::class, 'index'])->name('search.index')->where('tab', 'timeline|list|ai');
    Route::get('/recruit/{id}', [CastRecruit::class, 'show'])->name('recruit.show');
    
    Route::get('/interaction', [ShopInteraction::class, 'index'])->name('interaction.index');
    Route::get('/mypage', [CastMypage::class, 'index'])->name('mypage.index');
    Route::get('/mypage/employment', [CastMypage::class, 'employment'])->name('mypage.employment');
    Route::get('/mypage/payment', [CastMypage::class, 'payment'])->name('mypage.payment');
    Route::get('/mypage/reviews', [CastMypage::class, 'reviews'])->name('mypage.reviews');

    Route::prefix('talk')->name('talk.')->group(function () {
        Route::get('/', [TalkController::class, 'index'])->name('index');
        Route::get('/room/{id}', [TalkController::class, 'room'])->name('room');
        Route::post('/send', [TalkController::class, 'store'])->name('send');
    });

    // SUPPORT ページ（キャスト向け）
    Route::get('/feature', [PageController::class, 'feature'])->name('feature');
    Route::get('/htu', [PageController::class, 'htu'])->name('htu');
    Route::get('/faq', [PageController::class, 'faq'])->name('faq');
});

/* 内部キャッシュクリア用 */
Route::get('/clear-route', function() {
    \Artisan::call('route:clear');
    return "Route cache cleared!";
});