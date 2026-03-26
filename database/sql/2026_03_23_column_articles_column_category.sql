-- ==============================================================================
-- column_articles: カテゴリを column_categories.id に紐づけ、summary / category 文字列を廃止
-- DB: MySQL 5.7+ / MariaDB 10.3+ 想定
-- 前提: テーブル `column_categories` が存在すること
-- 実行前にバックアップ推奨
-- ==============================================================================

SET @db := DATABASE();

-- column_category_id 追加（slug の直後）
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'column_articles' AND COLUMN_NAME = 'column_category_id'
    ),
    'SELECT 1',
    'ALTER TABLE `column_articles` ADD COLUMN `column_category_id` bigint unsigned DEFAULT NULL AFTER `slug`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- インデックス（FK 用）
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'column_articles' AND INDEX_NAME = 'column_articles_column_category_id_index'
    ),
    'SELECT 1',
    'ALTER TABLE `column_articles` ADD INDEX `column_articles_column_category_id_index` (`column_category_id`)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 外部キー（未作成のときのみ）
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'column_articles'
        AND CONSTRAINT_NAME = 'column_articles_column_category_id_foreign'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ),
    'SELECT 1',
    'ALTER TABLE `column_articles` ADD CONSTRAINT `column_articles_column_category_id_foreign` FOREIGN KEY (`column_category_id`) REFERENCES `column_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 旧カラム category（文字列）削除
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'column_articles' AND COLUMN_NAME = 'category'
    ),
    'ALTER TABLE `column_articles` DROP COLUMN `category`',
    'SELECT 1'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 一覧用抜粋 summary 削除
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'column_articles' AND COLUMN_NAME = 'summary'
    ),
    'ALTER TABLE `column_articles` DROP COLUMN `summary`',
    'SELECT 1'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
