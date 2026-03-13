-- ミセチョク demo 用
-- 請求・振込管理機能、およびプロフィール画像機能で不足している
-- MySQL テーブル / カラムを補うためのパッチ SQL
--
-- 想定DB: mock_demo (DB_CONNECTION=mysql)
-- 実行例:
--   mysql -h 127.0.0.1 -P 3306 -u mearin0424 -p mock_demo < database/schema_patch_billing_and_media.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. bank_accounts
-- -----------------------------------------------------------------------------
SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'bank_accounts'
        ),
        'SELECT ''bank_accounts already exists''',
        'CREATE TABLE bank_accounts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id VARCHAR(20) NOT NULL,
            bank_name VARCHAR(100) NOT NULL,
            branch_name VARCHAR(100) NULL,
            account_type VARCHAR(20) NOT NULL,
            account_number VARCHAR(30) NOT NULL,
            account_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY bank_accounts_member_id_unique (member_id),
            CONSTRAINT bank_accounts_member_id_foreign
                FOREIGN KEY (member_id) REFERENCES casts(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 2. bank_account_shops
-- -----------------------------------------------------------------------------
SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'bank_account_shops'
        ),
        'SELECT ''bank_account_shops already exists''',
        'CREATE TABLE bank_account_shops (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            shop_id VARCHAR(20) NOT NULL,
            bank_name VARCHAR(100) NOT NULL,
            branch_name VARCHAR(100) NULL,
            account_type VARCHAR(20) NOT NULL,
            account_number VARCHAR(30) NOT NULL,
            account_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY bank_account_shops_shop_id_unique (shop_id),
            CONSTRAINT bank_account_shops_shop_id_foreign
                FOREIGN KEY (shop_id) REFERENCES shops(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 3. admin_bank_accounts
-- -----------------------------------------------------------------------------
SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'admin_bank_accounts'
        ),
        'SELECT ''admin_bank_accounts already exists''',
        'CREATE TABLE admin_bank_accounts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            bank_name VARCHAR(100) NOT NULL,
            branch_name VARCHAR(100) NULL,
            account_type VARCHAR(20) NOT NULL,
            account_number VARCHAR(30) NOT NULL,
            account_name VARCHAR(100) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 4. cast_images
-- -----------------------------------------------------------------------------
SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'cast_images'
        ),
        'SELECT ''cast_images already exists''',
        'CREATE TABLE cast_images (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            cast_id VARCHAR(20) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            type TINYINT NULL,
            front_and_back TINYINT(1) NOT NULL DEFAULT 0,
            status TINYINT NOT NULL DEFAULT 0,
            is_main TINYINT(1) NOT NULL DEFAULT 0,
            main_order INT NULL,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY cast_images_cast_id_index (cast_id),
            KEY cast_images_type_index (type),
            CONSTRAINT cast_images_cast_id_foreign
                FOREIGN KEY (cast_id) REFERENCES casts(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 5. shop_images
-- -----------------------------------------------------------------------------
SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'shop_images'
        ),
        'SELECT ''shop_images already exists''',
        'CREATE TABLE shop_images (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            shop_id VARCHAR(20) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            type TINYINT NULL,
            is_main TINYINT(1) NOT NULL DEFAULT 0,
            main_order INT NULL,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY shop_images_shop_id_index (shop_id),
            CONSTRAINT shop_images_shop_id_foreign
                FOREIGN KEY (shop_id) REFERENCES shops(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 6. cast_profiles.main_image_path
-- -----------------------------------------------------------------------------
SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'cast_profiles'
              AND COLUMN_NAME = 'main_image_path'
        ),
        'SELECT ''cast_profiles.main_image_path already exists''',
        'ALTER TABLE cast_profiles ADD COLUMN main_image_path VARCHAR(255) NULL AFTER memo'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 7. shop_profiles.main_image_path
-- -----------------------------------------------------------------------------
SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'shop_profiles'
              AND COLUMN_NAME = 'main_image_path'
        ),
        'SELECT ''shop_profiles.main_image_path already exists''',
        'ALTER TABLE shop_profiles ADD COLUMN main_image_path VARCHAR(255) NULL AFTER message'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 8. application_deposits に請求・振込管理用カラム追加
