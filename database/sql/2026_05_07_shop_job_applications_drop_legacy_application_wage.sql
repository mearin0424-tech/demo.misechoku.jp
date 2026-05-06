-- コードを applied_* に寄せたあと、レガシー列を削除する場合に実行（バックアップ推奨）
-- hired_bonus_* の定義で AFTER `normal_time` となっている環境では、DROP 後にテーブル定義のみ変わります。

ALTER TABLE `shop_job_applications`
  DROP COLUMN `hourly_wage_regular`,
  DROP COLUMN `normal_time`;
