<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\AdminOperationalSummaryService;
use App\Services\DocumentReviewService;
use App\Services\ReviewPortalService;
use Illuminate\Support\Facades\Auth;
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

        View::composer('*', function ($view) {
            $notifications = [];
            $unreadNewsCount = 0;

            $todoList = [];
            if (request()->is('shop/*') && Auth::guard('shop')->check()) {
                $shopId = (string) Auth::guard('shop')->user()->shop_id;
                $reviewPortalService = app(ReviewPortalService::class);
                $notifications = $reviewPortalService->getShopReviewNotifications($shopId);
                $unreadNewsCount = count($notifications);
                $todoList = app(DocumentReviewService::class)->getShopPortalTodoMessages($shopId);
            }

            $view->with('notifications', $notifications);
            $view->with('unreadNewsCount', $unreadNewsCount);
            $view->with('todoList', $todoList);
        });

        View::composer('layouts.admin', function ($view) {
            $svc = app(AdminOperationalSummaryService::class);
            $view->with('adminOperationBadges', $svc->getOperationBadgeCounts());
            $view->with('adminOperationAchievements', $svc->getOperationAchievementCounts());
            $notify = $svc->getNotificationsForLayout(30);
            $view->with('adminNotifications', $notify['items']);
            $view->with('adminNotificationCount', $notify['total_count']);
        });

    }
}
