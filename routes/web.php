<?php
// prj/routes/web.php

use Illuminate\Support\Facades\Route;

// 共通・認証系
use App\Http\Controllers\Common\PageController;
use App\Http\Controllers\Common\SettingController;
use App\Http\Controllers\Common\DemoLoginController;
use App\Http\Controllers\Common\BankLookupController;
use App\Http\Controllers\Common\RegistrationController;
use App\Http\Controllers\Auth\Cast\LoginController as CastLogin;
use App\Http\Controllers\Common\TalkController as TalkController;

// 管理者（バックオフィス）
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\DepositController as AdminDeposit;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoice;
use App\Http\Controllers\Admin\SalesController as AdminSales;
use App\Http\Controllers\Admin\MasterController as AdminMaster;
use App\Http\Controllers\Admin\ColumnController as AdminColumn;
use App\Http\Controllers\Admin\InquiryController as AdminInquiry;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\ShopController as AdminShop;
use App\Http\Controllers\Admin\CastController as AdminCast;
use App\Http\Controllers\Admin\NgWordController as AdminNgWord;
use App\Http\Controllers\Admin\NoticeController as AdminNotice;
use App\Http\Controllers\Admin\TaskController as AdminTask;
use App\Http\Controllers\Admin\AdminAccountController as AdminAccount;
use App\Http\Controllers\Admin\VerificationController as AdminVerification;
use App\Http\Controllers\Admin\BankController as AdminBank;

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
Route::redirect('/', '/login');
Route::redirect('/shop', '/shop/home');
Route::redirect('/cast', '/cast/home');
Route::redirect('/bk', '/admin');
Route::get('/bk/{path}', function ($path) {
    return redirect('/admin/' . $path);
})->where('path', '.*');

/*
|--------------------------------------------------------------------------
| Admin Portal (管理者専用)
|--------------------------------------------------------------------------
|
| 店舗・キャストとは別のプレフィックス `/admin` で管理画面を提供する。
| 認証まわりは今後拡張しやすいようにルートを分離しておく。
|
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // ログインは共通 /login に統一（リダイレクトのみ）
    Route::get('/login', fn () => redirect('/login'))->name('login');
    Route::post('/logout', [AdminAuth::class, 'logout'])->name('logout');

    // 管理画面本体
    Route::middleware([])->group(function () {
        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');

        // 請求書発行
        Route::get('/invoices', [AdminInvoice::class, 'index'])->name('invoices.index');
        Route::post('/invoices/issue-manual', [AdminInvoice::class, 'issueManual'])->name('invoices.issue-manual');
        Route::get('/invoices/template-settings', [AdminInvoice::class, 'templateSettings'])->name('invoices.template-settings');
        Route::post('/invoices/template-settings', [AdminInvoice::class, 'updateTemplateSettings'])->name('invoices.template-settings.update');

        // 入金・振込管理
        Route::get('/deposits/invoice-template/download', [AdminDeposit::class, 'downloadInvoiceTemplate'])->name('deposits.invoice-template.download');
        Route::get('/deposits', [AdminDeposit::class, 'index'])->name('deposits.index');
        Route::get('/deposits/{deposit}/invoice', [AdminDeposit::class, 'showInvoice'])->name('deposits.invoice.show');
        Route::get('/deposits/{deposit}/invoice/pdf', [AdminDeposit::class, 'downloadInvoicePdf'])->name('deposits.invoice.pdf');
        Route::post('/deposits/{deposit}/invoice', [AdminDeposit::class, 'issueInvoice'])->name('deposits.invoice.issue');
        Route::post('/deposits/{deposit}/confirm-shop-payment', [AdminDeposit::class, 'confirmShopPayment'])->name('deposits.shop-payment.confirm');
        Route::post('/deposits/{deposit}/transfer-cast', [AdminDeposit::class, 'transferCast'])->name('deposits.cast-transfer.execute');

        // 売上管理
        Route::get('/sales', [AdminSales::class, 'index'])->name('sales.index');

        // マスタ設定管理
        Route::get('/masters', [AdminMaster::class, 'index'])->name('masters.index');
        Route::post('/masters/catalogs/{catalogKey}', [AdminMaster::class, 'storeCatalog'])->name('masters.catalogs.store');
        Route::patch('/masters/catalogs/{catalogKey}/{recordId}', [AdminMaster::class, 'updateCatalog'])->name('masters.catalogs.update');

        // 店舗管理
        Route::get('/shops', [AdminShop::class, 'index'])->name('shops.index');
        Route::post('/shops/{shopId}/toggle-recruit-status', [AdminShop::class, 'toggleRecruitStatus'])->name('shops.toggle-recruit-status');

        // キャスト管理
        Route::get('/casts', [AdminCast::class, 'index'])->name('casts.index');

        // NGワード管理
        Route::get('/ngwords', [AdminNgWord::class, 'index'])->name('ngwords.index');

        // お知らせ管理
        Route::get('/notices', [AdminNotice::class, 'index'])->name('notices.index');

        // コラム管理
        Route::get('/columns', [AdminColumn::class, 'index'])->name('columns.index');

        // 請求・振込タスク管理
        Route::get('/tasks', [AdminTask::class, 'index'])->name('tasks.index');

        // 本人・書類審査
        Route::get('/verification', [AdminVerification::class, 'index'])->name('verification.index');
        Route::post('/verification/cast/{document}/approve', [AdminVerification::class, 'approveCast'])->name('verification.cast.approve');
        Route::post('/verification/cast/{document}/reject', [AdminVerification::class, 'rejectCast'])->name('verification.cast.reject');
        Route::post('/verification/shopdoc/{document}/approve', [AdminVerification::class, 'approveShopDocument'])->name('verification.shopdoc.approve');
        Route::post('/verification/shopdoc/{document}/reject', [AdminVerification::class, 'rejectShopDocument'])->name('verification.shopdoc.reject');

        // 問い合わせ管理
        Route::get('/inquiries', [AdminInquiry::class, 'index'])->name('inquiries.index');

        // アカウント管理（運営）
        Route::get('/admin-accounts', [AdminAccount::class, 'index'])->name('admin-accounts.index');

        // 運営口座情報
        Route::get('/bank', [AdminBank::class, 'index'])->name('bank.index');
        Route::post('/bank', [AdminBank::class, 'store'])->name('bank.store');
    });
});

/*
|--------------------------------------------------------------------------
| 1. Public & Guest Routes (LP・認証)
|--------------------------------------------------------------------------
*/
// デモ用共通ログイン
Route::get('/login', [DemoLoginController::class, 'show'])->name('login.demo');
Route::post('/login', [DemoLoginController::class, 'login'])->name('login.demo.post');

