<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SalesController extends Controller
{
    /**
     * 売上管理（サブスク／仲介料）のサマリー画面
     *
     * 実データとの連携は後続で行う前提で、まずはレイアウトを用意する。
     */
    public function index()
    {
        $summary = [
            'subscription_monthly_total' => 0,
            'commission_monthly_total' => 0,
            'subscription_last_month_total' => 0,
            'commission_last_month_total' => 0,
        ];

        return view('admin.sales.index', [
            'summary' => $summary,
        ]);
    }
}

