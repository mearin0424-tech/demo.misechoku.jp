-- =============================================================================
-- ミセチョク：テスト用データセット（全機能検証向け）
--
-- 使い方:
--     mysql -u root -p misechoku < database/test_reset.sql
--
-- 前提:
--     - スキーマは database/schema.sql または database/mock_demo.sql で
--       既に作成済みであること（参照テーブル industries / cast_tags /
--       shop_tags / review_contents / character_guide_settings /
--       ng_words / column_categories / policy_* が投入済みであること）
--     - 初回セットアップ手順:
--         mysql -u root -p -e "CREATE DATABASE misechoku CHARACTER SET utf8mb4;"
--         mysql -u root -p misechoku < database/mock_demo.sql
--         mysql -u root -p misechoku < database/test_reset.sql
--
-- 画像ファイルについて:
--     - cast_images / shop_images は外部プレースホルダー画像 URL（i.pravatar.cc /
--       ui-avatars.com / picsum.photos）を直接 image_path に格納。
--       assetPathForStored() 側で http:// / https:// を素通しするよう拡張済み。
--       → 画像ファイルの準備は不要。ネット接続さえあれば全カードに画像が表示される。
--       → seed 固定 URL なので毎回同じ人物写真が返ってくる（キャッシュも効く）。
--       → オフライン検証したい場合は Phase 15b をコメントアウトすれば
--         全カードが no-image.png にフォールバックする。
--     - no-image.png は public/assets/images/common/ に配置済み（icon-192x192.png のコピー）。
--     - cast_identity_documents / shop_license_documents の image_path は private/dummy/... の
--       ダミーパス。管理画面で画像プレビューは表示されないが、
--       承認・差戻し等のワークフロー検証には影響しない。
--
-- 挙動:
--     1. ユーザースケールのテーブルを TRUNCATE（casts / shops / applications
--        / messages / deposits / documents / favorites / profile_views /
--        subscriptions / reviews / cast_tag_relations / shop_tag_relations
--        / cast_images / shop_images / cast_posts / shop_posts / bank_accounts /
--        cast_search_preferences / notifications / footprints / keeps）
--     2. 参照テーブル（industries / cast_tags / shop_tags / ng_words /
--        character_guide_settings / column_* / policy_* / invoice_template_settings /
--        admin_role_permissions / review_contents）は温存
--     3. テスト用のパーソナを一括投入
--
-- ログイン情報:
--     全アカウントのパスワード = 「password」
--     ハッシュ: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
--
--     ─── 管理者 ─────────────────────
--       admin@test.jp
--
--     ─── キャスト（10 人）─────────────
--       cast01@test.jp   c00000001 みさき     Tier A 宣言中(+2h) / 東京中央区 / 承認済み
--       cast02@test.jp   c00000002 ゆい       Tier A 宣言中(+4h) / 渋谷 / 採用済み 入金完了
--       cast03@test.jp   c00000003 マリ       Tier B オンライン中(20分前) / 面談待ち / DRAFT本人確認
--       cast04@test.jp   c00000004 政子       Tier B(直近 2h前) / PENDING 本人確認
--       cast05@test.jp   c00000005 のりりん   Tier B(直近 10h前) / REJECTED 本人確認(差戻し)
--       cast06@test.jp   c00000006 あや       Tier C 位置なし / 7日前
--       cast07@test.jp   c00000007 さくら     Tier C 大阪 / 3日前
--       cast08@test.jp   c00000008 みほ       Tier A 宣言中(+8h) 位置未設定(末尾テスト)
--       cast09@test.jp   c00000009 えりか     HIRED 状態 / 請求書発行済み
--       cast10@test.jp   c00000010 かな       HIRED_FULLTIME / 入金完了
--
--     ─── 店舗（5 店）───────────────
--       shop01@test.jp   s00000001 CLUB LUMINOUS     許可証 全承認 / Premium ACTIVE
--       shop02@test.jp   s00000002 CUTE club          許可証 DRAFT混在(新2段階フロー確認)
--       shop03@test.jp   s00000003 CAFE MOCHA         許可証 REJECTED(差戻し確認)
--       shop04@test.jp   s00000004 SNACK PEARL        許可証 未提出 / Premium PENDING_PAYMENT
--       shop05@test.jp   s00000005 LOUNGE STAR        Premium EXPIRED
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET time_zone = '+09:00';

-- =============================================================================
-- Phase 1: TRUNCATE 対象（ユーザースケール／セッション系）
-- =============================================================================

