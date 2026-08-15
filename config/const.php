<?php
/**
 * config/const.php
 * サイト共通定数・設定ファイル
 *
 * Laravel's config loader re-includes every file under config/ on each
 * application boot. Under PHPUnit multiple boots happen in one process,
 * so bare define() would throw "Constant X already defined" on the
 * second and later boots. Guard each define() with defined() to make
 * the file idempotent. Behavior is unchanged in production (single boot).
 */

defined('MOCK_MODE') || define('MOCK_MODE', true);

defined('SITE_NAME') || define('SITE_NAME', 'Shop Portal Site');

defined('JOB_CATEGORIES') || define('JOB_CATEGORIES', [
    'waitress' => 'ウェイトレス',
    'kitchen'  => 'キッチン',
    'manager'  => 'マネージャー',
    'hall'     => 'ホールスタッフ'
]);

defined('SHOP_FEATURES') || define('SHOP_FEATURES', [
    'wifi'      => 'Wi-Fi完備',
    'smoking'   => '喫煙可',
    'card'      => 'カード決済OK',
    'parking'   => '駐車場あり'
]);

defined('CAST_TAGS') || define('CAST_TAGS', [
    'new'       => '新人',
    'experience'=> '経験者優遇',
    'weekend'   => '週末のみOK',
    'high_pay'  => '高収入'
]);
