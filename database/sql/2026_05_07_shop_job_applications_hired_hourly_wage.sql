-- 採用確定時に店舗が入力する本入時給（キャストの採用・入金画面などで表示）

ALTER TABLE `shop_job_applications`
  ADD COLUMN `hired_regular_hourly_wage` varchar(255) NULL COMMENT '採用確定時給（店舗入力）';
