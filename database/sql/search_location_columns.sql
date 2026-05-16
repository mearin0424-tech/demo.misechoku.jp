-- ==============================================================================
-- user_search_locations : キャスト／店舗の「検索拠点」設定を一元管理する専用テーブル
--
-- 旧設計（cast_profiles / shop_profiles に search_* カラムを足す方式）から
-- 別テーブル方式へ移行。owner_type / owner_id でポリモーフィックに紐づける
-- （user_talk_templates と同じ規約）。
--
-- カラム:
--   owner_type           : 'cast' または 'shop'
--   owner_id             : cast_profiles.cast_id / shop_profiles.shop_id
--   mode                 : 'profile' | 'passport' | 'current' | NULL（=未設定）
--   passport_address     : パスポートモードの住所文字列
--   passport_latitude    : パスポートモードの緯度
--   passport_longitude   : パスポートモードの経度
--   passport_label       : 表示用ラベル（駅名など）
--   max_distance_km      : 0=制限なし、>0 で半径 km、NULL=未設定
-- ==============================================================================

CREATE TABLE IF NOT EXISTS `user_search_locations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'cast または shop',
  `owner_id`   varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode`               varchar(16)   COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'profile / passport / current',
  `passport_address`   text          COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_latitude`  decimal(10,7) DEFAULT NULL,
  `passport_longitude` decimal(10,7) DEFAULT NULL,
  `passport_label`     varchar(80)   COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_distance_km`    smallint      DEFAULT NULL COMMENT '0=制限なし、>0 で半径 km',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_search_locations_owner` (`owner_type`, `owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 旧スキーマからのデータ移行（cast_profiles / shop_profiles に search_* カラムが
-- 残っている環境向け）。既に user_search_locations にデータが存在する
-- owner_type/owner_id の組は INSERT IGNORE でスキップされる。
-- ------------------------------------------------------------------------------
-- INSERT IGNORE INTO `user_search_locations`
--   (`owner_type`, `owner_id`, `mode`, `passport_address`, `passport_latitude`,
--    `passport_longitude`, `passport_label`, `max_distance_km`, `created_at`, `updated_at`)
-- SELECT 'cast', `cast_id`,
--        `search_location_mode`, `search_passport_address`, `search_passport_latitude`,
--        `search_passport_longitude`, `search_passport_label`, `search_max_distance_km`,
--        NOW(), NOW()
-- FROM `cast_profiles`
-- WHERE `search_location_mode` IS NOT NULL
--    OR `search_max_distance_km` IS NOT NULL
--    OR `search_passport_address` IS NOT NULL;
--
-- INSERT IGNORE INTO `user_search_locations`
--   (`owner_type`, `owner_id`, `mode`, `passport_address`, `passport_latitude`,
--    `passport_longitude`, `passport_label`, `max_distance_km`, `created_at`, `updated_at`)
-- SELECT 'shop', `shop_id`,
--        `search_location_mode`, `search_passport_address`, `search_passport_latitude`,
--        `search_passport_longitude`, `search_passport_label`, `search_max_distance_km`,
--        NOW(), NOW()
-- FROM `shop_profiles`
-- WHERE `search_location_mode` IS NOT NULL
--    OR `search_max_distance_km` IS NOT NULL
--    OR `search_passport_address` IS NOT NULL;
--
-- ALTER TABLE `cast_profiles`
--   DROP COLUMN IF EXISTS `search_location_mode`,
--   DROP COLUMN IF EXISTS `search_passport_address`,
--   DROP COLUMN IF EXISTS `search_passport_latitude`,
--   DROP COLUMN IF EXISTS `search_passport_longitude`,
--   DROP COLUMN IF EXISTS `search_passport_label`,
--   DROP COLUMN IF EXISTS `search_max_distance_km`;
--
-- ALTER TABLE `shop_profiles`
--   DROP COLUMN IF EXISTS `search_location_mode`,
--   DROP COLUMN IF EXISTS `search_passport_address`,
--   DROP COLUMN IF EXISTS `search_passport_latitude`,
--   DROP COLUMN IF EXISTS `search_passport_longitude`,
--   DROP COLUMN IF EXISTS `search_passport_label`,
--   DROP COLUMN IF EXISTS `search_max_distance_km`;
