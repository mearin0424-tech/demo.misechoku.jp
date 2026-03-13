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

    /**
     * ひらがな/カタカナ、全角/半角、英字大小の揺れを吸収する。
     */
    protected function normalizeSearchText(?string $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $value = mb_convert_kana($value, 'asKV', 'UTF-8');
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace_callback('/[\x{30A1}-\x{30F6}]/u', function (array $matches) {
            return mb_chr(mb_ord($matches[0], 'UTF-8') - 0x60, 'UTF-8');
        }, $value) ?? $value;

        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }
}