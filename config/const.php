<?php
/**
 * config/const.php
 * サイト共通定数・設定ファイル
 */

// 開発・営業用モックモード (true: モック画像を使用, false: 本番画像を使用)
define('MOCK_MODE', true);

// サイト基本情報
define('SITE_NAME', 'Shop Portal Site');

// 職種カテゴリ
define('JOB_CATEGORIES', [
    'waitress' => 'ウェイトレス',
    'kitchen'  => 'キッチン',
    'manager'  => 'マネージャー',
    'hall'     => 'ホールスタッフ'
]);

// こだわり条件
define('SHOP_FEATURES', [
    'wifi'      => 'Wi-Fi完備',
    'smoking'   => '喫煙可',
    'card'      => 'カード決済OK',
    'parking'   => '駐車場あり'
]);

// キャストタグ
define('CAST_TAGS', [
    'new'       => '新人',
    'experience'=> '経験者優遇',
    'weekend'   => '週末のみOK',
    'high_pay'  => '高収入'
]);