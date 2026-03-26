-- 手動請求書発行時の宛先表示上書き（任意。未設定時は店舗プロフィール等を使用）
SET @db = DATABASE();

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'invoice_display_shop_name') = 0,
    'ALTER TABLE `application_deposits` ADD COLUMN `invoice_display_shop_name` varchar(255) DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'invoice_display_shop_address') = 0,
    'ALTER TABLE `application_deposits` ADD COLUMN `invoice_display_shop_address` varchar(500) DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'invoice_display_shop_email') = 0,
    'ALTER TABLE `application_deposits` ADD COLUMN `invoice_display_shop_email` varchar(255) DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'application_deposits' AND COLUMN_NAME = 'invoice_display_cast_name') = 0,
    'ALTER TABLE `application_deposits` ADD COLUMN `invoice_display_cast_name` varchar(255) DEFAULT NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
