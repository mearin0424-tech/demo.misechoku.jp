-- ==============================================================================
-- 手動実行用: 2026_03_22_000000_create_column_articles_table.php 相当
-- DB: MySQL 5.7+ / MariaDB 10.3+ 想定
-- 実行前にバックアップ推奨
-- ==============================================================================

CREATE TABLE IF NOT EXISTS `column_articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `category` varchar(100) DEFAULT NULL COMMENT 'カテゴリ表示名',
  `summary` text COMMENT '一覧用抜粋',
  `body` longtext NOT NULL COMMENT '本文（プレーンテキスト想定）',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `visible_to_cast` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'キャスト向けに表示',
  `visible_to_shop` tinyint(1) NOT NULL DEFAULT '1' COMMENT '店舗向けに表示',
  `visible_to_guest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '未ログインの/support/columnに表示',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `column_articles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
