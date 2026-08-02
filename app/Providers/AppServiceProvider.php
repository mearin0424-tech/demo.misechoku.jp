<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\AdminOperationalSummaryService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        //app()->bind('FileConsts', App\Consts\FileConsts::class);

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Paginator::useBootstrap();

        Request::macro('isMobile', function () {
            $userAgent = $this->header('User-Agent');
            return preg_match('/Mobile|Android|iPhone|iPad/', $userAgent);
        });

        // Blade ディレクティブ：オーナー限定表示（店舗ログイン時のみ role=1 で true）
        //   使い方: @shopowner ... @endshopowner
        //   スタッフ（role=2）にはボタン・リンクを隠したい場面で使用。
        //   サーバ側の権限は shop.owner middleware / controller のガードで別途強制済み。
        Blade::if('shopowner', function () {
            $manager = auth()->guard('shop')->user();
            return $manager && (int) $manager->role === \App\Models\ShopManager::ROLE_OWNER;
        });

        // ヘッダーバッジ用の $notifications / $operationalNotices / $unreadNewsCount / $todoList
        // は App\Http\Middleware\InjectHeaderBadges が View::share() で全ページに注入する。
        // ここで view composer を再宣言すると上書きが発生し、実データが表示されなくなる
        // （旧実装ではレビュー通知のみ・書類 TODO のみで、TALK/LIKE 等の新規通知が消えていた）。
        // 追加のバッジデータが必要になったら、middleware 側で共有すること。

        View::composer('layouts.admin', function ($view) {
            $svc = app(AdminOperationalSummaryService::class);
            $view->with('adminOperationBadges', $svc->getOperationBadgeCounts());
            $view->with('adminOperationAchievements', $svc->getOperationAchievementCounts());
            $notify = $svc->getNotificationsForLayout(30);
            $view->with('adminNotifications', $notify['items']);
            $view->with('adminNotificationCount', $notify['total_count']);

            $inbox = $svc->getInboxForLayout(20);
            $view->with('adminInboxItems', $inbox['items']);
            $view->with('adminInboxUnread', $inbox['unread']);
        });

    }
}
