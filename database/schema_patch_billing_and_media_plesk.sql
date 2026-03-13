-- Plesk / phpMyAdmin でそのまま実行する想定の SQL
-- 前提:
-- - 既存の core schema (`casts`, `shops`, `cast_profiles`, `shop_profiles`,
--   `shop_job_applications`, `application_deposits`) は作成済み
-- - 今回不足している `bank_accounts` などを追加する

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `bank_accounts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` VARCHAR(20) NOT NULL,
  `bank_name` VARCHAR(100) NOT NULL,
  `branch_name` VARCHAR(100) DEFAULT NULL,
  `account_type` VARCHAR(20) NOT NULL,
  `account_number` VARCHAR(30) NOT NULL,
  `account_name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bank_accounts_member_id_unique` (`member_id`),
  CONSTRAINT `bank_accounts_member_id_foreign`
    FOREIGN KEY (`member_id`) REFERENCES `casts` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bank_account_shops` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` VARCHAR(20) NOT NULL,
  `bank_name` VARCHAR(100) NOT NULL,
  `branch_name` VARCHAR(100) DEFAULT NULL,
  `account_type` VARCHAR(20) NOT NULL,
  `account_number` VARCHAR(30) NOT NULL,
  `account_name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bank_account_shops_shop_id_unique` (`shop_id`),
  CONSTRAINT `bank_account_shops_shop_id_foreign`
    FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_bank_accounts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bank_name` VARCHAR(100) NOT NULL,
  `branch_name` VARCHAR(100) DEFAULT NULL,
  `account_type` VARCHAR(20) NOT NULL,
  `account_number` VARCHAR(30) NOT NULL,
  `account_name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cast_images` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` VARCHAR(20) NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `type` TINYINT DEFAULT NULL,
  `front_and_back` TINYINT(1) NOT NULL DEFAULT 0,
  `status` TINYINT NOT NULL DEFAULT 0,
  `is_main` TINYINT(1) NOT NULL DEFAULT 0,
  `main_order` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cast_images_cast_id_index` (`cast_id`),
  KEY `cast_images_type_index` (`type`),
  CONSTRAINT `cast_images_cast_id_foreign`
    FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_images` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` VARCHAR(20) NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `type` TINYINT DEFAULT NULL,
  `is_main` TINYINT(1) NOT NULL DEFAULT 0,
  `main_order` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_images_shop_id_index` (`shop_id`),
  CONSTRAINT `shop_images_shop_id_foreign`
    FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `cast_profiles`
  ADD COLUMN `main_image_path` VARCHAR(255) NULL AFTER `memo`;

ALTER TABLE `shop_profiles`
  ADD COLUMN `main_image_path` VARCHAR(255) NULL AFTER `message`;

ALTER TABLE `application_deposits`
  ADD COLUMN `invoice_number` VARCHAR(50) NULL AFTER `is_read`,
  ADD COLUMN `bonus_amount` INT NULL AFTER `invoice_number`,
  ADD COLUMN `system_fee_amount` INT NULL AFTER `bonus_amount`,
  ADD COLUMN `invoice_amount` INT NULL AFTER `system_fee_amount`,
  ADD COLUMN `cast_transfer_amount` INT NULL AFTER `invoice_amount`,
  ADD COLUMN `invoice_issued_at` TIMESTAMP NULL AFTER `cast_transfer_amount`,
  ADD COLUMN `invoice_due_date` DATE NULL AFTER `invoice_issued_at`,
  ADD COLUMN `invoice_sent_at` TIMESTAMP NULL AFTER `invoice_due_date`,
  ADD COLUMN `shop_payment_reported_at` TIMESTAMP NULL AFTER `invoice_sent_at`,
  ADD COLUMN `shop_payment_reported_amount` INT NULL AFTER `shop_payment_reported_at`,
  ADD COLUMN `shop_payment_reference` VARCHAR(255) NULL AFTER `shop_payment_reported_amount`,
  ADD COLUMN `shop_payment_confirmed_at` TIMESTAMP NULL AFTER `shop_payment_reference`,
  ADD COLUMN `cast_transferred_at` TIMESTAMP NULL AFTER `shop_payment_confirmed_at`,
  ADD COLUMN `cast_transfer_reference` VARCHAR(255) NULL AFTER `cast_transferred_at`,
  ADD COLUMN `cast_transfer_note` TEXT NULL AFTER `cast_transfer_reference`,
  ADD COLUMN `completed_at` TIMESTAMP NULL AFTER `cast_transfer_note`;

CREATE INDEX `application_deposits_invoice_number_index`
  ON `application_deposits` (`invoice_number`);

INSERT INTO `admin_bank_accounts` (
  `bank_name`,
  `branch_name`,
  `account_type`,
  `account_number`,
  `account_name`,
  `is_active`,
  `created_at`,
  `updated_at`
)
SELECT
  'みせちょく銀行',
  '本店営業部',
  'ordinary',
  '1234567',
  'ミセチョク ウンエイ',
  1,
  NOW(),
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM `admin_bank_accounts`
);

SET FOREIGN_KEY_CHECKS = 1;
