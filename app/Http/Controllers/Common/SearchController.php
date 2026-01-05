<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 検索機能の基底コントローラー
 */
abstract class SearchController extends Controller
{
    /**
     * 共通のインデックス表示ロジック
     */
    protected function renderIndex(array $data)
    {
        return view('common.search.index', array_merge([
            'pageId' => 'search',
        ], $data));
    }
}