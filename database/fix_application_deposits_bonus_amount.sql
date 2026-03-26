-- application_deposits に bonus_amount カラムを追加
-- エラー: Unknown column 'application_deposits.bonus_amount' の解消用
--
-- 請求番号・手数料・入金日時など一式をまとめて足す場合は次を推奨:
-- database/sql/2026_03_28_application_deposits_add_billing_columns.sql

-- bonus_amount のみ追加する場合（invoice_number がまだ無い想定）
ALTER TABLE application_deposits
  ADD COLUMN bonus_amount INT NULL AFTER is_read;
