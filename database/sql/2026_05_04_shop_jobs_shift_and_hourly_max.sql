-- shop_jobs: 勤務時間（開始・終了・LAST）と時給上限（本入・体験・ヘルプ）
-- MySQL 想定。重複エラーになる場合は該当行をスキップしてください。

ALTER TABLE `shop_jobs` ADD COLUMN `shift_time_start` TIME NULL COMMENT '勤務開始時刻';
ALTER TABLE `shop_jobs` ADD COLUMN `shift_time_end` TIME NULL COMMENT '勤務終了時刻';
ALTER TABLE `shop_jobs` ADD COLUMN `shift_end_is_last` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '終了がLASTのとき1';

ALTER TABLE `shop_jobs` ADD COLUMN `regular_hourly_wage_max` INT UNSIGNED NULL COMMENT '本入時給上限（円）';
ALTER TABLE `shop_jobs` ADD COLUMN `trial_hourly_wage_max` INT UNSIGNED NULL COMMENT '体験入店時給上限（円）';
ALTER TABLE `shop_jobs` ADD COLUMN `help_hourly_wage_max` INT UNSIGNED NULL COMMENT 'ヘルプ時給上限（円）';
