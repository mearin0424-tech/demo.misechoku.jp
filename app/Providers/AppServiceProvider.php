<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\AdminOperationalSummaryService;
use App\Services\DocumentReviewService;
use App\Services\ReviewPortalService;
use App\Models\Notice;
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
            $operationalNotices = [];
            $unreadNewsCount = 0;

            $todoList = [];
            if (request()->is('shop/*') && Auth::guard('shop')->check()) {
                $shopId = (string) Auth::guard('shop')->user()->shop_id;
                $reviewPortalService = app(ReviewPortalService::class);
                $notifications = $reviewPortalService->getShopReviewNotifications($shopId);
                $unreadNewsCount = count($notifications);
                $todoList = app(DocumentReviewService::class)->getShopPortalTodoMessages($shopId);
            }

            try {
                $noticeQuery = Notice::query()
                    ->published()
                    ->orderByDesc('published_at')
                    ->orderByDesc('id');

                if (request()->is('cast/*')) {
                    $noticeQuery->forCast();
                } elseif (request()->is('shop/*')) {
                    $noticeQuery->forShop();
                } else {
                    $noticeQuery->forGuest();
                }

                $operationalNotices = $noticeQuery
                    ->limit(5)
                    ->get(['title', 'slug', 'body', 'published_at'])
                    ->map(fn (Notice $notice) => [
                        'title' => $notice->title,
                        'body' => $notice->body,
                        'url' => route('pages.support.notices.show', ['slug' => $notice->slug]),
                        'published_at' => optional($notice->published_at)->format('Y/m/d'),
                    ])
                    ->all();
            } catch (\Throwable $e) {
                $operationalNotices = [];
            }

            $view->with('notifications', $notifications);
            $view->with('operationalNotices', $operationalNotices);
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
