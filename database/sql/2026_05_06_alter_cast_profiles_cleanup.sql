-- cast_profiles schema cleanup and data migration
-- Target: MySQL 8+

START TRANSACTION;

-- 1) Ensure pr exists and can hold merged text
ALTER TABLE `cast_profiles`
  MODIFY COLUMN `pr` TEXT NULL;

-- 2) Merge charm_point / memo into pr (data preservation)
UPDATE `cast_profiles`
SET `pr` = TRIM(
  CONCAT_WS(
    "\n\n",
    NULLIF(TRIM(COALESCE(`pr`, '')), ''),
    CASE
      WHEN `charm_point` IS NULL OR TRIM(`charm_point`) = '' THEN NULL
      ELSE CONCAT('【チャームポイント】', "\n", TRIM(`charm_point`))
    END,
    CASE
      WHEN `memo` IS NULL OR TRIM(`memo`) = '' THEN NULL
      ELSE CONCAT('【メモ】', "\n", TRIM(`memo`))
    END
  )
);

-- 3) Add addr/building (shop_profiles align)
SET @has_addr := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'cast_profiles'
    AND column_name = 'addr'
);
SET @has_building := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'cast_profiles'
    AND column_name = 'building'
);

SET @sql := IF(
  @has_addr = 0,
  'ALTER TABLE `cast_profiles` ADD COLUMN `addr` VARCHAR(255) NULL AFTER `city`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @has_building = 0,
  'ALTER TABLE `cast_profiles` ADD COLUMN `building` VARCHAR(255) NULL AFTER `addr`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4) Migrate addr1/addr2/addr3 -> addr/building
UPDATE `cast_profiles`
SET
  `addr` = NULLIF(
    TRIM(
      CONCAT_WS(
        ' ',
        NULLIF(TRIM(COALESCE(`addr1`, '')), ''),
        NULLIF(TRIM(COALESCE(`addr2`, '')), '')
      )
    ),
    ''
  ),
  `building` = NULLIF(TRIM(COALESCE(`addr3`, '')), '')
WHERE
  (`addr` IS NULL OR TRIM(`addr`) = '')
  OR (`building` IS NULL OR TRIM(`building`) = '');

-- 5) Drop unused columns
ALTER TABLE `cast_profiles`
  DROP COLUMN `main_image_path`,
  DROP COLUMN `gender`,
  DROP COLUMN `addr1`,
  DROP COLUMN `addr2`,
  DROP COLUMN `addr3`,
  DROP COLUMN `memo`,
  DROP COLUMN `charm_point`,
  DROP COLUMN `ng_reason`;

COMMIT;
