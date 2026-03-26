-- ==============================================================================
-- review_details に review_content_id を追加（無い場合のみ）
-- DB: MySQL 5.7+ / MariaDB 10.3+ 想定
-- 実行前にバックアップ推奨
--
-- 背景:
-- - 新スキーマでは review_content_id を想定
-- - 旧データでは val のみの環境があり得る（アプリは val を優先参照する）
-- - INSERT で review_content_id を指定する場合は本カラムが必要
--
-- 処理: カラム追加 → 既存 val からコピー → 残NULLが無ければ FK 追加
-- ==============================================================================

SET @db := DATABASE();

-- ------------------------------------------------------------------------------
-- 1) カラム追加
-- ------------------------------------------------------------------------------
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'review_details' AND COLUMN_NAME = 'review_content_id'
    ),
    'SELECT 1 AS review_content_id_column_already_exists',
    'ALTER TABLE `review_details` ADD COLUMN `review_content_id` bigint unsigned NULL AFTER `review_id`'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------------------------
-- 2) 旧カラム val からコピー（val があるときだけ）
-- ------------------------------------------------------------------------------
SET @has_val := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'review_details' AND COLUMN_NAME = 'val'
);

SET @sql := IF(
  @has_val > 0,
  'UPDATE `review_details` SET `review_content_id` = `val` WHERE `review_content_id` IS NULL AND `val` IS NOT NULL',
  'SELECT 1 AS skip_backfill_no_val_column'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ------------------------------------------------------------------------------
-- 3) 外部キー（review_contents があり、NULL が残っていないときだけ）
--    NULL が残る場合はデータ修正後に手動で ALTER してください。
-- ------------------------------------------------------------------------------
SET @null_cnt := (SELECT COUNT(*) FROM `review_details` WHERE `review_content_id` IS NULL);
SET @has_rc := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'review_contents'
);
SET @has_fk := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'review_details'
    AND CONSTRAINT_NAME = 'review_details_review_content_id_foreign'
);

SET @sql := IF(
  @null_cnt = 0 AND @has_rc > 0 AND @has_fk = 0,
  'ALTER TABLE `review_details` ADD CONSTRAINT `review_details_review_content_id_foreign` FOREIGN KEY (`review_content_id`) REFERENCES `review_contents` (`id`) ON DELETE CASCADE',
  'SELECT 1 AS skip_fk_check_nulls_and_existing'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
