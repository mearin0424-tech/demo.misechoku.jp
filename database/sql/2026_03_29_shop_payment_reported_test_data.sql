-- ==============================================================================
-- 店舗入金済み（運営確認待ち）テストデータ
-- status=4（店舗入金報告済み）を1件追加
--
-- 前提（既存シード/テストSQLと同じ想定）:
-- - casts: c00000001
-- - shops: s00000001
-- - shop_jobs.id = 1（s00000001）
-- ------------------------------------------------------------------------------
-- 実行後に想定される状態:
-- - application_deposits.id=6 が status=4
-- - shop_payment_reported_at / shop_payment_reported_amount がセット済み
-- - shop_payment_confirmed_at は NULL（= 運営確認待ち）
-- ==============================================================================

SET NAMES utf8mb4;

-- 採用済み応募（status=4）を作成
INSERT IGNORE INTO `shop_job_applications` (
  `id`, `cast_id`, `shop_job_id`, `status`, `result_date`, `hourly_wage_regular`,
  `hired_bonus_amount`, `hired_bonus_condition`, `created_at`, `updated_at`
) VALUES (
  14, 'c00000001', 1, 4, '2026-03-20', '5000',
  36000, 'テスト用（店舗入金済み・運営確認待ち）', '2026-03-20 09:00:00', NOW()
);

-- 店舗入金済み（運営確認待ち）レコード
-- status: 4 = STATUS_SHOP_PAYMENT_REPORTED
INSERT IGNORE INTO `application_deposits` (
  `id`, `shop_job_application_id`, `status`, `is_read`,
  `invoice_number`, `bonus_amount`, `system_fee_amount`, `invoice_amount`, `cast_transfer_amount`,
  `invoice_issued_at`, `invoice_due_date`, `invoice_sent_at`,
  `shop_payment_reported_at`, `shop_payment_reported_amount`, `shop_payment_reference`,
  `shop_payment_confirmed_at`, `cast_transferred_at`, `cast_transfer_reference`, `completed_at`,
  `created_at`, `updated_at`
) VALUES (
  6, 14, 4, 0,
  'INV-202603-TEST06', 36000, 3600, 39600, 36000,
  '2026-03-21 10:00:00', '2026-03-28', '2026-03-21 10:05:00',
  '2026-03-24 15:30:00', 39600, 'RCP-TEST-06',
  NULL, NULL, NULL, NULL,
  '2026-03-21 09:30:00', NOW()
);

-- 履歴（再実行時の重複を避けるため先に削除）
DELETE FROM `application_deposit_histories` WHERE `application_deposit_id` = 6;

INSERT INTO `application_deposit_histories` (
  `application_deposit_id`, `status`, `status_date`, `created_at`
) VALUES
  (6, 1, '2026-03-21 09:30:00', NOW()),
  (6, 2, '2026-03-21 09:45:00', NOW()),
  (6, 3, '2026-03-21 10:00:00', NOW()),
  (6, 4, '2026-03-24 15:30:00', NOW());

-- 参考確認SQL
-- SELECT id, status, shop_payment_reported_at, shop_payment_confirmed_at
-- FROM application_deposits
-- WHERE id = 6;
