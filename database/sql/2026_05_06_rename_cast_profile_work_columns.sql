-- cast_profiles: where_work / shift を work_where / work_time へ移行
-- 前提: MySQL 8.x

SET @db := DATABASE();

-- where_work -> work_where
SET @has_where_work := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'cast_profiles'
    AND COLUMN_NAME = 'where_work'
);

SET @has_work_where := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'cast_profiles'
    AND COLUMN_NAME = 'work_where'
);

SET @sql := IF(
  @has_where_work > 0 AND @has_work_where = 0,
  'ALTER TABLE cast_profiles CHANGE COLUMN where_work work_where VARCHAR(500) NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- shift -> work_time
SET @has_shift := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'cast_profiles'
    AND COLUMN_NAME = 'shift'
);

SET @has_work_time := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'cast_profiles'
    AND COLUMN_NAME = 'work_time'
);

SET @sql := IF(
  @has_shift > 0 AND @has_work_time = 0,
  'ALTER TABLE cast_profiles CHANGE COLUMN shift work_time INT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

