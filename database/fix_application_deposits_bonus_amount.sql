-- application_deposits に bonus_amount カラムを追加
-- エラー: Unknown column 'application_deposits.bonus_amount' の解消用

-- bonus_amount のみ追加する場合（invoice_number がまだ無い想定）
ALTER TABLE application_deposits
  ADD COLUMN bonus_amount INT NULL AFTER is_read;