TRUNCATE TABLE `application_deposit_histories`;
TRUNCATE TABLE `application_deposits`;
TRUNCATE TABLE `shop_job_applications`;
TRUNCATE TABLE `shop_jobs`;
TRUNCATE TABLE `messages`;
TRUNCATE TABLE `talk_blocks`;
TRUNCATE TABLE `reviews`;
TRUNCATE TABLE `review_details`;
TRUNCATE TABLE `favorites`;
TRUNCATE TABLE `keeps`;
TRUNCATE TABLE `footprints`;
TRUNCATE TABLE `profile_views`;
TRUNCATE TABLE `cast_identity_documents`;
TRUNCATE TABLE `shop_license_documents`;
TRUNCATE TABLE `cast_images`;
TRUNCATE TABLE `shop_images`;
TRUNCATE TABLE `cast_posts`;
TRUNCATE TABLE `shop_posts`;
TRUNCATE TABLE `cast_profiles`;
TRUNCATE TABLE `shop_profiles`;
TRUNCATE TABLE `cast_tag_relations`;
TRUNCATE TABLE `shop_tag_relations`;
TRUNCATE TABLE `shop_job_tag_relations`;
TRUNCATE TABLE `bank_accounts`;
TRUNCATE TABLE `cast_search_preferences`;
TRUNCATE TABLE `shop_search_preferences`;
TRUNCATE TABLE `cast_providers`;
TRUNCATE TABLE `cast_shop_relation`;
TRUNCATE TABLE `notifications`;
TRUNCATE TABLE `notification_preferences`;
TRUNCATE TABLE `push_subscriptions`;
TRUNCATE TABLE `payment_tasks`;
TRUNCATE TABLE `shop_managers`;
TRUNCATE TABLE `casts`;
TRUNCATE TABLE `shops`;
TRUNCATE TABLE `shop_plan_subscriptions`;
TRUNCATE TABLE `user_talk_templates`;
TRUNCATE TABLE `support_inquiries`;
TRUNCATE TABLE `line_messages`;
TRUNCATE TABLE `admin_operation_logs`;
TRUNCATE TABLE `user_reports`;

-- =============================================================================
-- Phase 2: casts（10 人）
-- 全パスワード = password / hash は共通
-- =============================================================================

