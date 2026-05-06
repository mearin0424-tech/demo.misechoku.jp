-- 申請時点の求人情報を shop_job_applications に焼き付ける（コードは Schema::hasColumn で吸収）
-- 実行前にバックアップ推奨。既に同名カラムがある場合はエラーになるので手動で調整してください。

ALTER TABLE `shop_job_applications`
  ADD COLUMN `applied_regular_status` tinyint NOT NULL DEFAULT '0' COMMENT '申請時点: 本入 0:非公開 1:公開' AFTER `shop_job_id`,
  ADD COLUMN `applied_regular_hourly_wage` varchar(255) NULL COMMENT '申請時点: 本入店時給' AFTER `applied_regular_status`,
  ADD COLUMN `applied_norma_day` int NULL COMMENT '申請時点: ボーナス達成に必要な在籍日数' AFTER `applied_regular_hourly_wage`,
  ADD COLUMN `applied_norma_hours` int NULL COMMENT '申請時点: 1日の勤務時間(ボーナス条件)' AFTER `applied_norma_day`,
  ADD COLUMN `applied_bonus_reward` int NULL COMMENT '申請時点: ボーナス金額' AFTER `applied_norma_hours`,
  ADD COLUMN `applied_bonus_remarks` varchar(255) NULL COMMENT '申請時点: ボーナス金額補足' AFTER `applied_bonus_reward`,
  ADD COLUMN `applied_bonus_condition` varchar(2000) NULL COMMENT '申請時点: ボーナス達成条件テキスト' AFTER `applied_bonus_remarks`,
  ADD COLUMN `applied_trial_hourly_wage` varchar(255) NULL COMMENT '申請時点: 体験入店時給' AFTER `applied_bonus_condition`,
  ADD COLUMN `applied_trial_status` tinyint NOT NULL DEFAULT '0' COMMENT '申請時点: 体験 0:非公開 1:公開' AFTER `applied_trial_hourly_wage`,
  ADD COLUMN `applied_has_help` tinyint(1) NOT NULL DEFAULT '0' COMMENT '申請時点: ヘルプあり' AFTER `applied_trial_status`,
  ADD COLUMN `applied_help_hourly_wage` varchar(255) NULL COMMENT '申請時点: ヘルプ時給' AFTER `applied_has_help`,
  ADD COLUMN `applied_help_status` tinyint NOT NULL DEFAULT '0' COMMENT '申請時点: ヘルプ 0:非公開 1:公開' AFTER `applied_help_hourly_wage`,
  ADD COLUMN `applied_working_day` varchar(255) NULL COMMENT '申請時点: 勤務日' AFTER `applied_help_status`,
  ADD COLUMN `applied_working_hours` varchar(255) NULL COMMENT '申請時点: 勤務時間' AFTER `applied_working_day`,
  ADD COLUMN `applied_regular_holiday` varchar(255) NULL COMMENT '申請時点: 定休日' AFTER `applied_working_hours`,
  ADD COLUMN `applied_qualification` varchar(255) NULL COMMENT '申請時点: 応募資格' AFTER `applied_regular_holiday`,
  ADD COLUMN `applied_shift_time_start` time NULL COMMENT '申請時点: 勤務開始時刻' AFTER `applied_qualification`,
  ADD COLUMN `applied_shift_time_end` time NULL COMMENT '申請時点: 勤務終了時刻' AFTER `applied_shift_time_start`,
  ADD COLUMN `applied_shift_end_is_last` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT '申請時点: 終了がLASTのとき1' AFTER `applied_shift_time_end`,
  ADD COLUMN `applied_regular_hourly_wage_max` int UNSIGNED NULL COMMENT '申請時点: 本入時給上限（円）' AFTER `applied_shift_end_is_last`,
  ADD COLUMN `applied_trial_hourly_wage_max` int UNSIGNED NULL COMMENT '申請時点: 体験入店時給上限（円）' AFTER `applied_regular_hourly_wage_max`,
  ADD COLUMN `applied_help_hourly_wage_max` int UNSIGNED NULL COMMENT '申請時点: ヘルプ時給上限（円）' AFTER `applied_trial_hourly_wage_max`;
