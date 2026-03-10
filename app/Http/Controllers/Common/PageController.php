<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function about()
    {
        return view('common.maintenance');
    }

    public function terms()
    {
        return view('common.maintenance');
    }

    public function privacy()
    {
        return view('common.maintenance');
    }

    /**
     * サービスの特徴（キャスト／店舗共通）
     */
    public function feature()
    {
        $isCast = request()->is('cast/*');
        return view('common.support.feature', compact('isCast'));
    }

    /**
     * ご利用ガイド（キャスト／店舗共通）
     */
    public function htu()
    {
        $isCast = request()->is('cast/*');
        return view('common.support.htu', compact('isCast'));
    }

    /**
     * よくある質問（FAQ）（キャスト／店舗共通）
     */
    public function faq()
    {
        $isCast = request()->is('cast/*');
        return view('common.support.faq', compact('isCast'));
    }

    /**
     * お役立ちコラム
     */
    public function column()
    {
        return view('common.support.column');
    }

    /**
     * お問い合わせ窓口（デモ用ダミーページ）
     */
    public function supportForm()
    {
        return view('common.support.form');
    }
}

