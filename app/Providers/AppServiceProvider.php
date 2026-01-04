<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\LineNotifyService;
use Illuminate\Support\Facades\Request;

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
        //$this->app->singleton(LineNotifyService::class, function ($app) {
        //    return new LineNotifyService();
        //});

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


    }
}
