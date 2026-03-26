-- ==============================================================================
-- 手動実行用: 2026_03_22_120000_add_image_and_tags_to_column_articles_table.php 相当
-- 前提: 2026_03_22_000000_create_column_articles.sql 実行済み（column_articles テーブルあり）
-- DB: MySQL 5.7+ / MariaDB 10.3+ 想定
-- カラムが既にある場合はスキップ（再実行安全）
-- 実行前にバックアップ推奨
-- ==============================================================================

SET @db := DATABASE();

-- image_path（category の直後）
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'column_articles' AND COLUMN_NAME = 'image_path'
    ),
    'SELECT 1',
    'ALTER TABLE `column_articles` ADD COLUMN `image_path` varchar(500) DEFAULT NULL COMMENT ''公開ディレクトリ相対パス'' AFTER `category`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- tags（image_path の直後）
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'column_articles' AND COLUMN_NAME = 'tags'
    ),
    'SELECT 1',
    'ALTER TABLE `column_articles` ADD COLUMN `tags` json DEFAULT NULL COMMENT ''タグ文字列のJSON配列'' AFTER `image_path`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- summary 削除（2つ目のマイグレーションで drop）
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
