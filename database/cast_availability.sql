-- =============================================================================
-- キャスト「今すぐ入れる」宣言 (available_until) の追加
-- 目的: 店舗ホームの「近い×今すぐ入れる」優先ソートで最上位ティアに使う
--
-- 実行方法:
--   mysql -u root -p misechoku < database/cast_availability.sql
-- =============================================================================

ALTER TABLE `cast_profiles`
    ADD COLUMN `available_until` timestamp NULL DEFAULT NULL
        COMMENT '「今すぐ入れる」宣言の有効期限。NULL または過去なら宣言なし'
        AFTER `longitude`,
    ADD COLUMN `available_declared_at` timestamp NULL DEFAULT NULL
        COMMENT '直近の available_until を宣言した時刻（同時刻タイブレーク用）'
        AFTER `available_until`,
    ADD KEY `idx_cast_profiles_available_until` (`available_until`);

-- 動作確認用の初期データ（任意）
-- UPDATE `cast_profiles` SET `available_until` = DATE_ADD(NOW(), INTERVAL 2 HOUR), `available_declared_at` = NOW() WHERE `cast_id` = 'c00000001';
-- UPDATE `cast_profiles` SET `available_until` = DATE_ADD(NOW(), INTERVAL 4 HOUR), `available_declared_at` = NOW() WHERE `cast_id` = 'c00000005';
