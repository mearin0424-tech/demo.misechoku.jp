<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            //return route('login');
            //foreach (['user', 'shop'] as $user) {
            //    if ($request->routeIs("{$user}.*")) {
            //       return route("{$user}.login.index");
            //    }
            //}
            if ($request->routeIs("user.*")) {
                   return route("user.index");
            }
            if ($request->routeIs("shop.*")) {
                   return route("shop.index");
            }
            if ($request->routeIs("member.*")) {
                   return route("member.index");
            }
        }
    }
}
