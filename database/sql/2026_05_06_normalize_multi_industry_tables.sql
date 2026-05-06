-- Normalize multi-industry tables and data
-- Target: MySQL 8+

START TRANSACTION;

-- 1) Ensure cast_industry exists
CREATE TABLE IF NOT EXISTS `cast_industry` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` VARCHAR(20) NOT NULL,
  `industry_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cast_industry` (`cast_id`, `industry_id`),
  KEY `idx_cast_industry_cast` (`cast_id`),
  KEY `idx_cast_industry_industry` (`industry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Rename industry_shop -> shop_industry (if needed)
SET @has_shop_industry := (
  SELECT COUNT(*)
  FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'shop_industry'
);
SET @has_industry_shop := (
  SELECT COUNT(*)
  FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'industry_shop'
);

SET @sql := IF(
  @has_shop_industry = 0 AND @has_industry_shop = 1,
  'RENAME TABLE `industry_shop` TO `shop_industry`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Ensure shop_industry exists (for fresh DBs)
CREATE TABLE IF NOT EXISTS `shop_industry` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` VARCHAR(20) NOT NULL,
  `industry_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shop_industry` (`shop_id`, `industry_id`),
  KEY `idx_shop_industry_shop` (`shop_id`),
  KEY `idx_shop_industry_industry` (`industry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Merge data from legacy table shop_industries (if exists)
SET @has_shop_industries := (
  SELECT COUNT(*)
  FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'shop_industries'
);
SET @sql := IF(
  @has_shop_industries = 1,
  'INSERT IGNORE INTO `shop_industry` (`shop_id`, `industry_id`, `created_at`, `updated_at`)
   SELECT `shop_id`, `industry_id`, NOW(), NOW()
   FROM `shop_industries`
   WHERE `shop_id` IS NOT NULL AND `industry_id` IS NOT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5) Backfill from single-select columns (compatibility source)
INSERT IGNORE INTO `shop_industry` (`shop_id`, `industry_id`, `created_at`, `updated_at`)
SELECT sp.`shop_id`, sp.`industry_id`, NOW(), NOW()
FROM `shop_profiles` sp
WHERE sp.`industry_id` IS NOT NULL;

INSERT IGNORE INTO `cast_industry` (`cast_id`, `industry_id`, `created_at`, `updated_at`)
SELECT cp.`cast_id`, cp.`industry_id`, NOW(), NOW()
FROM `cast_profiles` cp
WHERE cp.`industry_id` IS NOT NULL;

-- 6) Align single-select columns with multi-select canonical tables
UPDATE `shop_profiles` sp
JOIN (
  SELECT `shop_id`, MIN(`industry_id`) AS first_industry_id
  FROM `shop_industry`
  GROUP BY `shop_id`
) si ON si.`shop_id` = sp.`shop_id`
SET sp.`industry_id` = si.`first_industry_id`;

UPDATE `cast_profiles` cp
JOIN (
  SELECT `cast_id`, MIN(`industry_id`) AS first_industry_id
  FROM `cast_industry`
  GROUP BY `cast_id`
) ci ON ci.`cast_id` = cp.`cast_id`
SET cp.`industry_id` = ci.`first_industry_id`;

COMMIT;

-- Optional cleanup after verification:
-- DROP TABLE IF EXISTS `shop_industries`;