-- -----------------------------------------------------------------------------
SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'invoice_number'
        ),
        'SELECT ''application_deposits.invoice_number already exists''',
        'ALTER TABLE application_deposits ADD COLUMN invoice_number VARCHAR(50) NULL AFTER is_read'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'bonus_amount'
        ),
        'SELECT ''application_deposits.bonus_amount already exists''',
        'ALTER TABLE application_deposits ADD COLUMN bonus_amount INT NULL AFTER invoice_number'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'system_fee_amount'
        ),
        'SELECT ''application_deposits.system_fee_amount already exists''',
        'ALTER TABLE application_deposits ADD COLUMN system_fee_amount INT NULL AFTER bonus_amount'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'invoice_amount'
        ),
        'SELECT ''application_deposits.invoice_amount already exists''',
        'ALTER TABLE application_deposits ADD COLUMN invoice_amount INT NULL AFTER system_fee_amount'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'cast_transfer_amount'
        ),
        'SELECT ''application_deposits.cast_transfer_amount already exists''',
        'ALTER TABLE application_deposits ADD COLUMN cast_transfer_amount INT NULL AFTER invoice_amount'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'invoice_issued_at'
        ),
        'SELECT ''application_deposits.invoice_issued_at already exists''',
        'ALTER TABLE application_deposits ADD COLUMN invoice_issued_at TIMESTAMP NULL AFTER cast_transfer_amount'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'invoice_due_date'
        ),
        'SELECT ''application_deposits.invoice_due_date already exists''',
        'ALTER TABLE application_deposits ADD COLUMN invoice_due_date DATE NULL AFTER invoice_issued_at'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'invoice_sent_at'
        ),
        'SELECT ''application_deposits.invoice_sent_at already exists''',
        'ALTER TABLE application_deposits ADD COLUMN invoice_sent_at TIMESTAMP NULL AFTER invoice_due_date'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'shop_payment_reported_at'
        ),
        'SELECT ''application_deposits.shop_payment_reported_at already exists''',
        'ALTER TABLE application_deposits ADD COLUMN shop_payment_reported_at TIMESTAMP NULL AFTER invoice_sent_at'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'shop_payment_reported_amount'
        ),
        'SELECT ''application_deposits.shop_payment_reported_amount already exists''',
        'ALTER TABLE application_deposits ADD COLUMN shop_payment_reported_amount INT NULL AFTER shop_payment_reported_at'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'shop_payment_reference'
        ),
        'SELECT ''application_deposits.shop_payment_reference already exists''',
        'ALTER TABLE application_deposits ADD COLUMN shop_payment_reference VARCHAR(255) NULL AFTER shop_payment_reported_amount'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'shop_payment_confirmed_at'
        ),
        'SELECT ''application_deposits.shop_payment_confirmed_at already exists''',
        'ALTER TABLE application_deposits ADD COLUMN shop_payment_confirmed_at TIMESTAMP NULL AFTER shop_payment_reference'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'cast_transferred_at'
        ),
        'SELECT ''application_deposits.cast_transferred_at already exists''',
        'ALTER TABLE application_deposits ADD COLUMN cast_transferred_at TIMESTAMP NULL AFTER shop_payment_confirmed_at'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'cast_transfer_reference'
        ),
        'SELECT ''application_deposits.cast_transfer_reference already exists''',
        'ALTER TABLE application_deposits ADD COLUMN cast_transfer_reference VARCHAR(255) NULL AFTER cast_transferred_at'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'cast_transfer_note'
        ),
        'SELECT ''application_deposits.cast_transfer_note already exists''',
        'ALTER TABLE application_deposits ADD COLUMN cast_transfer_note TEXT NULL AFTER cast_transfer_reference'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND COLUMN_NAME = 'completed_at'
        ),
        'SELECT ''application_deposits.completed_at already exists''',
        'ALTER TABLE application_deposits ADD COLUMN completed_at TIMESTAMP NULL AFTER cast_transfer_note'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- invoice_number 用 index
SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'application_deposits'
              AND INDEX_NAME = 'application_deposits_invoice_number_index'
        ),
        'SELECT ''application_deposits_invoice_number_index already exists''',
        'CREATE INDEX application_deposits_invoice_number_index ON application_deposits (invoice_number)'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- 9. 初期レコード（運営口座が空なら1件だけ追加）
-- -----------------------------------------------------------------------------
INSERT INTO admin_bank_accounts (
    bank_name,
    branch_name,
    account_type,
    account_number,
    account_name,
    is_active,
    created_at,
    updated_at
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
    SELECT 1 FROM admin_bank_accounts
);

SET FOREIGN_KEY_CHECKS = 1;
