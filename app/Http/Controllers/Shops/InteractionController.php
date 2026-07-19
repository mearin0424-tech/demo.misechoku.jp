<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;

/**
 * 旧 KEEPS（つながり）画面。
 * キープリストは SEARCH のキープタブへ移設したため、旧URLはリダイレクトのみ残す
 * （通知・ブックマーク等からの流入互換）。
 */
class InteractionController extends Controller
{
    public function index()
    {
        return $this->redirectToKeepTab();
    }

    public function keep()
    {
        return $this->redirectToKeepTab();
    }

    private function redirectToKeepTab()
    {
        return redirect()->route(
            request()->is('cast/*') ? 'cast.search.index' : 'shop.search.index',
            ['tab' => 'keep']
        );
    }
}