Route::name('pages.')->group(function () {
    Route::get('/about', [PageController::class, 'about'])->name('official.about');
    Route::get('/terms', [PageController::class, 'terms'])->name('official.terms');
    Route::get('/privacy', [PageController::class, 'privacy'])->name('official.privacy');
    Route::get('/support/column', [PageController::class, 'column'])->name('support.column');
    Route::get('/support/form', [PageController::class, 'supportForm'])->name('support.form');
});

Route::prefix('share')->name('share.')->group(function () {
    Route::get('/recruit/{id}', [CastRecruit::class, 'publicShow'])->name('recruit.show');
    Route::get('/cast/{id}', [CastProfile::class, 'publicShow'])->name('cast.show');
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

Route::middleware('signed')->group(function () {
    Route::get('/billing/invoices/{deposit}', [AdminDeposit::class, 'showSignedInvoice'])->name('billing.invoices.show');
    Route::get('/billing/invoices/{deposit}/pdf', [AdminDeposit::class, 'showSignedInvoicePdf'])->name('billing.invoices.pdf');
});

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

Route::prefix('api/bank-lookup')->name('api.bank-lookup.')->group(function () {
    Route::get('banks', [BankLookupController::class, 'banks'])->name('banks');
    Route::get('branches', [BankLookupController::class, 'branches'])->name('branches');
});

Route::prefix('cast')->name('cast.')->group(function () {
    Route::get('/login', fn () => redirect('/login'))->name('login');
    Route::get('/register', [RegistrationController::class, 'showCast'])->name('register');
    Route::post('/register', [RegistrationController::class, 'storeCast'])->name('register.store');
});

Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/login', fn () => redirect('/login'))->name('login');
    Route::get('/register', [RegistrationController::class, 'showShop'])->name('register');
    Route::post('/register', [RegistrationController::class, 'storeShop'])->name('register.store');
});

