-- ==============================================================================
-- application_deposits に請求・入金・振込用カラムを追加（無いものだけ）
-- DB: MySQL 5.7+ / MariaDB 10.3+ 想定
-- 実行前にバックアップ推奨
--
-- 背景: schema.sql では ALTER で追加しているが、旧DBでは未適用のことがある
-- bonus_amount のみ追加済み（fix_application_deposits_bonus_amount.sql）の場合も、
-- invoice_number は is_read の直後に挿入される（AFTER is_read）
-- ==============================================================================

SET @db := DATABASE();

-- ヘルパ: カラムが無ければ ADD（動的SQL）
-- 引数: カラム名, ADD COLUMN 句（カラム名以降）
-- 直前カラム: 最初は is_read、以降はチェーン

-- invoice_number
SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'invoice_number'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `invoice_number` varchar(50) DEFAULT NULL AFTER `is_read`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- bonus_amount（invoice_number の後。invoice_number が無いと失敗するが、上で追加済み想定）
SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'bonus_amount'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `bonus_amount` int DEFAULT NULL AFTER `invoice_number`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'system_fee_amount'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `system_fee_amount` int DEFAULT NULL AFTER `bonus_amount`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'invoice_amount'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `invoice_amount` int DEFAULT NULL AFTER `system_fee_amount`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'cast_transfer_amount'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `cast_transfer_amount` int DEFAULT NULL AFTER `invoice_amount`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'invoice_issued_at'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `invoice_issued_at` timestamp NULL DEFAULT NULL AFTER `cast_transfer_amount`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'invoice_due_date'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `invoice_due_date` date DEFAULT NULL AFTER `invoice_issued_at`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'invoice_sent_at'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `invoice_sent_at` timestamp NULL DEFAULT NULL AFTER `invoice_due_date`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'shop_payment_reported_at'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `shop_payment_reported_at` timestamp NULL DEFAULT NULL AFTER `invoice_sent_at`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'shop_payment_reported_amount'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `shop_payment_reported_amount` int DEFAULT NULL AFTER `shop_payment_reported_at`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'shop_payment_reference'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `shop_payment_reference` varchar(255) DEFAULT NULL AFTER `shop_payment_reported_amount`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'shop_payment_confirmed_at'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `shop_payment_confirmed_at` timestamp NULL DEFAULT NULL AFTER `shop_payment_reference`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'cast_transferred_at'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `cast_transferred_at` timestamp NULL DEFAULT NULL AFTER `shop_payment_confirmed_at`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'cast_transfer_reference'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `cast_transfer_reference` varchar(255) DEFAULT NULL AFTER `cast_transferred_at`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'cast_transfer_note'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `cast_transfer_note` text AFTER `cast_transfer_reference`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'completed_at'),
    'SELECT 1',
    'ALTER TABLE `application_deposits` ADD COLUMN `completed_at` timestamp NULL DEFAULT NULL AFTER `cast_transfer_note`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