-- テスト用アカウントはメール認証済み扱い（NOW() を全員に設定）
INSERT INTO `casts` (`id`, `email`, `email_verified_at`, `password`, `status`, `identity_status`, `last_login_at`, `created_at`, `updated_at`) VALUES
-- c001: Tier A（宣言 +2h）承認済み本人確認、直近ログイン
('c00000001', 'cast01@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 3, DATE_SUB(NOW(), INTERVAL 5 MINUTE), '2026-01-15 10:00:00', NOW()),
-- c002: Tier A（宣言 +4h）承認済み本人確認、Premium 店舗と入金完了
('c00000002', 'cast02@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 3, DATE_SUB(NOW(), INTERVAL 15 MINUTE), '2026-02-01 10:00:00', NOW()),
-- c003: Tier B（オンライン中：20分前）DRAFT本人確認、面談待ち
('c00000003', 'cast03@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 2, DATE_SUB(NOW(), INTERVAL 20 MINUTE), '2026-03-01 10:00:00', NOW()),
-- c004: Tier B（直近ログイン2h前）PENDING本人確認
('c00000004', 'cast04@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 2, DATE_SUB(NOW(), INTERVAL 2 HOUR), '2026-03-10 10:00:00', NOW()),
-- c005: Tier B（10h前）REJECTED本人確認（差戻し）
('c00000005', 'cast05@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 2, DATE_SUB(NOW(), INTERVAL 10 HOUR), '2026-03-20 10:00:00', NOW()),
-- c006: Tier C 位置なし / 7日前ログイン
('c00000006', 'cast06@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, DATE_SUB(NOW(), INTERVAL 7 DAY), '2026-04-01 10:00:00', NOW()),
-- c007: Tier C 大阪 / 3日前
('c00000007', 'cast07@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), '2026-04-10 10:00:00', NOW()),
-- c008: Tier A（宣言 +8h）位置未設定（末尾扱いテスト）
('c00000008', 'cast08@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 3, DATE_SUB(NOW(), INTERVAL 30 MINUTE), '2026-05-01 10:00:00', NOW()),
-- c009: HIRED（採用済み）状態、請求書発行段階
('c00000009', 'cast09@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 3, DATE_SUB(NOW(), INTERVAL 2 DAY), '2026-05-15 10:00:00', NOW()),
-- c010: HIRED_FULLTIME、入金完了
('c00000010', 'cast10@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 3, DATE_SUB(NOW(), INTERVAL 5 DAY), '2026-06-01 10:00:00', NOW());

-- =============================================================================
-- Phase 3: cast_profiles（位置情報／available_until を各パーソナに割り当て）
-- =============================================================================

INSERT INTO `cast_profiles` (`id`, `industry_id`, `cast_id`, `nickname`, `name`, `birthday`, `pref`, `city`, `latitude`, `longitude`, `available_until`, `available_declared_at`, `pr`, `exp`, `profession`, `personality_type`, `created_at`, `updated_at`) VALUES
-- c001 Tier A 東京中央区
(1, 1, 'c00000001', 'みさき', '桜井美咲', '2001-05-15', '東京都', '中央区', 35.6826780, 139.7807160, DATE_ADD(NOW(), INTERVAL 2 HOUR), NOW(), '経験1年です！素敵なお店で頑張りたいです。', 1, '大学生', 'LCOH', NOW(), NOW()),
-- c002 Tier A 渋谷
(2, 1, 'c00000002', 'ゆい', '田中結衣', '1994-04-24', '東京都', '渋谷区', 35.6580000, 139.7016000, DATE_ADD(NOW(), INTERVAL 4 HOUR), NOW(), 'よろしくお願いします！', 1, 'モデル', 'LDMV', NOW(), NOW()),
-- c003 Tier B 江戸川区・オンライン中
(3, 1, 'c00000003', 'マリ', '田端麻里奈', '1999-10-15', '東京都', '江戸川区', 35.7062820, 139.8683050, NULL, NULL, '面談まちです', 0, '学生', 'HXOL', NOW(), NOW()),
-- c004 Tier B 品川区
(4, 1, 'c00000004', '政子', '田所政子', '2005-05-10', '東京都', '品川区', 35.6058540, 139.7325590, NULL, NULL, '初めてです、優しく教えてください。', 0, 'アルバイト', 'PXTQ', NOW(), NOW()),
-- c005 Tier B 港区
(5, 1, 'c00000005', 'のりりん', '間瀬紀子', '1998-05-10', '東京都', '港区', 35.6598140, 139.7290560, NULL, NULL, '5年経験あります。', 1, '会社員', 'LDOH', NOW(), NOW()),
-- c006 Tier C 位置なし
(6, NULL, 'c00000006', 'あや', '藤原彩', '2000-08-20', '東京都', '練馬区', NULL, NULL, NULL, NULL, 'よろしく', 0, NULL, NULL, NOW(), NOW()),
-- c007 Tier C 大阪
(7, 1, 'c00000007', 'さくら', '桜庭さくら', '1996-03-14', '大阪府', '大阪市中央区', 34.6873000, 135.5259000, NULL, NULL, '関西です！東京進出予定！', 1, 'キャバ嬢', 'PDOH', NOW(), NOW()),
-- c008 Tier A 位置未設定（末尾テスト用）
(8, 1, 'c00000008', 'みほ', '田村美帆', '2003-11-05', '東京都', '中野区', NULL, NULL, DATE_ADD(NOW(), INTERVAL 8 HOUR), NOW(), '長時間対応できます', 0, 'アルバイト', NULL, NOW(), NOW()),
-- c009 採用済み
(9, 1, 'c00000009', 'えりか', '安倍絵里加', '1997-01-25', '東京都', '中央区', 35.6812780, 139.7671250, NULL, NULL, 'よろしくお願いします', 1, 'OL', 'LDOV', NOW(), NOW()),
-- c010 入金完了
(10, 1, 'c00000010', 'かな', '木村奏', '1995-12-08', '東京都', '中央区', 35.6712780, 139.7501250, NULL, NULL, '毎日出勤できます', 1, 'フリーター', 'LXOH', NOW(), NOW());

-- =============================================================================
-- Phase 4: shops（5 店）+ shop_profiles + shop_managers
-- =============================================================================

INSERT INTO `shops` (`id`, `email`, `status`, `license_status`, `business_license_status`, `entertainment_license_status`, `created_at`, `updated_at`) VALUES
('s00000001', 'shop01@test.jp', 1, 3, 3, 3, '2026-01-10 10:00:00', NOW()), -- 全許可 承認済み
('s00000002', 'shop02@test.jp', 1, 2, 2, 1, '2026-02-01 10:00:00', NOW()), -- DRAFT 混在
('s00000003', 'shop03@test.jp', 1, 2, 3, 1, '2026-02-10 10:00:00', NOW()), -- REJECTED あり
('s00000004', 'shop04@test.jp', 1, 1, 1, 1, '2026-03-01 10:00:00', NOW()), -- 未提出
('s00000005', 'shop05@test.jp', 1, 3, 3, 3, '2026-01-05 10:00:00', NOW()); -- Premium 期限切れ

INSERT INTO `shop_managers` (`id`, `shop_id`, `name`, `email`, `email_verified_at`, `password`, `role`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES
('m00000001', 's00000001', '佐藤 店長', 'shop01@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, NOW(), '2026-01-10 10:00:00', NOW()),
('m00000002', 's00000002', '山田 マネージャー', 'shop02@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, NOW(), '2026-02-01 10:00:00', NOW()),
('m00000003', 's00000003', '鈴木 オーナー', 'shop03@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, NOW(), '2026-02-10 10:00:00', NOW()),
('m00000004', 's00000004', '中村 店長', 'shop04@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, NOW(), '2026-03-01 10:00:00', NOW()),
('m00000005', 's00000005', '高橋 店長', 'shop05@test.jp', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, NOW(), '2026-01-05 10:00:00', NOW());

INSERT INTO `shop_profiles` (`id`, `industry_id`, `shop_id`, `shop_name`, `zip`, `pref`, `city`, `addr`, `tel`, `open_time`, `close_is_last`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(1, 1, 's00000001', 'CLUB LUMINOUS (ルミナス)', '104-0061', '東京都', '中央区', '銀座 8-10-5', '03-1111-1111', '19:00:00', 1, 35.6712780, 139.7601250, '2026-01-10 10:00:00', NOW()),
(2, 1, 's00000002', 'CUTE club', '106-0032', '東京都', '港区', '六本木 5-1-2', '03-2222-2222', '19:00:00', 1, 35.6641820, 139.7301560, '2026-02-01 10:00:00', NOW()),
(3, 1, 's00000003', 'CAFE MOCHA', '150-0002', '東京都', '渋谷区', '渋谷 1-5-3', '03-3333-3333', '18:00:00', 0, 35.6595050, 139.7005460, '2026-02-10 10:00:00', NOW()),
(4, 1, 's00000004', 'SNACK PEARL', '160-0022', '東京都', '新宿区', '新宿 3-14-8', '03-4444-4444', '20:00:00', 1, 35.6919760, 139.7031560, '2026-03-01 10:00:00', NOW()),
(5, 1, 's00000005', 'LOUNGE STAR', '170-0013', '東京都', '豊島区', '東池袋 1-1-1', '03-5555-5555', '19:00:00', 1, 35.7295030, 139.7141470, '2026-01-05 10:00:00', NOW());

-- =============================================================================
-- Phase 5: shop_jobs（求人票）
-- =============================================================================

INSERT INTO `shop_jobs` (`id`, `shop_id`, `pr`, `catch_copy`, `job_content`, `regular_status`, `regular_hourly_wage`, `regular_hourly_wage_max`, `norma_day`, `bonus_reward`, `trial_hourly_wage`, `trial_hourly_wage_max`, `trial_status`, `has_help`, `help_hourly_wage`, `help_hourly_wage_max`, `help_status`, `working_day`, `working_hours`, `shift_time_start`, `shift_time_end`, `shift_end_is_last`, `qualification`, `created_at`, `updated_at`) VALUES
(1, 's00000001', '銀座エリアの高級クラブです。未経験も歓迎！', '入店祝い金 15万円！', '接客業務全般', 1, '6000', 12000, 5, 150000, '4000', 6000, 1, 1, '4500', 6000, 1, '週2〜', '19:00〜LAST', '19:00:00', NULL, 1, '18歳以上（高校生不可）', '2026-01-10 10:00:00', NOW()),
(2, 's00000002', '六本木の隠れ家的スナック', 'ボーナス 10万円！', '会話と接客', 1, '5000', 10000, 3, 100000, '3500', 5000, 1, 1, '4000', 5500, 1, '週1〜', '19:00〜3:00', '19:00:00', '03:00:00', 0, '20歳以上', '2026-02-01 10:00:00', NOW()),
(3, 's00000003', '渋谷のオシャレカフェ', NULL, 'カフェ業務', 0, '1500', 1800, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, '2026-02-10 10:00:00', NOW()),
(4, 's00000004', '新宿のアットホームスナック', 'ボーナス 5万円', '接客', 0, '4000', NULL, NULL, 50000, '3000', NULL, 0, 0, NULL, NULL, 0, '週3〜', '20:00〜2:00', '20:00:00', '02:00:00', 0, '20歳以上', '2026-03-01 10:00:00', NOW()),
(5, 's00000005', '池袋の落ち着いたラウンジ', 'ボーナス 8万円', 'ラウンジ接客', 1, '4500', 8000, 5, 80000, '3200', 4500, 1, 1, '4000', 5000, 1, '週2〜', '19:00〜LAST', '19:00:00', NULL, 1, '18歳以上（高校生不可）', '2026-01-05 10:00:00', NOW());

-- =============================================================================
-- Phase 6: cast_identity_documents（新2段階フロー確認用に全状態を用意）
--   0=DRAFT / 1=PENDING / 2=APPROVED / 3=REJECTED
-- =============================================================================

INSERT INTO `cast_identity_documents` (`id`, `cast_id`, `category`, `type`, `image_path_front`, `image_path_back`, `status`, `ng_reason`, `expired_at`, `approved_at`, `created_at`, `updated_at`) VALUES
-- c001 承認済み
(1, 'c00000001', 'photo_id', 'driver_license', 'private/dummy/c001_front.jpg', 'private/dummy/c001_back.jpg', 2, NULL, '2030-12-31', DATE_SUB(NOW(), INTERVAL 30 DAY), '2026-01-15 10:00:00', DATE_SUB(NOW(), INTERVAL 30 DAY)),
-- c002 承認済み
(2, 'c00000002', 'photo_id', 'mynumber_card', 'private/dummy/c002_front.jpg', NULL, 2, NULL, '2029-06-30', DATE_SUB(NOW(), INTERVAL 45 DAY), '2026-02-01 10:00:00', DATE_SUB(NOW(), INTERVAL 45 DAY)),
-- c003 DRAFT（新フロー：アップロード済み・未提出）
(3, 'c00000003', 'photo_id', 'passport', 'private/dummy/c003_front.jpg', 'private/dummy/c003_back.jpg', 0, NULL, '2028-03-15', NULL, DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR)),
-- c004 PENDING（審査中）
(4, 'c00000004', 'photo_id', 'driver_license', 'private/dummy/c004_front.jpg', 'private/dummy/c004_back.jpg', 1, NULL, '2031-05-10', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
-- c005 REJECTED（差戻し）
(5, 'c00000005', 'photo_id', 'driver_license', 'private/dummy/c005_front.jpg', NULL, 3, '書類が不鮮明です。もう一度撮影し直してご提出ください。', '2030-08-20', NULL, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
-- c008 承認済み
(6, 'c00000008', 'photo_id', 'mynumber_card', 'private/dummy/c008_front.jpg', NULL, 2, NULL, '2029-11-05', DATE_SUB(NOW(), INTERVAL 10 DAY), '2026-05-01 10:00:00', DATE_SUB(NOW(), INTERVAL 10 DAY)),
-- c009, c010 も承認済み
(7, 'c00000009', 'photo_id', 'driver_license', 'private/dummy/c009_front.jpg', 'private/dummy/c009_back.jpg', 2, NULL, '2030-01-25', DATE_SUB(NOW(), INTERVAL 60 DAY), '2026-05-15 10:00:00', DATE_SUB(NOW(), INTERVAL 60 DAY)),
(8, 'c00000010', 'photo_id', 'passport', 'private/dummy/c010_front.jpg', 'private/dummy/c010_back.jpg', 2, NULL, '2028-12-08', DATE_SUB(NOW(), INTERVAL 90 DAY), '2026-06-01 10:00:00', DATE_SUB(NOW(), INTERVAL 90 DAY));

-- =============================================================================
-- Phase 7: shop_license_documents
--   0=DRAFT / 1=PENDING / 2=APPROVED / 3=REJECTED
-- =============================================================================

INSERT INTO `shop_license_documents` (`id`, `shop_id`, `type`, `image_path`, `status`, `ng_reason`, `expired_at`, `approved_at`, `created_at`, `updated_at`) VALUES
-- s001 全承認
(1, 's00000001', 'business', 'private/dummy/s001_business.jpg', 2, NULL, '2030-06-30', DATE_SUB(NOW(), INTERVAL 60 DAY), '2026-01-10 10:00:00', DATE_SUB(NOW(), INTERVAL 60 DAY)),
(2, 's00000001', 'entertainment', 'private/dummy/s001_entertainment.jpg', 2, NULL, NULL, DATE_SUB(NOW(), INTERVAL 60 DAY), '2026-01-10 10:00:00', DATE_SUB(NOW(), INTERVAL 60 DAY)),
-- s002 混在（business=PENDING, entertainment=DRAFT）新フロー確認
(3, 's00000002', 'business', 'private/dummy/s002_business.jpg', 1, NULL, '2029-12-15', NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 's00000002', 'entertainment', 'private/dummy/s002_entertainment.jpg', 0, NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR)),
-- s003 差戻し（business=REJECTED）
(5, 's00000003', 'business', 'private/dummy/s003_business.jpg', 3, '許可書の日付が判読できません。全体が写るように再度アップロードしてください。', '2028-08-31', NULL, DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY));
-- s004 未提出（レコードなし）
-- s005 全承認（Premium 履歴用）
INSERT INTO `shop_license_documents` (`id`, `shop_id`, `type`, `image_path`, `status`, `expired_at`, `approved_at`, `created_at`, `updated_at`) VALUES
(6, 's00000005', 'business', 'private/dummy/s005_business.jpg', 2, '2029-05-01', DATE_SUB(NOW(), INTERVAL 200 DAY), '2026-01-05 10:00:00', DATE_SUB(NOW(), INTERVAL 200 DAY)),
(7, 's00000005', 'entertainment', 'private/dummy/s005_entertainment.jpg', 2, NULL, DATE_SUB(NOW(), INTERVAL 200 DAY), '2026-01-05 10:00:00', DATE_SUB(NOW(), INTERVAL 200 DAY));

-- =============================================================================
-- Phase 8: shop_plan_subscriptions（Premium）
--   status: 1=入金待ち 2=有効 3=期間満了 4=キャンセル
-- =============================================================================

INSERT INTO `shop_plan_subscriptions` (`shop_id`, `plan`, `billing_cycle`, `amount`, `status`, `invoice_number`, `invoice_issued_at`, `payment_due_date`, `paid_confirmed_at`, `confirmed_by`, `receipt_number`, `starts_at`, `ends_at`, `created_at`, `updated_at`) VALUES
-- s001 ACTIVE（有効中）
('s00000001', 'premium', 'monthly', 20000, 2, 'PLN-202608-0001', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 15 DAY), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), '1', 'RCT-202608-0001', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
-- s004 PENDING_PAYMENT
('s00000004', 'premium', 'monthly', 20000, 1, 'PLN-202608-0002', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 4 DAY), NULL, NULL, NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
-- s005 EXPIRED（期限切れ）
('s00000005', 'premium', 'monthly', 20000, 3, 'PLN-202605-0001', '2026-05-01 10:00:00', '2026-05-08', '2026-05-05 10:00:00', '1', 'RCT-202605-0001', '2026-05-05 10:00:00', '2026-06-05 10:00:00', '2026-05-01 10:00:00', '2026-06-06 00:00:00');

-- =============================================================================
-- Phase 9: shop_job_applications（各ステータス）
--   status: 1=やり取り中 2=面談日調整中 3=面談日決定 4=採用 5=不採用 6=採用(本入) 7=不採用(体験)
-- =============================================================================

INSERT INTO `shop_job_applications` (`id`, `cast_id`, `shop_job_id`, `status`, `hired_bonus_amount`, `talk_job_kind`, `result_date`, `real_start_date`, `hourly_wage_regular`, `created_at`, `updated_at`) VALUES
-- c003 → s002：面談待ち
(1, 'c00000003', 2, 2, NULL, 'trial', NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
-- c004 → s001：やり取り中
(2, 'c00000004', 1, 1, NULL, NULL, NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
-- c009 → s001：採用（HIRED - trial）請求書発行済み
(3, 'c00000009', 1, 4, 150000, 'trial', DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), '6000', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
-- c010 → s001：採用（HIRED_FULLTIME - fulltime）入金完了
(4, 'c00000010', 1, 6, 150000, 'fulltime', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 25 DAY), '6000', DATE_SUB(NOW(), INTERVAL 40 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
-- c002 → s001：面談日決定
(5, 'c00000002', 1, 3, NULL, 'trial', DATE_ADD(CURDATE(), INTERVAL 3 DAY), NULL, NULL, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
-- c001 → s002：やり取り中
(6, 'c00000001', 2, 1, NULL, NULL, NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),
-- c007 → s005：不採用
(7, 'c00000007', 5, 5, NULL, 'trial', DATE_SUB(CURDATE(), INTERVAL 10 DAY), NULL, NULL, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

-- =============================================================================
-- Phase 10: application_deposits（各ステータス）
--   status: 1=請求書発行 2=店舗入金報告 3=店舗入金確認 4=キャスト振込済 5=完了
-- =============================================================================

INSERT INTO `application_deposits` (`id`, `shop_job_application_id`, `status`, `is_read`, `invoice_number`, `bonus_amount`, `system_fee_amount`, `invoice_amount`, `cast_transfer_amount`, `invoice_issued_at`, `invoice_due_date`, `shop_payment_confirmed_at`, `cast_transferred_at`, `completed_at`, `created_at`, `updated_at`) VALUES
-- application_id=3 (c009×s001): 請求書発行段階
(1, 3, 1, 0, 'INV-202608-0001', 150000, 15000, 165000, 135000, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
-- application_id=4 (c010×s001): 完了状態
(2, 4, 5, 1, 'INV-202607-0001', 150000, 15000, 165000, 135000, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 13 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
-- application_id=7 (c007×s005): 不採用のためキャンセル
(3, 7, 1, 1, 'INV-202607-0002', NULL, NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 15 DAY), NULL, NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

INSERT INTO `application_deposit_histories` (`application_deposit_id`, `status`, `status_date`, `created_at`) VALUES
(1, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 1, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
(2, 2, DATE_SUB(NOW(), INTERVAL 16 DAY), DATE_SUB(NOW(), INTERVAL 16 DAY)),
(2, 3, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(2, 4, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, 5, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

-- =============================================================================
-- Phase 11: bank_accounts（キャスト振込先／システム口座）
-- =============================================================================

INSERT INTO `bank_accounts` (`holder_type`, `holder_id`, `bank_code`, `bank_name`, `bank_name_kana`, `branch_code`, `branch_name`, `branch_name_kana`, `account_type`, `account_number`, `account_name`, `created_at`, `updated_at`) VALUES
('casts', 'c00000009', '0001', 'みずほ銀行', 'ミズホ', '001', '東京営業部', 'トウキヨウ', 'ordinary', '1234567', 'アベ エリカ', NOW(), NOW()),
('casts', 'c00000010', '0005', '三菱UFJ銀行', 'ミツビシユーエフジェイ', '051', '新宿支店', 'シンジユク', 'ordinary', '7654321', 'キムラ カナ', NOW(), NOW()),
('system_accounts', '1', '0033', 'PayPay銀行', 'ペイペイ', '001', '本店営業部', 'ホンテン', 'ordinary', '99999999', 'ミセチヨク', NOW(), NOW());

-- =============================================================================
-- Phase 12: messages（各シナリオのトーク）
-- =============================================================================

INSERT INTO `messages` (`cast_id`, `shop_id`, `sender_type`, `type`, `content`, `is_read`, `created_at`, `updated_at`) VALUES
-- c003 × s002：面談日調整中
('c00000003', 's00000002', 1, 1, 'はじめまして！求人を拝見してご連絡しました。', 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
('c00000003', 's00000002', 2, 1, 'こちらこそご連絡ありがとうございます！ぜひ一度お話しできればと思います。', 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
('c00000003', 's00000002', 2, 1, '面談の候補日をお送りしました。ご都合はいかがでしょうか？', 0, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
-- c004 × s001：やり取り中
('c00000004', 's00000001', 1, 1, 'プロフィールを拝見して興味を持ちました！', 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
('c00000004', 's00000001', 2, 1, '未経験でも大歓迎です！詳しくお話しできればと思います。', 1, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
-- c001 × s002：緊急招集テンプレ（新機能テスト）
('c00000001', 's00000002', 2, 1, '今からヘルプで入れませんか？急遽ピンチヒッターを探しています。', 0, DATE_SUB(NOW(), INTERVAL 10 MINUTE), DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
-- c009 × s001：採用済み
('c00000009', 's00000001', 1, 1, 'はじめまして！応募させていただきました。', 1, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
('c00000009', 's00000001', 2, 4, '面談ありがとうございました。ぜひ採用で進めさせていただきたいと考えております。', 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
('c00000009', 's00000001', 1, 1, '採用ありがとうございます！精一杯頑張ります。', 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
-- c010 × s001：完了済み
('c00000010', 's00000001', 2, 1, 'この度は本入店ありがとうございます！', 1, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
('c00000010', 's00000001', 1, 1, 'よろしくお願いします！', 1, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY));

-- =============================================================================
-- Phase 13: favorites（KEEP）
-- =============================================================================

INSERT INTO `favorites` (`cast_id`, `shop_id`, `action_type`, `sender_type`, `created_at`) VALUES
-- 店舗→キャストのKEEP
('c00000001', 's00000001', 'KEEP', 'shop', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('c00000002', 's00000001', 'KEEP', 'shop', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('c00000003', 's00000002', 'KEEP', 'shop', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('c00000008', 's00000001', 'KEEP', 'shop', DATE_SUB(NOW(), INTERVAL 1 DAY)),
-- キャスト→店舗のKEEP
('c00000001', 's00000002', 'KEEP', 'cast', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('c00000002', 's00000005', 'KEEP', 'cast', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('c00000004', 's00000001', 'KEEP', 'cast', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('c00000007', 's00000001', 'KEEP', 'cast', DATE_SUB(NOW(), INTERVAL 8 DAY));

-- =============================================================================
-- Phase 14: profile_views（プロフィール閲覧ログ）Premium 閲覧者一覧テスト用
-- =============================================================================

INSERT INTO `profile_views` (`viewer_type`, `viewer_id`, `target_type`, `target_id`, `created_at`) VALUES
-- s001（Premium）のプロフィールを見に来たキャスト
('cast', 'c00000001', 'shop', 's00000001', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
('cast', 'c00000002', 'shop', 's00000001', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('cast', 'c00000004', 'shop', 's00000001', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
('cast', 'c00000007', 'shop', 's00000001', DATE_SUB(NOW(), INTERVAL 1 DAY)),
-- キャストのプロフィールを見に来た店舗
('shop', 's00000001', 'cast', 'c00000001', DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
('shop', 's00000002', 'cast', 'c00000002', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('shop', 's00000001', 'cast', 'c00000003', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
('shop', 's00000001', 'cast', 'c00000008', DATE_SUB(NOW(), INTERVAL 30 MINUTE));

-- =============================================================================
-- Phase 15: reviews（レビュー）採用済み案件のみ
--   review_details.val は review_contents マスタの val 値。1〜5 の代表値を投入。
-- =============================================================================

INSERT INTO `reviews` (`id`, `cast_id`, `shop_id`, `contents`, `eva`, `is_anonymous`, `created_at`, `updated_at`) VALUES
(1, 'c00000010', 's00000001', 'とても優しく丁寧な指導で、初心者でも安心して働けました。おすすめです！', 4.7, 1, DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY));

INSERT INTO `review_details` (`review_id`, `val`, `score`, `created_at`, `updated_at`) VALUES
(1, 1, 5.0, DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY)),
(1, 2, 5.0, DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY)),
(1, 3, 4.0, DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY));

-- =============================================================================
-- Phase 15b: cast_images / shop_images
--
--   ★ 画像ファイル 0 個で済む外部プレースホルダーサービスを利用 ★
--     - randomuser.me  : /portraits/women/{0-99}.jpg で女性写真だけを直接指定
--                        （番号固定＝毎回同じ女性）→ キャストのメイン写真に最適
--     - loremflickr.com: /<w>/<h>/<tag1,tag2,...>?lock=<n> でキーワード検索
--                        （nightclub/bar/lounge 等でナイトワーク系店内写真）
--                        → 店舗の店内・看板写真に最適
--     - picsum.photos  : ?seed=<seed> で seed 毎に異なる風景写真 → キャストのサブ写真
--   コントローラの assetPathForStored() が http:// / https:// を素通しするように
--   拡張済みなので、URL をそのまま image_path に格納すれば OK。
--
--   実写に差し替えたい場合はアプリの写真アップロード機能で差し替え可能。
--   オフライン検証したい場合はこの Phase を丸ごとコメントアウトして
--   assetPathForStored() の empty 分岐(no-image.png)に任せてください。
-- =============================================================================

INSERT INTO `cast_images` (`cast_id`, `image_path`, `type`, `status`, `is_main`, `main_order`, `created_at`, `updated_at`) VALUES
-- c001 みさき（メイン + サブ2枚：写真スライド動作確認用）
('c00000001', 'https://randomuser.me/api/portraits/women/25.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000001', 'https://picsum.photos/seed/misaki2/400/500', 1, 0, 0, 1, NOW(), NOW()),
('c00000001', 'https://picsum.photos/seed/misaki3/400/500', 1, 0, 0, 2, NOW(), NOW()),
-- c002 ゆい（メイン + サブ1枚）
('c00000002', 'https://randomuser.me/api/portraits/women/32.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000002', 'https://picsum.photos/seed/yui2/400/500', 1, 0, 0, 1, NOW(), NOW()),
-- c003〜c010（メインのみ）女性写真は index を全員別番号で指定
('c00000003', 'https://randomuser.me/api/portraits/women/47.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000004', 'https://randomuser.me/api/portraits/women/58.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000005', 'https://randomuser.me/api/portraits/women/12.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000006', 'https://randomuser.me/api/portraits/women/68.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000007', 'https://randomuser.me/api/portraits/women/71.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000008', 'https://randomuser.me/api/portraits/women/89.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000009', 'https://randomuser.me/api/portraits/women/44.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000010', 'https://randomuser.me/api/portraits/women/55.jpg', 1, 0, 1, 0, NOW(), NOW());

-- 店舗はナイトワーク系のキーワードで loremflickr から取得。1 店舗あたり 4〜5 枚を
-- 別 lock 番号 + キーワードのバリエーションで生成することで写真スライド動作もカバーする。
-- lock 値を全 URL でユニークにすることで、同じ店舗内でも別カットが返ってくる。
--
INSERT INTO `shop_images` (`shop_id`, `image_path`, `type`, `is_main`, `main_order`, `created_at`, `updated_at`) VALUES

-- ─── s001 CLUB LUMINOUS: 銀座の高級クラブ（5 枚） ───
('s00000001', 'https://loremflickr.com/400/500/nightclub,elegant,gold?lock=101', 1, 1, 0, NOW(), NOW()),
('s00000001', 'https://loremflickr.com/400/500/nightclub,champagne?lock=102',      1, 0, 1, NOW(), NOW()),
('s00000001', 'https://loremflickr.com/400/500/lounge,vip,luxury?lock=103',        1, 0, 2, NOW(), NOW()),
('s00000001', 'https://loremflickr.com/400/500/bar,counter,elegant?lock=104',      1, 0, 3, NOW(), NOW()),
('s00000001', 'https://loremflickr.com/400/500/nightclub,interior?lock=105',       1, 0, 4, NOW(), NOW()),

-- ─── s002 CUTE club: 六本木のポップな店（4 枚） ───
('s00000002', 'https://loremflickr.com/400/500/bar,pink,neon?lock=201',           1, 1, 0, NOW(), NOW()),
('s00000002', 'https://loremflickr.com/400/500/cocktail,pink?lock=202',           1, 0, 1, NOW(), NOW()),
('s00000002', 'https://loremflickr.com/400/500/nightclub,neon,pink?lock=203',     1, 0, 2, NOW(), NOW()),
('s00000002', 'https://loremflickr.com/400/500/bar,interior,pink?lock=204',       1, 0, 3, NOW(), NOW()),

-- ─── s003 CAFE MOCHA: 渋谷カフェ（3 枚） ───
('s00000003', 'https://loremflickr.com/400/500/cafe,cozy,drink?lock=301',         1, 1, 0, NOW(), NOW()),
('s00000003', 'https://loremflickr.com/400/500/cafe,latte,art?lock=302',          1, 0, 1, NOW(), NOW()),
('s00000003', 'https://loremflickr.com/400/500/cafe,interior?lock=303',           1, 0, 2, NOW(), NOW()),

-- ─── s004 SNACK PEARL: 新宿スナック（4 枚） ───
('s00000004', 'https://loremflickr.com/400/500/bar,neon,tokyo?lock=401',          1, 1, 0, NOW(), NOW()),
('s00000004', 'https://loremflickr.com/400/500/snack,bar,japan?lock=402',         1, 0, 1, NOW(), NOW()),
('s00000004', 'https://loremflickr.com/400/500/bar,karaoke?lock=403',             1, 0, 2, NOW(), NOW()),
('s00000004', 'https://loremflickr.com/400/500/bar,drinks,night?lock=404',        1, 0, 3, NOW(), NOW()),

-- ─── s005 LOUNGE STAR: 池袋ラウンジ（5 枚） ───
('s00000005', 'https://loremflickr.com/400/500/lounge,cocktail,gold?lock=501',    1, 1, 0, NOW(), NOW()),
('s00000005', 'https://loremflickr.com/400/500/lounge,sofa,vip?lock=502',         1, 0, 1, NOW(), NOW()),
('s00000005', 'https://loremflickr.com/400/500/nightclub,gold,luxury?lock=503',   1, 0, 2, NOW(), NOW()),
('s00000005', 'https://loremflickr.com/400/500/cocktail,bar,elegant?lock=504',    1, 0, 3, NOW(), NOW()),
('s00000005', 'https://loremflickr.com/400/500/lounge,neon,night?lock=505',       1, 0, 4, NOW(), NOW());

-- =============================================================================
-- Phase 16: cast_posts / shop_posts（ひとこと）
-- =============================================================================

INSERT INTO `cast_posts` (`cast_id`, `body`, `created_at`, `updated_at`) VALUES
('c00000001', '今日は元気です！', DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('c00000002', '今週末入れます', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('c00000003', 'よろしくお願いします', DATE_SUB(NOW(), INTERVAL 3 HOUR), DATE_SUB(NOW(), INTERVAL 3 HOUR));

INSERT INTO `shop_posts` (`shop_id`, `body`, `created_at`, `updated_at`) VALUES
('s00000001', '本日体験入店募集中！ボーナス最大 15 万円', NOW(), NOW()),
('s00000002', 'ヘルプ大歓迎です', DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- =============================================================================
-- Phase 17: cast_tag_relations（プロフィールタグ）
--   タグは既存の cast_tags を利用（looks / personality の 2 種類）
--   UNIQUE(cast_id, tag_id, tag_type)
-- =============================================================================

INSERT INTO `cast_tag_relations` (`cast_id`, `tag_id`, `tag_type`, `created_at`, `updated_at`) VALUES
('c00000001', 1, 'looks', NOW(), NOW()),
('c00000001', 3, 'looks', NOW(), NOW()),
('c00000001', 5, 'personality', NOW(), NOW()),
('c00000002', 2, 'looks', NOW(), NOW()),
('c00000002', 7, 'personality', NOW(), NOW()),
('c00000003', 1, 'looks', NOW(), NOW()),
('c00000005', 4, 'looks', NOW(), NOW()),
('c00000005', 6, 'personality', NOW(), NOW()),
('c00000007', 2, 'looks', NOW(), NOW()),
('c00000008', 8, 'personality', NOW(), NOW()),
('c00000009', 3, 'looks', NOW(), NOW()),
('c00000010', 5, 'personality', NOW(), NOW());

-- =============================================================================
-- Phase 18: notification_preferences（デフォルト設定を主要ロールに）
--   user_type / user_id / push_enabled / line_enabled / interview_reminder_enabled / deadline_reminder_enabled
-- =============================================================================

INSERT INTO `notification_preferences` (`user_type`, `user_id`, `push_enabled`, `line_enabled`, `interview_reminder_enabled`, `deadline_reminder_enabled`, `created_at`, `updated_at`) VALUES
('cast', 'c00000001', 1, 1, 1, 1, NOW(), NOW()),
('cast', 'c00000002', 1, 1, 1, 1, NOW(), NOW()),
('cast', 'c00000003', 1, 1, 1, 1, NOW(), NOW()),
('shop', 's00000001', 1, 1, 1, 1, NOW(), NOW()),
('shop', 's00000002', 1, 1, 1, 1, NOW(), NOW());

-- =============================================================================
-- Phase 19: system_accounts（管理者アカウントを再作成）
-- =============================================================================

TRUNCATE TABLE `system_accounts`;
INSERT INTO `system_accounts` (`id`, `name`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '管理者テスト', 'admin@test.jp', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW(), NOW());

-- =============================================================================
-- 完了
-- =============================================================================
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'テストデータの投入が完了しました' AS status,
       (SELECT COUNT(*) FROM casts) AS casts,
       (SELECT COUNT(*) FROM shops) AS shops,
       (SELECT COUNT(*) FROM shop_job_applications) AS applications,
       (SELECT COUNT(*) FROM application_deposits) AS deposits,
       (SELECT COUNT(*) FROM messages) AS messages;
