-- 以前の版で追加したテキスト系 applied_* を取り除く場合に実行（未作成なら不要）
-- カラムが存在しないとエラーになるので、その場合は当該行をコメントアウトしてください。

ALTER TABLE `shop_job_applications`
  DROP COLUMN `applied_pr`,
  DROP COLUMN `applied_catch_copy`,
  DROP COLUMN `applied_job_content`;
