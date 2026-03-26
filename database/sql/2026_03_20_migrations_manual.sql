-- ==============================================================================
-- 手動実行用: 2026_03_20 のマイグレーション相当（php artisan migrate が使えない場合）
-- DB: MySQL 5.7+ / MariaDB 10.3+ 想定
-- 実行前にバックアップ推奨
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- 2026_03_20_000000_create_push_subscriptions_table
-- （schema.sql に既にある場合はスキップされる）
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `endpoint` varchar(500) NOT NULL,
  `public_key` varchar(255) DEFAULT NULL,
  `auth_token` varchar(255) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2026_03_20_104202_add_actor_columns_to_push_subscriptions_table
-- カラムが無いときだけ追加（既存DB向け）
-- ------------------------------------------------------------------------------
SET @db := DATABASE();

-- user_type
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'push_subscriptions' AND COLUMN_NAME = 'user_type'
    ),
    'SELECT 1',
    'ALTER TABLE `push_subscriptions` ADD COLUMN `user_type` varchar(32) NULL AFTER `id`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- user_id
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'push_subscriptions' AND COLUMN_NAME = 'user_id'
    ),
    'SELECT 1',
    'ALTER TABLE `push_subscriptions` ADD COLUMN `user_id` varchar(32) NULL AFTER `user_type`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- インデックス（無いときだけ）
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'push_subscriptions' AND INDEX_NAME = 'push_subscriptions_user_idx'
    ),
    'SELECT 1',
    'ALTER TABLE `push_subscriptions` ADD INDEX `push_subscriptions_user_idx` (`user_type`, `user_id`)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------------------------
-- 2026_03_20_104205_create_notification_preferences_table
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notification_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_type` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `push_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `line_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `interview_reminder_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `deadline_reminder_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_preferences_user_unique` (`user_type`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- column_articles は database/sql/2026_03_22_000000_create_column_articles.sql
-- および 2026_03_22_120000_add_image_and_tags_to_column_articles.sql を参照