/*
|--------------------------------------------------------------------------
| 2. Shop Portal (店舗専用)
|--------------------------------------------------------------------------
*/
Route::prefix('shop')->name('shop.')->middleware('shop.auth')->group(function () {

    Route::get('/home', [ShopHome::class, 'index'])->name('home');
    Route::get('/search', fn () => redirect()->route('shop.search.index', ['tab' => 'timeline']));
    Route::get('/search/{tab}', [ShopSearch::class, 'index'])->name('search.index')->where('tab', 'timeline|list');

    // トーク
    Route::prefix('talk')->name('talk.')->group(function () {
        Route::get('/', [TalkController::class, 'index'])->name('index');
        Route::get('/room/{id}', [TalkController::class, 'room'])->name('room');
        Route::post('/send', [TalkController::class, 'store'])->name('send');
        Route::post('/delete', [TalkController::class, 'destroy'])->name('delete');
        Route::post('/action', [TalkController::class, 'action'])->name('action');
        Route::post('/block', [TalkController::class, 'toggleBlock'])->name('block');
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
        Route::post('/images/order', [ShopProfile::class, 'updateOrder'])->name('images.order');
        Route::delete('/image/{id}', [ShopProfile::class, 'deleteImage'])->name('image.delete');
    });

    // ★ 求人票 (Recruits)
    Route::prefix('recruits')->name('recruits.')->group(function () {
        Route::get('/status', [ShopRecruit::class, 'status'])->name('status');
        Route::get('/edit', [ShopRecruit::class, 'edit'])->name('edit');
        Route::get('/show/{id?}', [ShopRecruit::class, 'show'])->name('show');
        Route::put('/update', [ShopRecruit::class, 'update'])->name('update');
        Route::post('/toggle-status', [ShopRecruit::class, 'toggleStatus'])->name('toggle-status');
    });

    // 求人票（recruits/show と同じ内容を shop/jobdescription で提供）
    Route::get('/jobdescription/{id?}', [ShopRecruit::class, 'show'])->name('jobdescription');

    // マイページ
    Route::prefix('mypage')->name('mypage.')->group(function () {
        Route::get('/', [ShopMypage::class, 'index'])->name('index');
        Route::post('/word', [ShopMypage::class, 'updateWord'])->name('word');
        Route::get('/payment', [ShopMypage::class, 'payment'])->name('payment.index');
        Route::get('/reviews', [ShopReview::class, 'index'])->name('review.index');
        Route::post('/documents/upload', [ShopMypage::class, 'uploadDocument'])->name('documents.upload');
        Route::post('/payment/bank', [ShopMypage::class, 'updateBank'])->name('payment.bank.update');
        Route::post('/deposit/approve', [ShopMypage::class, 'approveDeposit'])->name('deposit.approve');
        Route::post('/deposit/pay', [ShopMypage::class, 'payToPlatform'])->name('deposit.pay');
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
Route::prefix('cast')->name('cast.')->middleware('member.auth')->group(function () {
    Route::get('/home', [ShopHome::class, 'index'])->name('home');
    Route::get('/profile/edit', [CastProfile::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [CastProfile::class, 'update'])->name('profile.update');
    Route::post('/profile/personality-type', [CastProfile::class, 'updatePersonalityType'])->name('profile.personality-type');
    Route::get('/shopprofileview/{id}', [CastProfile::class, 'show'])->name('shopprofileview.show');
    Route::redirect('/profile/{id}', '/cast/shopprofileview/{id}')->name('profile.show.redirect');
    Route::get('/search', fn () => redirect()->route('cast.search.index', ['tab' => 'timeline']));
    Route::get('/search/{tab}', [CastSearch::class, 'index'])->name('search.index')->where('tab', 'timeline|list|ai');
    Route::get('/recruit/{id}', [CastRecruit::class, 'show'])->name('recruit.show');
    
    Route::get('/interaction', [ShopInteraction::class, 'index'])->name('interaction.index');
    Route::get('/mypage', [CastMypage::class, 'index'])->name('mypage.index');
    Route::post('/mypage/word', [CastMypage::class, 'updateWord'])->name('mypage.word');
    Route::get('/mypage/employment', [CastMypage::class, 'employment'])->name('mypage.employment');
    Route::get('/mypage/reviews', [CastMypage::class, 'reviews'])->name('mypage.reviews');
    Route::post('/mypage/payment/bank', [CastMypage::class, 'updateBank'])->name('mypage.payment.bank.update');
    Route::get('/mypage/identity', [CastMypage::class, 'identity'])->name('mypage.identity');
    Route::post('/mypage/identity/upload', [CastMypage::class, 'uploadIdentity'])->name('mypage.identity.upload');
    Route::post('/mypage/images/upload', [CastMypage::class, 'uploadImage'])->name('mypage.images.upload');
    Route::post('/mypage/images/order', [CastMypage::class, 'updateImageOrder'])->name('mypage.images.order');
    Route::delete('/mypage/images/{id}', [CastMypage::class, 'deleteImage'])->name('mypage.images.delete');
    Route::post('/mypage/deposit/request', [CastMypage::class, 'requestDeposit'])->name('mypage.deposit.request');
    Route::post('/mypage/deposit/review', [CastMypage::class, 'postReview'])->name('mypage.deposit.review');
    Route::get('/mypage/deposit/request-target', [CastMypage::class, 'getDepositRequestTarget'])->name('mypage.deposit.request-target');
    Route::post('/mypage/deposit/confirm', [CastMypage::class, 'confirmDeposit'])->name('mypage.deposit.confirm');

    Route::prefix('talk')->name('talk.')->group(function () {
        Route::get('/', [TalkController::class, 'index'])->name('index');
        Route::get('/room/{id}', [TalkController::class, 'room'])->name('room');
        Route::post('/send', [TalkController::class, 'store'])->name('send');
        Route::post('/delete', [TalkController::class, 'destroy'])->name('delete');
        Route::post('/action', [TalkController::class, 'action'])->name('action');
        Route::post('/block', [TalkController::class, 'toggleBlock'])->name('block');
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