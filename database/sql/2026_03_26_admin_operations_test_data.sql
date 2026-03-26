-- ==============================================================================
-- 管理画面「オペレーション」テスト用モックデータ（INSERT）
-- 前提: database/schema.sql 相当のシードが入っていること
--   - casts: c00000001, c00000002, c00000003
--   - shops: s00000001, s00000002
--   - shop_jobs: id 1 (s00000001), id 2 (s00000002)
--   - shop_job_applications: id 1〜3, application_deposits: id 1（完了済み）
--   - review_contents: id 1〜4
--   - bank_accounts: 運営・店舗・キャスト（c00000001, c00000002）など
-- 重複キーエラーになる場合は、既存データの id を確認して調整してください。
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 書類審査テーブル（未作成環境向け）
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_identity_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) NOT NULL,
  `type` varchar(32) NOT NULL,
  `image_path_front` varchar(255) NOT NULL,
  `image_path_back` varchar(255) DEFAULT NULL,
  `status` tinyint NOT NULL,
  `ng_reason` text,
  `expired_at` date DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cast_identity_documents_cast_id_index` (`cast_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_license_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(20) NOT NULL,
  `type` varchar(32) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `status` tinyint NOT NULL,
  `ng_reason` text,
  `expired_at` date DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_license_documents_shop_id_index` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------------------------
-- キャスト c00000003 の振込先（振込タスク確認用・未登録だと「口座未登録」表示になる）
-- ------------------------------------------------------------------------------
INSERT IGNORE INTO `bank_accounts` (
  `holder_type`, `holder_id`, `bank_code`, `bank_name`, `bank_name_kana`, `branch_code`, `branch_name`, `branch_name_kana`,
  `account_type`, `account_number`, `account_name`, `created_at`, `updated_at`
) VALUES (
  'casts', 'c00000003', '0006', 'テスト銀行', 'テストギンコウ', '001', '本店', 'ホンテン',
  'ordinary', '9988776', 'ヤマダ タロウ', NOW(), NOW()
);

-- ------------------------------------------------------------------------------
-- レビュー（請求書発行・入金フローに必要。cast_id + shop_id の組み合わせごとに1件）
-- id 30〜33 は未使用想定
-- ------------------------------------------------------------------------------
INSERT IGNORE INTO `reviews` (`id`, `cast_id`, `shop_id`, `contents`, `eva`, `created_at`) VALUES
(30, 'c00000001', 's00000002', '【テスト】採用レビュー（請求書発行待ち用）', 4.5, '2026-03-02 10:00:00'),
(31, 'c00000003', 's00000002', '【テスト】採用レビュー（入金照合用）', 4.0, '2026-03-19 10:00:00'),
(32, 'c00000002', 's00000001', '【テスト】採用レビュー（振込実行待ち用）', 4.5, '2026-03-08 10:00:00'),
(33, 'c00000003', 's00000001', '【テスト】採用レビュー（振込済・長期未確認用）', 4.0, '2026-03-12 10:00:00');

INSERT IGNORE INTO `review_details` (`review_id`, `review_content_id`, `score`) VALUES
(30, 1, 5.0), (30, 2, 5.0), (30, 3, 4.0), (30, 4, 5.0),
(31, 1, 4.0), (31, 2, 4.0), (31, 3, 4.0), (31, 4, 4.0),
(32, 1, 5.0), (32, 2, 5.0), (32, 3, 4.0), (32, 4, 5.0),
(33, 1, 4.0), (33, 2, 4.0), (33, 3, 4.0), (33, 4, 4.0);

-- ------------------------------------------------------------------------------
-- 採用済み応募（status=4）— 各 deposit と 1:1
-- id 10: c1 × Lounge Stella(job2) → 請求書発行待ち（deposit 2）
-- id 11: c3 × Stella(job2) → 店舗入金報告済（deposit 3）
-- id 12: c2 × Luminous(job1) → 店舗入金確認済・振込待ち（deposit 4）
-- id 13: c3 × Luminous(job1) → キャスト振込済・確認遅延（deposit 5）
-- ------------------------------------------------------------------------------
INSERT IGNORE INTO `shop_job_applications` (
  `id`, `cast_id`, `shop_job_id`, `status`, `result_date`, `hourly_wage_regular`,
  `hired_bonus_amount`, `hired_bonus_condition`, `created_at`, `updated_at`
) VALUES
(10, 'c00000001', 2, 4, '2026-03-01', '3500', 40000, 'テスト用ボーナス条件（請求書発行待ち）', '2026-03-01 12:00:00', NOW()),
(11, 'c00000003', 2, 4, '2026-03-18', '3500', 28000, 'テスト用（入金照合）', '2026-03-18 12:00:00', NOW()),
(12, 'c00000002', 1, 4, '2026-03-08', '5000', 50000, 'テスト用（振込実行待ち）', '2026-03-08 12:00:00', NOW()),
(13, 'c00000003', 1, 4, '2026-03-12', '5000', 22000, 'テスト用（振込済・長期未確認）', '2026-03-12 12:00:00', NOW());

-- ------------------------------------------------------------------------------
-- application_deposits
-- status: 2=店舗承認済(請求書発行) 4=入金報告済 5=店舗入金確認済 6=キャスト振込済
-- ------------------------------------------------------------------------------
INSERT IGNORE INTO `application_deposits` (
  `id`, `shop_job_application_id`, `status`, `is_read`,
  `invoice_number`, `bonus_amount`, `system_fee_amount`, `invoice_amount`, `cast_transfer_amount`,
  `invoice_issued_at`, `invoice_due_date`, `invoice_sent_at`,
  `shop_payment_reported_at`, `shop_payment_reported_amount`, `shop_payment_reference`,
  `shop_payment_confirmed_at`, `cast_transferred_at`, `cast_transfer_reference`, `completed_at`,
  `created_at`, `updated_at`
) VALUES
-- 2: 請求書発行バッジ・通知（invoice 未発行）
(
  2, 10, 2, 0,
  NULL, 40000, 4000, 44000, 40000,
  NULL, NULL, NULL,
  NULL, NULL, NULL,
  NULL, NULL, NULL, NULL,
  '2026-03-02 09:00:00', NOW()
),
-- 3: 入金照合（請求期限は発行日より後かつ「今日」より前にすると期限超過扱いになりやすい）
(
  3, 11, 4, 0,
  'INV-202603-TEST03', 28000, 2800, 30800, 28000,
  '2026-03-01 11:00:00', '2026-03-08', '2026-03-01 11:05:00',
  '2026-03-20 14:00:00', 30800, 'RCP-TEST-03',
  NULL, NULL, NULL, NULL,
  '2026-03-01 10:00:00', NOW()
),
-- 4: 振込実行待ち（店舗入金確認済）
(
  4, 12, 5, 0,
  'INV-202603-TEST04', 50000, 5000, 55000, 50000,
  '2026-03-09 10:00:00', '2026-03-16', '2026-03-09 10:05:00',
  '2026-03-10 12:00:00', 55000, 'RCP-TEST-04',
  '2026-03-11 09:00:00', NULL, NULL, NULL,
  '2026-03-09 08:00:00', NOW()
),
-- 5: キャスト振込済・振込日から7日超（ダッシュボード「7日以上未確認」系）
(
  5, 13, 6, 0,
  'INV-202603-TEST05', 22000, 2200, 24200, 22000,
  '2026-03-12 10:00:00', '2026-03-19', '2026-03-12 10:05:00',
  '2026-03-13 11:00:00', 24200, 'RCP-TEST-05',
  '2026-03-14 10:00:00', DATE_SUB(NOW(), INTERVAL 10 DAY), 'TRF-TEST-05', NULL,
  '2026-03-12 09:00:00', NOW()
);

-- ------------------------------------------------------------------------------
-- 振込タスク（deposit 4 は店舗入金確認済のため 1 件）
-- payout = invoice_amount - system_fee_amount - 銀行手数料(220円) に合わせる
-- ------------------------------------------------------------------------------
INSERT IGNORE INTO `payment_tasks` (
  `application_deposit_id`, `status`, `shop_received_amount`, `platform_fee_amount`, `bank_fee_amount`, `payout_amount`,
  `created_at`, `updated_at`
) VALUES (
  4, 1, 55000, 5000, 220, 49780,
  NOW(), NOW()
);

-- ------------------------------------------------------------------------------
-- 身分証・許可証（審査バッジ・通知）
-- status: 1=審査待ち 2=承認 3=差戻し
-- 再実行時に重複しないよう、同一キーは削除してから投入
-- ------------------------------------------------------------------------------
DELETE FROM `cast_identity_documents` WHERE `cast_id` = 'c00000002' AND `type` = 'id_card';
INSERT INTO `cast_identity_documents` (
  `cast_id`, `type`, `image_path_front`, `image_path_back`, `status`, `created_at`, `updated_at`
) VALUES (
  'c00000002', 'id_card', 'public/casts/identity/test-mock-front.txt', 'public/casts/identity/test-mock-back.txt', 1, NOW(), NOW()
);

DELETE FROM `shop_license_documents` WHERE `shop_id` = 's00000001' AND `type` = 'business';
INSERT INTO `shop_license_documents` (
  `shop_id`, `type`, `image_path`, `status`, `created_at`, `updated_at`
) VALUES (
  's00000001', 'business', 'public/shops/documents/test-mock-license.txt', 1, NOW(), NOW()
);

-- ==============================================================================
-- 期待される管理画面の動き（目安）
-- - サイドバー 請求書発行: +1（deposit id=2）
-- - 入金確認・振込: +2（id=3 照合, id=4 振込）※ id=5 はステージ6のため「振込待ち」集計には含まれない
-- - 身分証・書類審査: +2（キャスト1件 + 店舗1件・審査待ち）
-- - 通知ベル: 上記タスク・期限超過・問合せモック等の合計
-- 問い合わせ一覧は DB 未接続のモックのため、この SQL では変更しません。
-- ==============================================================================
