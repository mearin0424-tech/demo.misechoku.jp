-- ==============================================================================
-- 位置情報フィルタ設定カラム追加マイグレーション
--
-- cast_profiles と shop_profiles に、MyPage の「位置情報で表示対象を絞る」設定を
-- 永続化するためのカラムを追加する。
--
-- 値:
--   search_location_mode      : 'profile' | 'passport' | 'current' | NULL（=未設定）
--   search_passport_address   : パスポートモードの住所文字列
--   search_passport_latitude  : パスポートモードの緯度
--   search_passport_longitude : パスポートモードの経度
--   search_passport_label     : 表示用ラベル（駅名など）
--   search_max_distance_km    : 0=制限なし、>0 なら半径 km で絞り込み、NULL=未設定
--
-- 既に同名カラムが存在しても安全に流せるよう IF NOT EXISTS を使用。
-- ==============================================================================

ALTER TABLE `cast_profiles`
  ADD COLUMN IF NOT EXISTS `search_location_mode`      varchar(16)   DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `search_passport_address`   text          DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `search_passport_latitude`  decimal(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `search_passport_longitude` decimal(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `search_passport_label`     varchar(80)   DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `search_max_distance_km`    smallint      DEFAULT NULL;

ALTER TABLE `shop_profiles`
  ADD COLUMN IF NOT EXISTS `search_location_mode`      varchar(16)   DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `search_passport_address`   text          DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `search_passport_latitude`  decimal(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `search_passport_longitude` decimal(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `search_passport_label`     varchar(80)   DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `search_max_distance_km`    smallint      DEFAULT NULL;
