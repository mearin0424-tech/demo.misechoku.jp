-- TALK/APPLYで選択した求人種別を保持するカラムを追加
-- 値: fulltime | trial | help
-- 実行前にバックアップ推奨

ALTER TABLE `shop_job_applications`
  ADD COLUMN `talk_job_kind` varchar(16) NULL COMMENT 'TALK/APPLYで選択した求人種別(fulltime|trial|help)' AFTER `shop_job_id`;

-- 既存データ初期化
-- 1) まず全件を fulltime で初期化
UPDATE `shop_job_applications`
SET `talk_job_kind` = 'fulltime'
WHERE `talk_job_kind` IS NULL OR `talk_job_kind` = '';

-- 2) ステータスが明確なものを上書き
-- 本採用(6) は fulltime、体験後不採用(7) は trial
UPDATE `shop_job_applications`
SET `talk_job_kind` = 'fulltime'
WHERE `status` = 6;

UPDATE `shop_job_applications`
SET `talk_job_kind` = 'trial'
WHERE `status` = 7;

-- 3) 求人票が「ヘルプ専用」に見えるものは help へ寄せる
-- job_type が無い環境でも使えるよう、時給列の有無で判定
UPDATE `shop_job_applications` sja
INNER JOIN `shop_jobs` sj ON sj.`id` = sja.`shop_job_id`
SET sja.`talk_job_kind` = 'help'
WHERE (sja.`talk_job_kind` IS NULL OR sja.`talk_job_kind` IN ('fulltime', 'trial'))
  AND sj.`help_hourly_wage` IS NOT NULL
  AND sj.`help_hourly_wage` <> ''
  AND (sj.`trial_hourly_wage` IS NULL OR sj.`trial_hourly_wage` = '');
