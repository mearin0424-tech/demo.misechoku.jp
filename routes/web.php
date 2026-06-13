<?php
// prj/routes/web.php

use Illuminate\Support\Facades\Route;

// 蜈ｱ騾壹・隱崎ｨｼ邉ｻ
use App\Http\Controllers\Common\PageController;
use App\Http\Controllers\Common\ColumnArticleController;
use App\Http\Controllers\Common\SupportNoticeController;
use App\Http\Controllers\Common\SettingController;
use App\Http\Controllers\Common\TalkTemplateController;
use App\Http\Controllers\Common\DemoLoginController;
use App\Http\Controllers\Common\BankLookupController;
use App\Http\Controllers\Common\RegistrationController;
use App\Http\Controllers\Auth\Cast\LoginController as CastLogin;
use App\Http\Controllers\Auth\LineLoginController as LineLogin;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\Common\TalkController as TalkController;
use App\Http\Controllers\Common\NotificationController as CommonNotification;

// 邂｡逅・・ｼ医ヰ繝・け繧ｪ繝輔ぅ繧ｹ・・
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
use App\Http\Controllers\Admin\PolicyController as AdminPolicy;
use App\Http\Controllers\Admin\NotificationSpecController as AdminNotificationSpec;
use App\Http\Controllers\Admin\CharacterGuideController as AdminCharacterGuide;

// 蠎苓・蛛ｴ
use App\Http\Controllers\Shops\HomeController as ShopHome;
use App\Http\Controllers\Shops\SearchController as ShopSearch;
use App\Http\Controllers\Shops\MypageController as ShopMypage;
use App\Http\Controllers\Shops\ProfileController as ShopProfile;
use App\Http\Controllers\Shops\RecruitmentController as ShopRecruit;
use App\Http\Controllers\Shops\ReviewController as ShopReview;
use App\Http\Controllers\Shops\InteractionController as ShopInteraction;
use App\Http\Controllers\Shops\StaffController as ShopStaff;

// 繧ｭ繝｣繧ｹ繝亥・
use App\Http\Controllers\Casts\ProfileController as CastProfile;
use App\Http\Controllers\Casts\MypageController as CastMypage;
use App\Http\Controllers\Casts\SearchController as CastSearch;
use App\Http\Controllers\Casts\RecruitmentController as CastRecruit;
use App\Http\Controllers\Casts\AiChatController as CastAiChat;

/*
|--------------------------------------------------------------------------
| 繝輔ぃ繝薙さ繝ｳ・・04髦ｲ豁｢・壹い繧､繧ｳ繝ｳ縺ｯ layout 縺ｮ link 縺ｧ謖・ｮ夲ｼ・
|--------------------------------------------------------------------------
*/
Route::get('/favicon.ico', function () {
    return response('', 204);
})->name('favicon');

