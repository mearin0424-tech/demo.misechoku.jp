-- 未使用テーブルの削除
-- 実行前に本番DBのバックアップを取ること
-- 2026-05-09 精査済み：コード上で一切参照なし

DROP TABLE IF EXISTS `news_reads`;
DROP TABLE IF EXISTS `news`;
DROP TABLE IF EXISTS `downloads`;
DROP TABLE IF EXISTS `mail_verifications`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `short_urls`;