/*
|--------------------------------------------------------------------------
| PWA Manifest・医せ繝槭・縺ｮ繧､繝ｳ繧ｹ繝医・繝ｫ蛻､螳夂畑・哺IME 縺ｨ逶ｸ蟇ｾURL・・
|--------------------------------------------------------------------------
*/
Route::get('/manifest.json', function () {
    // 未ログインでも 200 で開ける URL にする（Chrome のインストール判定が通りやすい）
    $startUrl = '/login?utm_source=pwa';
    $manifest = [
        'name' => 'ミセチョク',
        'short_name' => 'ミセチョク',
        'description' => 'ミセチョク - デモ',
        'start_url' => $startUrl,
        'scope' => '/',
        'id' => $startUrl,
        'display' => 'standalone',
        'display_override' => ['standalone', 'minimal-ui', 'browser'],
        'orientation' => 'portrait-primary',
        'theme_color' => '#190509',
        'background_color' => '#190509',
        'lang' => 'ja',
        'prefer_related_applications' => false,
        'icons' => [
            ['src' => '/assets/images/pwa/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/assets/images/pwa/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
            ['src' => '/assets/images/pwa/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/assets/images/pwa/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ],
    ];
    return response()->json($manifest, 200, [
        'Content-Type' => 'application/manifest+json',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('manifest');

/*
|--------------------------------------------------------------------------
| 繝ｪ繝繧､繝ｬ繧ｯ繝郁ｨｭ螳・
|--------------------------------------------------------------------------
*/
// ルートはサービス紹介の welcome へ
Route::redirect('/', '/welcome');
Route::view('/welcome', 'common.welcome')->name('welcome');
Route::view('/welcome/shop', 'lp.shop')->name('welcome.shop');
Route::redirect('/shop', '/shop/home');
Route::redirect('/cast', '/cast/home');
Route::redirect('/bk', '/admin');
Route::get('/bk/{path}', function ($path) {
    return redirect('/admin/' . $path);
})->where('path', '.*');

/*
|--------------------------------------------------------------------------
| Admin Portal (邂｡逅・・ｰら畑)
|--------------------------------------------------------------------------
|
| 蠎苓・繝ｻ繧ｭ繝｣繧ｹ繝医→縺ｯ蛻･縺ｮ繝励Ξ繝輔ぅ繝・け繧ｹ `/admin` 縺ｧ邂｡逅・判髱｢繧呈署萓帙☆繧九・
| 隱崎ｨｼ縺ｾ繧上ｊ縺ｯ莉雁ｾ梧僑蠑ｵ縺励ｄ縺吶＞繧医≧縺ｫ繝ｫ繝ｼ繝医ｒ蛻・屬縺励※縺翫￥縲・
|
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // 繝ｭ繧ｰ繧､繝ｳ縺ｯ蜈ｱ騾・/login 縺ｫ邨ｱ荳・医Μ繝繧､繝ｬ繧ｯ繝医・縺ｿ・・
    Route::get('/login', fn () => redirect('/login'))->name('login');
    Route::post('/logout', [AdminAuth::class, 'logout'])->name('logout');

    // 邂｡逅・判髱｢譛ｬ菴・
    Route::middleware([])->group(function () {
        Route::get('/', [AdminDashboard::class, 'index'])
            ->middleware('admin.permission:dashboard.view')
            ->name('dashboard');

        // 隲区ｱよ嶌逋ｺ陦・
        Route::middleware('admin.permission:operations.invoices')->group(function () {
            Route::get('/invoices', [AdminInvoice::class, 'index'])->name('invoices.index');
            Route::post('/invoices/issue-manual', [AdminInvoice::class, 'issueManual'])->name('invoices.issue-manual');
            Route::get('/invoices/template-settings', [AdminInvoice::class, 'templateSettings'])->name('invoices.template-settings');
            Route::post('/invoices/template-settings', [AdminInvoice::class, 'updateTemplateSettings'])->name('invoices.template-settings.update');
        });

        // 蜈･驥代・謖ｯ霎ｼ邂｡逅・
        Route::middleware('admin.permission:operations.deposits')->group(function () {
            Route::get('/deposits/invoice-template/download', [AdminDeposit::class, 'downloadInvoiceTemplate'])->name('deposits.invoice-template.download');
            Route::get('/deposits', [AdminDeposit::class, 'index'])->name('deposits.index');
            Route::get('/deposits/{deposit}/invoice', [AdminDeposit::class, 'showInvoice'])->name('deposits.invoice.show');
            Route::get('/deposits/{deposit}/invoice/pdf', [AdminDeposit::class, 'downloadInvoicePdf'])->name('deposits.invoice.pdf');
            Route::post('/deposits/{deposit}/invoice', [AdminDeposit::class, 'issueInvoice'])->name('deposits.invoice.issue');
            Route::post('/deposits/{deposit}/confirm-shop-payment', [AdminDeposit::class, 'confirmShopPayment'])->name('deposits.shop-payment.confirm');
            Route::post('/deposits/{deposit}/transfer-start', [AdminDeposit::class, 'transferStart'])->name('deposits.transfer-start');
            Route::post('/deposits/{deposit}/transfer-complete', [AdminDeposit::class, 'transferComplete'])->name('deposits.transfer-complete');
            Route::post('/deposits/{deposit}/payment-task-invalidate', [AdminDeposit::class, 'paymentTaskInvalidate'])->name('deposits.payment-task.invalidate');
            Route::post('/deposits/{deposit}/payment-task-refund-flag', [AdminDeposit::class, 'paymentTaskRefundFlag'])->name('deposits.payment-task.refund-flag');
            Route::post('/deposits/{deposit}/transfer-cast', [AdminDeposit::class, 'transferCast'])->name('deposits.cast-transfer.execute');
        });

        // 螢ｲ荳顔ｮ｡逅・
        Route::get('/sales', [AdminSales::class, 'index'])
            ->middleware('admin.permission:analytics.sales')
            ->name('sales.index');

        // 繝槭せ繧ｿ險ｭ螳夂ｮ｡逅・
        Route::middleware('admin.permission:master.masters')->group(function () {
            Route::get('/masters', [AdminMaster::class, 'index'])->name('masters.index');
            Route::post('/masters/catalogs/{catalogKey}', [AdminMaster::class, 'storeCatalog'])->name('masters.catalogs.store');
            Route::patch('/masters/catalogs/{catalogKey}/{recordId}', [AdminMaster::class, 'updateCatalog'])->name('masters.catalogs.update');
            Route::patch('/masters/catalogs/{catalogKey}/{recordId}/sort-order', [AdminMaster::class, 'updateSortOrder'])->name('masters.catalogs.sort-order');
            Route::delete('/masters/catalogs/{catalogKey}/{recordId}', [AdminMaster::class, 'destroyCatalog'])->name('masters.catalogs.destroy');
        });

        // 通知・タスク仕様の確認／変更
        Route::middleware('admin.permission:master.notification_spec')->group(function () {
            Route::get('/notification-spec', [AdminNotificationSpec::class, 'index'])->name('notification-spec.index');
            Route::put('/notification-spec/notifications/{key}', [AdminNotificationSpec::class, 'updateNotification'])
                ->where('key', '[A-Za-z0-9_.\-]+')
                ->name('notification-spec.notifications.update');
            Route::put('/notification-spec/reminders/{key}', [AdminNotificationSpec::class, 'updateReminder'])
                ->where('key', '[A-Za-z0-9_.\-]+')
                ->name('notification-spec.reminders.update');
            Route::put('/notification-spec/tasks/{key}', [AdminNotificationSpec::class, 'updateTask'])
                ->where('key', '[A-Za-z0-9_.\-]+')
                ->name('notification-spec.tasks.update');
        });

        // 蠎苓・邂｡逅・
        Route::middleware('admin.permission:accounts.shops.view')->group(function () {
            Route::get('/shops', [AdminShop::class, 'index'])->name('shops.index');
            Route::get('/shops/{shopId}', [AdminShop::class, 'show'])->name('shops.show');
            Route::post('/shops/{shopId}/lock-private', [AdminShop::class, 'lockPrivate'])->name('shops.lock-private');
            Route::post('/shops/{shopId}/unlock-private', [AdminShop::class, 'unlockPrivate'])
                ->middleware('admin.permission:accounts.shops.private')
                ->name('shops.unlock-private');
        });
        Route::middleware('admin.permission:accounts.shops.manage')->group(function () {
            Route::post('/shops/{shopId}/suspend', [AdminShop::class, 'suspend'])->name('shops.suspend');
            Route::post('/shops/{shopId}/unsuspend', [AdminShop::class, 'unsuspend'])->name('shops.unsuspend');
        });

        // 繧ｭ繝｣繧ｹ繝育ｮ｡逅・
        Route::middleware('admin.permission:accounts.casts.view')->group(function () {
            Route::get('/casts', [AdminCast::class, 'index'])->name('casts.index');
            Route::get('/casts/{castId}', [AdminCast::class, 'show'])->name('casts.show');
            Route::post('/casts/{castId}/lock-private', [AdminCast::class, 'lockPrivate'])->name('casts.lock-private');
            Route::post('/casts/{castId}/unlock-private', [AdminCast::class, 'unlockPrivate'])
                ->middleware('admin.permission:accounts.casts.private')
                ->name('casts.unlock-private');
        });
        Route::middleware('admin.permission:accounts.casts.manage')->group(function () {
            Route::post('/casts/{castId}/suspend', [AdminCast::class, 'suspend'])->name('casts.suspend');
            Route::post('/casts/{castId}/unsuspend', [AdminCast::class, 'unsuspend'])->name('casts.unsuspend');
        });

        // NG繝ｯ繝ｼ繝臥ｮ｡逅・
        Route::middleware('admin.permission:master.ngwords')->group(function () {
            Route::get('/ngwords', [AdminNgWord::class, 'index'])->name('ngwords.index');
            Route::post('/ngwords', [AdminNgWord::class, 'store'])->name('ngwords.store');
            Route::put('/ngwords/{id}', [AdminNgWord::class, 'update'])->whereNumber('id')->name('ngwords.update');
            Route::delete('/ngwords/{id}', [AdminNgWord::class, 'destroy'])->whereNumber('id')->name('ngwords.destroy');
        });

        // オコジョガイド設定
        Route::middleware('admin.permission:master.character_guide')->group(function () {
            Route::get('/character-guide', [AdminCharacterGuide::class, 'index'])->name('character-guide.index');
            Route::put('/character-guide', [AdminCharacterGuide::class, 'update'])->name('character-guide.update');
        });

        // 縺顔衍繧峨○邂｡逅・
        Route::middleware('admin.permission:content.notices')->group(function () {
            Route::get('/notices', [AdminNotice::class, 'index'])->name('notices.index');
            Route::get('/notices/create', [AdminNotice::class, 'create'])->name('notices.create');
            Route::post('/notices', [AdminNotice::class, 'store'])->name('notices.store');
            Route::get('/notices/{notice}/edit', [AdminNotice::class, 'edit'])->name('notices.edit');
            Route::put('/notices/{notice}', [AdminNotice::class, 'update'])->name('notices.update');
            Route::delete('/notices/{notice}', [AdminNotice::class, 'destroy'])->name('notices.destroy');
        });

        // 繧ｳ繝ｩ繝邂｡逅・
        Route::middleware('admin.permission:content.columns')->group(function () {
            Route::get('/columns', [AdminColumn::class, 'index'])->name('columns.index');
            Route::get('/columns/create', [AdminColumn::class, 'create'])->name('columns.create');
            Route::post('/columns', [AdminColumn::class, 'store'])->name('columns.store');
            Route::get('/columns/{column}/edit', [AdminColumn::class, 'edit'])->name('columns.edit');
            Route::put('/columns/{column}', [AdminColumn::class, 'update'])->name('columns.update');
            Route::delete('/columns/{column}', [AdminColumn::class, 'destroy'])->name('columns.destroy');
        });

        // サポート問い合わせ管理
        Route::middleware('admin.permission:content.notices')->group(function () {
            Route::get('/support-inquiries', [\App\Http\Controllers\Admin\SupportInquiryController::class, 'index'])->name('support-inquiries.index');
            Route::get('/support-inquiries/{inquiry}', [\App\Http\Controllers\Admin\SupportInquiryController::class, 'show'])->name('support-inquiries.show');
            Route::post('/support-inquiries/{inquiry}/status', [\App\Http\Controllers\Admin\SupportInquiryController::class, 'updateStatus'])->name('support-inquiries.status');
            Route::post('/support-inquiries/{inquiry}/note', [\App\Http\Controllers\Admin\SupportInquiryController::class, 'updateNote'])->name('support-inquiries.note');
        });

        // 隲区ｱゅ・謖ｯ霎ｼ繧ｿ繧ｹ繧ｯ邂｡逅・
        Route::get('/tasks', [AdminTask::class, 'index'])
            ->middleware('admin.permission:dashboard.view')
            ->name('tasks.index');

        // 譛ｬ莠ｺ繝ｻ譖ｸ鬘槫ｯｩ譟ｻ
        Route::middleware('admin.permission:operations.verification')->group(function () {
            Route::get('/verification', [AdminVerification::class, 'index'])->name('verification.index');
            Route::post('/verification/cast/{document}/approve', [AdminVerification::class, 'approveCast'])->name('verification.cast.approve');
            Route::post('/verification/cast/{document}/reject', [AdminVerification::class, 'rejectCast'])->name('verification.cast.reject');
            Route::post('/verification/shopdoc/{document}/approve', [AdminVerification::class, 'approveShopDocument'])->name('verification.shopdoc.approve');
            Route::post('/verification/shopdoc/{document}/reject', [AdminVerification::class, 'rejectShopDocument'])->name('verification.shopdoc.reject');
            // 機密ファイル配信（private ディスクから直接ストリーム。Web 直アクセス禁止）
            Route::get('/verification/cast/{document}/file/{side}', [AdminVerification::class, 'viewCastFile'])
                ->where('side', 'front|back')
                ->whereNumber('document')
                ->name('verification.cast.file');
            Route::get('/verification/shopdoc/{document}/file', [AdminVerification::class, 'viewShopFile'])
                ->whereNumber('document')
                ->name('verification.shopdoc.file');
            // 保持期間ポリシーに基づく完全削除（運営の手動操作のみ。自動削除は行わない）
            Route::post('/verification/cast/{document}/purge', [AdminVerification::class, 'purgeCast'])
                ->whereNumber('document')
                ->name('verification.cast.purge');
            Route::post('/verification/shopdoc/{document}/purge', [AdminVerification::class, 'purgeShopDocument'])
                ->whereNumber('document')
                ->name('verification.shopdoc.purge');
        });

        // 蝠上＞蜷医ｏ縺帷ｮ｡逅・
        Route::middleware('admin.permission:operations.inquiries')->group(function () {
            Route::get('/inquiries', [AdminInquiry::class, 'index'])->name('inquiries.index');
            Route::get('/inquiries/{id}', [AdminInquiry::class, 'show'])->whereNumber('id')->name('inquiries.show');
        });

        // 繧｢繧ｫ繧ｦ繝ｳ繝育ｮ｡逅・ｼ磯°蝟ｶ・・
        Route::middleware('admin.permission:accounts.admins')->group(function () {
            Route::get('/admin-accounts', [AdminAccount::class, 'index'])->name('admin-accounts.index');
            Route::get('/admin-accounts/operation-log', [AdminAccount::class, 'operationLog'])->name('admin-accounts.operation-log');
            Route::get('/admin-accounts/roles/{role}/edit', [AdminAccount::class, 'editRole'])
                ->where('role', 'admin|staff')
                ->name('admin-accounts.roles.edit');
            Route::put('/admin-accounts/roles/{role}', [AdminAccount::class, 'updateRole'])
                ->where('role', 'admin|staff')
                ->name('admin-accounts.roles.update');
        });

        // 驕句霧蜿｣蠎ｧ諠・ｱ
        Route::middleware('admin.permission:operations.deposits')->group(function () {
            Route::get('/bank', [AdminBank::class, 'index'])->name('bank.index');
            Route::post('/bank', [AdminBank::class, 'store'])->name('bank.store');
        });

        // 規約管理（運営協会／利用規約／プライバシーポリシー）
        Route::middleware('admin.permission:policies.manage')->group(function () {
            Route::get('/policies/{key}', [AdminPolicy::class, 'show'])
                ->where('key', 'about|terms|privacy')
                ->name('policies.show');
            Route::get('/policies/{key}/edit', [AdminPolicy::class, 'edit'])
                ->where('key', 'about|terms|privacy')
                ->name('policies.edit');
            Route::put('/policies/{key}', [AdminPolicy::class, 'update'])
                ->where('key', 'about|terms|privacy')
                ->name('policies.update');
            Route::post('/policies/{key}/toggle-lock', [AdminPolicy::class, 'toggleLock'])
                ->where('key', 'about|terms|privacy')
                ->name('policies.toggle-lock');
        });
    });
});

/*
|--------------------------------------------------------------------------
| 1. Public & Guest Routes (LP繝ｻ隱崎ｨｼ)
|--------------------------------------------------------------------------
*/
// 繝・Δ逕ｨ蜈ｱ騾壹Ο繧ｰ繧､繝ｳ
Route::get('/login', [DemoLoginController::class, 'show'])->name('login.demo');
Route::post('/login', [DemoLoginController::class, 'login'])->name('login.demo.post');

// LINE繝ｭ繧ｰ繧､繝ｳ
Route::get('/login/line', [LineLogin::class, 'redirect'])->name('login.line.redirect');
Route::get('/login/line/callback', [LineLogin::class, 'callback'])->name('login.line.callback');

// Messaging API Webhook（LINE Login のコールバックとは別。検証は同一チャネルの Channel secret が .env と一致している必要あり）
Route::post('/line/webhook', LineWebhookController::class)->name('line.webhook');

Route::name('pages.')->group(function () {
    Route::get('/about', [PageController::class, 'about'])->name('official.about');
    Route::get('/terms', [PageController::class, 'terms'])->name('official.terms');
    Route::get('/privacy', [PageController::class, 'privacy'])->name('official.privacy');
    Route::get('/support/column', [ColumnArticleController::class, 'index'])->name('support.column');
    Route::get('/support/column/{slug}', [ColumnArticleController::class, 'show'])->name('support.column.show');
    Route::get('/support/notices', [SupportNoticeController::class, 'index'])->name('support.notices');
    Route::get('/support/notices/{slug}', [SupportNoticeController::class, 'show'])->name('support.notices.show');
    Route::get('/support/form', [PageController::class, 'supportForm'])->name('support.form');
    Route::post('/support/form', [\App\Http\Controllers\Common\SupportInquiryController::class, 'store'])
        ->middleware('throttle:5,60')  // 1 ユーザー 60 分 5 件まで（連投スパム対策）
        ->name('support.form.submit');
});

// 停止中アカウント向けランディング
Route::get('/account/suspended', [\App\Http\Controllers\Common\SuspendedController::class, 'show'])
    ->name('account.suspended');

// おしらせ既読 API（ログイン中のロール自動判定。cast/shop/admin 共通）
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/unread-count', [CommonNotification::class, 'unreadCount'])->name('unread-count');
    Route::post('/{id}/read', [CommonNotification::class, 'markRead'])
        ->whereNumber('id')
        ->name('read');
    Route::post('/read-all', [CommonNotification::class, 'markAllRead'])->name('read-all');
});

Route::prefix('share')->name('share.')->group(function () {
    Route::get('/recruit/{id}', [CastRecruit::class, 'publicShow'])->name('recruit.show');
    Route::get('/cast/{id}', [CastProfile::class, 'publicShow'])->name('cast.show');
});

// 險ｭ螳夂ｳｻ・亥・騾夲ｼ・
Route::prefix('setting')->name('setting.')->group(function () {
    Route::get('/notification', [SettingController::class, 'notification'])->name('notification');
    Route::post('/notification', [SettingController::class, 'updateNotification'])->name('notification.update');
    Route::get('/line/link', [LineLogin::class, 'redirectLink'])->name('line.link');
    Route::get('/account', [SettingController::class, 'account'])->name('account');
    Route::post('/account/email', [SettingController::class, 'updateEmail'])->name('account.email.update');
    Route::post('/account/password', [SettingController::class, 'updatePassword'])->name('account.password.update');
    Route::post('/account/line/unlink', [SettingController::class, 'unlinkLine'])->name('account.line.unlink');
    Route::post('/account/withdraw', [SettingController::class, 'withdraw'])->name('account.withdraw');
    // 探索拠点（現在地／パスポート）
    Route::post('/location', [\App\Http\Controllers\Common\LocationController::class, 'store'])->name('location.store');
    Route::delete('/location', [\App\Http\Controllers\Common\LocationController::class, 'destroy'])->name('location.destroy');

    // トーク定型文（キャスト／店舗ともに利用）— トーク画面のモーダルから JSON で操作
    Route::prefix('talk-templates')->name('talk-templates.')->group(function () {
        Route::get('/slots', [TalkTemplateController::class, 'slots'])->name('slots');
        Route::put('/slot/{slot}', [TalkTemplateController::class, 'saveSlot'])->whereNumber('slot')->name('slot.save');
        Route::delete('/slot/{slot}', [TalkTemplateController::class, 'resetSlot'])->whereNumber('slot')->name('slot.reset');
    });
});

// 繝励Λ繝ｳ險ｭ螳夲ｼ亥ｺ苓・蟆ら畑繝ｻ繝・Δ逕ｨ・・
Route::get('/subscription', [SettingController::class, 'subscription'])->name('subscription');

// 譛ｪ螳溯｣・判髱｢繝ｻ讖溯・逕ｨ・・aintenance-screen.png 繧定｡ｨ遉ｺ・・
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
| PWA Push 騾夂衍 API・亥酔荳繧ｪ繝ｪ繧ｸ繝ｳ繝ｻCSRF 縺ゅｊ・・
|--------------------------------------------------------------------------
*/
Route::prefix('api/push')->name('push.')->group(function () {
    Route::get('vapid-public-key', [\App\Http\Controllers\Api\PushController::class, 'vapidPublicKey'])->name('vapid');
    Route::post('subscribe', [\App\Http\Controllers\Api\PushController::class, 'subscribe'])->name('subscribe');
    Route::post('send-test', [\App\Http\Controllers\Api\PushController::class, 'sendTest'])->name('send-test');
});

Route::prefix('api/favorites')->name('api.favorites.')->group(function () {
    Route::post('toggle', [\App\Http\Controllers\Api\FavoriteController::class, 'toggle'])->name('toggle');
});

Route::prefix('api/bank-lookup')->name('api.bank-lookup.')->group(function () {
    Route::get('banks', [BankLookupController::class, 'banks'])->name('banks');
    Route::get('branches', [BankLookupController::class, 'branches'])->name('branches');
});

// 住所→緯度経度のジオコーディング（保存はしない、プレビュー用）
Route::get('/api/geocoding/lookup', [\App\Http\Controllers\Common\LocationController::class, 'lookup'])
    ->name('api.geocoding.lookup');
Route::get('/api/geocoding/suggest', [\App\Http\Controllers\Common\LocationController::class, 'suggest'])
    ->name('api.geocoding.suggest');

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
| 2. Shop Portal (蠎苓・蟆ら畑)
|--------------------------------------------------------------------------
*/
Route::prefix('shop')->name('shop.')->middleware('shop.auth')->group(function () {

    Route::get('/home', [ShopHome::class, 'index'])->name('home');
    Route::get('/search', [ShopSearch::class, 'index'])->name('search.index');
    Route::get('/search/{tab}', fn ($tab) => redirect()->route('shop.search.index'))->where('tab', 'timeline|list');
    Route::post('/search-preferences', [ShopSearch::class, 'savePreferences'])->name('search-preferences.save');

    // 繝医・繧ｯ
    Route::prefix('talk')->name('talk.')->group(function () {
        Route::get('/', [TalkController::class, 'index'])->name('index');
        Route::get('/room/{id}', [TalkController::class, 'room'])->name('room');
        Route::post('/send', [TalkController::class, 'store'])->name('send');
        Route::post('/delete', [TalkController::class, 'destroy'])->name('delete');
        Route::post('/action', [TalkController::class, 'action'])->name('action');
        Route::post('/block', [TalkController::class, 'toggleBlock'])->name('block');
    });

    // 縺､縺ｪ縺後ｊ
    Route::prefix('interaction')->name('interaction.')->group(function () {
        Route::get('/', [ShopInteraction::class, 'index'])->name('index');
        Route::get('/keep', [ShopInteraction::class, 'keep'])->name('keep');
        Route::get('/like', [ShopInteraction::class, 'like'])->name('like');
    });

    // 繧ｭ繝｣繧ｹ繝医・繝励Ο繝輔ぅ繝ｼ繝ｫ髢ｲ隕ｧ・亥ｺ苓・縺九ｉ隕九ｋ・・
    Route::get('/castprofileview/{id}', [CastProfile::class, 'show'])->name('castprofileview.show');

    // 繝励Ο繝輔ぅ繝ｼ繝ｫ・医く繝｣繧ｹ繝育畑邱ｨ髮・ｼ捏hop/profile/edit縲∝ｺ苓・逕ｨ縺ｯ store 繧ｵ繝悶ヱ繧ｹ・・
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [ShopProfile::class, 'edit'])->name('edit');
        Route::post('/update', [ShopProfile::class, 'update'])->name('update');
        Route::post('/suggest-stations', [ShopProfile::class, 'suggestStations'])->name('suggest-stations');
        Route::post('/upload-image', [ShopProfile::class, 'uploadImage'])->name('upload.image');
        Route::post('/images/order', [ShopProfile::class, 'updateOrder'])->name('images.order');
        Route::delete('/image/{id}', [ShopProfile::class, 'deleteImage'])->name('image.delete');
    });

    // 笘・豎ゆｺｺ逾ｨ (Recruits)
    Route::prefix('recruits')->name('recruits.')->group(function () {
        Route::post('/application/hired-wage', [ShopRecruit::class, 'updateApplicationHiredWage'])->name('application-hired-wage');
        Route::get('/edit', [ShopRecruit::class, 'edit'])->name('edit');
        Route::get('/show/{id?}', [ShopRecruit::class, 'show'])->name('show');
        Route::put('/update', [ShopRecruit::class, 'update'])->name('update');
        Route::post('/toggle-status', [ShopRecruit::class, 'toggleStatus'])->name('toggle-status');
    });

    // 豎ゆｺｺ逾ｨ・・ecruits/show 縺ｨ蜷後§蜀・ｮｹ繧・shop/jobdescription 縺ｧ謠蝉ｾ幢ｼ・
    Route::get('/jobdescription/{id?}', [ShopRecruit::class, 'show'])->name('jobdescription');

    // 繝槭う繝壹・繧ｸ
    Route::prefix('mypage')->name('mypage.')->group(function () {
        Route::get('/', [ShopMypage::class, 'index'])->name('index');
        Route::post('/word', [ShopMypage::class, 'updateWord'])->name('word');
        Route::post('/search-location', [ShopMypage::class, 'updateSearchLocation'])->name('search-location.update');
        Route::get('/management', [ShopRecruit::class, 'management'])->name('management');
        Route::get('/reviews', [ShopReview::class, 'index'])->name('review.index');
        Route::get('/documents/{type}', [ShopMypage::class, 'viewLicenseDocument'])->name('documents.show')->whereIn('type', ['business', 'entertainment']);
        Route::post('/documents/upload', [ShopMypage::class, 'uploadDocument'])->name('documents.upload');
        Route::post('/documents/request-review', [ShopMypage::class, 'requestDocumentReview'])->name('documents.request-review');
        Route::post('/documents/withdraw-review', [ShopMypage::class, 'withdrawDocumentReview'])->name('documents.withdraw-review');
        Route::post('/payment/bank', [ShopMypage::class, 'updateBank'])->name('payment.bank.update');
        Route::post('/deposit/approve', [ShopMypage::class, 'approveDeposit'])->name('deposit.approve');
        Route::post('/deposit/pay', [ShopMypage::class, 'payToPlatform'])->name('deposit.pay');

        // スタッフ管理（1店舗に複数の店舗ログインアカウントを持たせる）
        Route::prefix('staff')->name('staff.')->group(function () {
            Route::get('/', [ShopStaff::class, 'index'])->name('index');
            Route::get('/create', [ShopStaff::class, 'create'])->name('create');
            Route::post('/', [ShopStaff::class, 'store'])->name('store');
            Route::delete('/{id}', [ShopStaff::class, 'destroy'])->name('destroy')->where('id', 'm[0-9]+');
        });
    });

    // 繝ｬ繝薙Η繝ｼ蜈ｬ髢九・髱櫁｡ｨ遉ｺ縺ｮ蛻・ｊ譖ｿ縺茨ｼ・eviews 逕ｻ髱｢縺ｮ JS 縺九ｉ菴ｿ逕ｨ・・
    Route::post('/mypage/review/update', [ShopReview::class, 'updateStatus'])->name('review.update');

    // SUPPORT 繝壹・繧ｸ・亥ｺ苓・蜷代￠・・
    Route::get('/htu', [PageController::class, 'htu'])->name('htu');

    Route::get('/column', [ColumnArticleController::class, 'index'])->name('column.index');
    Route::get('/column/{slug}', [ColumnArticleController::class, 'show'])->name('column.show');
});

/*
|--------------------------------------------------------------------------
| 3. Cast Portal (繧ｭ繝｣繧ｹ繝亥ｰら畑)
|--------------------------------------------------------------------------
*/
Route::prefix('cast')->name('cast.')->middleware('member.auth')->group(function () {
    Route::get('/home', [ShopHome::class, 'index'])->name('home');
    Route::get('/profile/edit', [CastProfile::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [CastProfile::class, 'update'])->name('profile.update');
    Route::post('/profile/personality-type', [CastProfile::class, 'updatePersonalityType'])->name('profile.personality-type');
    Route::get('/search', fn () => redirect()->route('cast.search.index', ['tab' => 'list']));
    Route::get('/search/{tab}', [CastSearch::class, 'index'])->name('search.index')->where('tab', 'search|ai|timeline|list');
    Route::post('/search-preferences', [CastSearch::class, 'savePreferences'])->name('search-preferences.save');
    Route::post('/search/ai-chat', [CastAiChat::class, 'respond'])->name('search.ai-chat');
    Route::get('/recruit/{id}', [CastRecruit::class, 'show'])->name('recruit.show');
    
    Route::get('/interaction', [ShopInteraction::class, 'index'])->name('interaction.index');
    Route::get('/mypage', [CastMypage::class, 'index'])->name('mypage.index');
    Route::post('/mypage/word', [CastMypage::class, 'updateWord'])->name('mypage.word');
    Route::post('/mypage/search-location', [CastMypage::class, 'updateSearchLocation'])->name('mypage.search-location.update');
    Route::get('/mypage/management', [CastMypage::class, 'employment'])->name('mypage.management');
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

    // SUPPORT 繝壹・繧ｸ・医く繝｣繧ｹ繝亥髄縺托ｼ・
    Route::get('/htu', [PageController::class, 'htu'])->name('htu');

    Route::get('/column', [ColumnArticleController::class, 'index'])->name('column.index');
    Route::get('/column/{slug}', [ColumnArticleController::class, 'show'])->name('column.show');
});

/* 蜀・Κ繧ｭ繝｣繝・す繝･繧ｯ繝ｪ繧｢逕ｨ */
Route::get('/clear-route', function() {
    \Artisan::call('route:clear');
    return "Route cache cleared!";
});
