-- =============================================================================
-- ミセチョク データベーススキーマ
-- Database: mock_demo
-- Generated: 2026-05-09
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- -----------------------------------------------------------------------------
-- push_subscriptions
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endpoint` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`),
  KEY `push_subscriptions_user_idx` (`user_type`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- casts
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `casts` (
  `id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主キー (c00000001~)',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `identity_status` tinyint NOT NULL DEFAULT '1' COMMENT '1:未提出, 2:未承認, 3:承認済',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `casts_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cast_profiles
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_profiles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `industry_id` bigint UNSIGNED DEFAULT NULL COMMENT '業種ID (industries)',
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_kana` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `zip` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pref` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `addr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `building` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `height` smallint DEFAULT NULL,
  `weight` smallint DEFAULT NULL,
  `bust` smallint DEFAULT NULL,
  `waist` smallint DEFAULT NULL,
  `hip` smallint DEFAULT NULL,
  `work_time` int DEFAULT NULL,
  `profession` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exp` tinyint DEFAULT NULL,
  `work_where` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pr` text COLLATE utf8mb4_unicode_ci,
  `personality_type` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cast_profiles_cast_id_foreign` (`cast_id`),
  KEY `fk_cast_profiles_industry` (`industry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cast_providers
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_providers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'line',
  `provider_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cast_providers_provider_id_unique` (`provider`, `provider_id`),
  KEY `cast_providers_cast_id_foreign` (`cast_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cast_images
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_images` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '1:アバター, 2:身分証等',
  `front_and_back` tinyint NOT NULL DEFAULT '0' COMMENT '1:表, 2:裏',
  `status` tinyint NOT NULL DEFAULT '0',
  `is_main` tinyint(1) NOT NULL DEFAULT '0',
  `main_order` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cast_images_cast_id` (`cast_id`),
  KEY `idx_cast_images_cast_id_type` (`cast_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cast_identity_documents
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_identity_documents` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'photo_id' COMMENT 'photo_id:顔写真付身分証 / non_photo_id:顔写真なし身分証 / address_proof:住所確認書類',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'photo_id: driver_license/passport/mynumber_card/residence_card | non_photo_id: health_insurance/pension_book | address_proof: residence_certificate/utility_bill',
  `image_path_front` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_path_back` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1:未承認, 2:承認済, 3:不備・却下',
  `ng_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `expired_at` date DEFAULT NULL COMMENT '有効期限',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT '承認日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cast_identity_documents_cast_category_unique` (`cast_id`, `category`),
  KEY `cast_id` (`cast_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cast_posts
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_posts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cast_posts_cast_id_foreign` (`cast_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cast_shop_relation
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_shop_relation` (
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `relation_type` tinyint NOT NULL COMMENT '1:ブロック, 2:追加等',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`cast_id`, `shop_id`),
  KEY `c_s_rel_shop_id_foreign` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cast_tags  (マスターデータ)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_tags` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'looks / personality',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `del_flg` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cast_tag` (`category`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cast_tag_relations
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_tag_relations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'キャストID',
  `tag_id` bigint UNSIGNED NOT NULL COMMENT '各種タグID',
  `tag_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'タグの種類 (例: looks, personalityなど)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cast_tag` (`cast_id`, `tag_id`, `tag_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- cast_industry
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_industry` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `industry_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cast_industry` (`cast_id`, `industry_id`),
  KEY `idx_cast_industry_cast` (`cast_id`),
  KEY `idx_cast_industry_industry` (`industry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shops
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shops` (
  `id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主キー (s00000001~)',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `license_status` tinyint NOT NULL DEFAULT '1',
  `business_license_status` tinyint NOT NULL DEFAULT '1' COMMENT '営業許可証 (1:未提出, 2:未承認, 3:承認済)',
  `entertainment_license_status` tinyint NOT NULL DEFAULT '1' COMMENT '風営法許可証 (1:未提出, 2:未承認, 3:承認済)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_profiles
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_profiles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `industry_id` bigint UNSIGNED DEFAULT NULL COMMENT '業種ID (industries)',
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pref` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `addr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '番地・丁目',
  `building` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '建物名・部屋番号',
  `tel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_time` time DEFAULT NULL COMMENT '開店時刻',
  `close_is_last` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=LAST',
  `close_time` time DEFAULT NULL COMMENT '閉店時刻',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shop_profiles_shop_id` (`shop_id`),
  KEY `fk_shop_profiles_industry` (`industry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_managers
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_managers` (
  `id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主キー (m00000001~)',
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` tinyint NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_managers_shop_id_foreign` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_images
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_images` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint DEFAULT NULL,
  `is_main` tinyint(1) NOT NULL DEFAULT '0',
  `main_order` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_shop_images_shop_id` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_posts
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_posts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci COMMENT '店舗のひとこと',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_posts_shop_id_unique` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_stations
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_stations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `station_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_shop_stations_shop_id` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_jobs
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pr` text COLLATE utf8mb4_unicode_ci COMMENT '店長からのメッセージ',
  `catch_copy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'キャッチコピー',
  `job_content` text COLLATE utf8mb4_unicode_ci COMMENT 'お仕事内容',
  `regular_status` tinyint NOT NULL DEFAULT '0' COMMENT '0:非公開, 1:公開',
  `regular_hourly_wage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '本入店時給',
  `norma_day` int DEFAULT NULL COMMENT 'ボーナス達成に必要な在籍日数',
  `norma_hours` int DEFAULT NULL COMMENT '1日の勤務時間(ボーナス条件)',
  `bonus_reward` int DEFAULT NULL COMMENT 'ボーナス金額',
  `bonus_remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ボーナス金額補足',
  `bonus_condition` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ボーナス達成条件テキスト',
  `trial_hourly_wage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trial_status` tinyint NOT NULL DEFAULT '0' COMMENT '0:非公開, 1:公開',
  `has_help` tinyint(1) NOT NULL DEFAULT '0',
  `help_hourly_wage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `help_status` tinyint NOT NULL DEFAULT '0' COMMENT '0:非公開, 1:公開',
  `working_day` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `working_hours` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regular_holiday` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qualification` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shift_time_start` time DEFAULT NULL COMMENT '勤務開始時刻',
  `shift_time_end` time DEFAULT NULL COMMENT '勤務終了時刻',
  `shift_end_is_last` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT '終了がLASTのとき1',
  `regular_hourly_wage_max` int UNSIGNED DEFAULT NULL COMMENT '本入時給上限（円）',
  `trial_hourly_wage_max` int UNSIGNED DEFAULT NULL COMMENT '体験入店時給上限（円）',
  `help_hourly_wage_max` int UNSIGNED DEFAULT NULL COMMENT 'ヘルプ時給上限（円）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shop_jobs_shop_id` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_job_applications
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_job_applications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_job_id` bigint UNSIGNED NOT NULL,
  `applied_regular_status` tinyint NOT NULL DEFAULT '0' COMMENT '申請時点: 本入 0:非公開 1:公開',
  `applied_regular_hourly_wage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '申請時点: 本入店時給',
  `applied_norma_day` int DEFAULT NULL COMMENT '申請時点: ボーナス達成に必要な在籍日数',
  `applied_norma_hours` int DEFAULT NULL COMMENT '申請時点: 1日の勤務時間(ボーナス条件)',
  `applied_bonus_reward` int DEFAULT NULL COMMENT '申請時点: ボーナス金額',
  `applied_bonus_remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '申請時点: ボーナス金額補足',
  `applied_bonus_condition` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '申請時点: ボーナス達成条件テキスト',
  `applied_trial_hourly_wage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '申請時点: 体験入店時給',
  `applied_trial_status` tinyint NOT NULL DEFAULT '0' COMMENT '申請時点: 体験 0:非公開 1:公開',
  `applied_has_help` tinyint(1) NOT NULL DEFAULT '0' COMMENT '申請時点: ヘルプあり',
  `applied_help_hourly_wage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '申請時点: ヘルプ時給',
  `applied_help_status` tinyint NOT NULL DEFAULT '0' COMMENT '申請時点: ヘルプ 0:非公開 1:公開',
  `applied_working_day` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '申請時点: 勤務日',
  `applied_working_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '申請時点: 勤務時間',
  `applied_regular_holiday` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '申請時点: 定休日',
  `applied_qualification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '申請時点: 応募資格',
  `applied_shift_time_start` time DEFAULT NULL COMMENT '申請時点: 勤務開始時刻',
  `applied_shift_time_end` time DEFAULT NULL COMMENT '申請時点: 勤務終了時刻',
  `applied_shift_end_is_last` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT '申請時点: 終了がLASTのとき1',
  `applied_regular_hourly_wage_max` int UNSIGNED DEFAULT NULL COMMENT '申請時点: 本入時給上限（円）',
  `applied_trial_hourly_wage_max` int UNSIGNED DEFAULT NULL COMMENT '申請時点: 体験入店時給上限（円）',
  `applied_help_hourly_wage_max` int UNSIGNED DEFAULT NULL COMMENT '申請時点: ヘルプ時給上限（円）',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1:やり取り中, 2:面談日調整中, 3:面談日決定, 4:採用, 5:不採用',
  `result_date` date DEFAULT NULL,
  `real_start_date` date DEFAULT NULL,
  `hourly_wage_regular` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `normal_time` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hired_bonus_amount` int DEFAULT NULL,
  `hired_bonus_condition` text COLLATE utf8mb4_unicode_ci,
  `reason_rejection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `talk_job_kind` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'fulltime|trial|help',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `s_j_app_cast_id_foreign` (`cast_id`),
  KEY `s_j_app_job_id_foreign` (`shop_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- application_deposits
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `application_deposits` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_job_application_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bonus_amount` int DEFAULT NULL,
  `system_fee_amount` int DEFAULT NULL,
  `invoice_amount` int DEFAULT NULL,
  `cast_transfer_amount` int DEFAULT NULL,
  `invoice_issued_at` timestamp NULL DEFAULT NULL,
  `invoice_due_date` date DEFAULT NULL,
  `invoice_sent_at` timestamp NULL DEFAULT NULL,
  `shop_payment_reported_at` timestamp NULL DEFAULT NULL,
  `shop_payment_reported_amount` int DEFAULT NULL,
  `shop_payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shop_payment_confirmed_at` timestamp NULL DEFAULT NULL,
  `cast_transferred_at` timestamp NULL DEFAULT NULL,
  `cast_transfer_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cast_transfer_note` text COLLATE utf8mb4_unicode_ci,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_dep_app_id_foreign` (`shop_job_application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- application_deposit_histories
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `application_deposit_histories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_deposit_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL,
  `status_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_dep_hist_dep_id_foreign` (`application_deposit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- payment_tasks
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payment_tasks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_deposit_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=待機 1=支払準備中 2=振込中 3=支払済 4=無効',
  `shop_received_amount` int UNSIGNED NOT NULL COMMENT '店舗入金額スナップショット',
  `platform_fee_amount` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'プラットフォーム手数料',
  `bank_fee_amount` int UNSIGNED NOT NULL DEFAULT '0' COMMENT '銀行振込手数料',
  `payout_amount` int UNSIGNED NOT NULL COMMENT 'キャスト振込額（自動計算）',
  `transferred_at` timestamp NULL DEFAULT NULL COMMENT '振込作業完了日時',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '支払済確定日時',
  `evidence_file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '振込完了証跡画像',
  `checklist_confirmed_account` tinyint(1) NOT NULL DEFAULT '0',
  `checklist_confirmed_amount` tinyint(1) NOT NULL DEFAULT '0',
  `operator_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '振込作業担当者ID',
  `refund_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '要返金フラグ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_tasks_application_deposit_id_unique` (`application_deposit_id`),
  KEY `payment_tasks_application_deposit_id_foreign` (`application_deposit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- favorites
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `favorites` (
<<<<<<< HEAD
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) DEFAULT NULL,
  `shop_id` varchar(20) DEFAULT NULL,
  `action_type` varchar(16) NOT NULL,
  `sender_type` varchar(8) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorites_unique_per_pair_action_sender` (`cast_id`, `shop_id`, `action_type`, `sender_type`),
  KEY `favorites_cast_id_foreign` (`cast_id`),
  KEY `favorites_shop_id_foreign` (`shop_id`),
  CONSTRAINT `favorites_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
=======
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_type` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorites_unique_per_pair_action_sender` (`cast_id`, `shop_id`, `action_type`, `sender_type`),
  KEY `favorites_shop_id_foreign` (`shop_id`)
>>>>>>> 3022dc1 (refactor: 未使用テーブル・デッドコードの削除とschema.sql再構築)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- keeps
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `keeps` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint NOT NULL DEFAULT '1',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keeps_cast_id_foreign` (`cast_id`),
  KEY `keeps_shop_id_foreign` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- footprints
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `footprints` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `footprints_cast_id_foreign` (`cast_id`),
  KEY `footprints_shop_id_foreign` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- messages
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `messages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_type` tinyint NOT NULL COMMENT '1:Cast, 2:Shop',
  `type` tinyint NOT NULL DEFAULT '1' COMMENT '1:Text, 2:Image',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_cast_id_foreign` (`cast_id`),
  KEY `messages_shop_id_foreign` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- talk_blocks
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `talk_blocks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `blocked_by` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'cast or shop',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `talk_blocks_cast_id_shop_id_unique` (`cast_id`, `shop_id`),
  KEY `talk_blocks_shop_id_index` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- reviews
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contents` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `eva` decimal(3,1) NOT NULL DEFAULT '0.0',
  `is_anonymous` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_cast_id_foreign` (`cast_id`),
  KEY `reviews_shop_id_foreign` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

<<<<<<< HEAD
CREATE TABLE IF NOT EXISTS `review_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `review_id` bigint unsigned NOT NULL,
  `review_content_id` bigint unsigned NOT NULL,
  `score` decimal(3,1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `review_details_review_id_foreign` (`review_id`),
  CONSTRAINT `review_details_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cast_tag` (
  `cast_id` varchar(20) NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`cast_id`, `tag_id`),
  KEY `cast_tag_tag_id_foreign` (`tag_id`),
  CONSTRAINT `cast_tag_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cast_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cast_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) NOT NULL,
  `body` text COMMENT '縺ｲ縺ｨ縺薙→',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cast_posts_cast_id_unique` (`cast_id`),
  CONSTRAINT `cast_posts_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_tag` (
  `shop_id` varchar(20) NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`shop_id`, `tag_id`),
  KEY `shop_tag_tag_id_foreign` (`tag_id`),
  CONSTRAINT `shop_tag_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shop_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `system_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COMMENT '邂｡逅・・錐',
  `email` varchar(255) NOT NULL COMMENT '繝ｭ繧ｰ繧､繝ｳ繝｡繝ｼ繝ｫ繧｢繝峨Ξ繧ｹ',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL COMMENT '繝上ャ繧ｷ繝･蛹悶ヱ繧ｹ繝ｯ繝ｼ繝・,
  `role` varchar(20) NOT NULL DEFAULT 'staff' COMMENT '讓ｩ髯・admin:蜈ｨ讖溯・, staff:荳驛ｨ讖溯・)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '譛牙柑繝輔Λ繧ｰ(false縺ｧ繝ｭ繧ｰ繧､繝ｳ荳榊庄)',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_accounts_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 繧ｳ繧｢蛻晄悄繝・・繧ｿ
INSERT IGNORE INTO `casts` (`id`, `email`, `password`, `status`, `identity_status`, `last_login_at`, `created_at`, `updated_at`) VALUES
('c00000001', 'cast01@example.com', '$2y$10$dummyhashedpassword01', 1, 3, '2026-03-01 10:00:00', '2026-01-10 12:00:00', '2026-03-01 10:00:00'),
('c00000002', 'cast02@example.com', '$2y$10$dummyhashedpassword02', 1, 3, '2026-03-05 15:30:00', '2026-02-15 09:00:00', '2026-03-05 15:30:00'),
('c00000003', 'cast03@example.com', '$2y$10$dummyhashedpassword03', 1, 1, '2026-03-10 20:15:00', '2026-03-01 18:45:00', '2026-03-10 20:15:00');

INSERT IGNORE INTO `cast_profiles` (`cast_id`, `nickname`, `name`, `birthday`, `gender`, `pref`, `city`, `height`, `weight`, `bust`, `waist`, `hip`, `profession`, `exp`, `pr`) VALUES
('c00000001', '縺ｿ縺輔″', '譯應ｺ慕ｾ主調', '2001-05-15', 1, '譚ｱ莠ｬ驛ｽ', '貂ｯ蛹ｺ', 160, 48, 85, 58, 86, '繧｢繝代Ξ繝ｫ蠎怜藤', 1, '讌ｽ縺励￥縺願ｩｱ縺励☆繧九・縺悟､ｧ螂ｽ縺阪〒縺呻ｼ√ｈ繧阪＠縺上♀鬘倥＞縺励∪縺吶・),
('c00000002', '縺ゅ＞', '螻ｱ逕ｰ諢・, '1999-10-22', 1, '逾槫･亥ｷ晉恁', '讓ｪ豬懷ｸ・, 155, 45, 82, 56, 84, '鄒主ｮｹ蟶ｫ', 1, '騾ｱ譛ｫ繝｡繧､繝ｳ縺ｧ蜒阪″縺溘＞縺ｧ縺呻ｼ・),
('c00000003', '繝ｦ繝・, '菴占陸邨占｡｣', '2003-02-14', 1, '蝓ｼ邇臥恁', '縺輔＞縺溘∪蟶・, 165, 50, 88, 60, 89, '螟ｧ蟄ｦ逕・, 0, '譛ｪ邨碁ｨ薙〒縺吶′荳逕滓・蜻ｽ鬆大ｼｵ繧翫∪縺呻ｼ・);

INSERT IGNORE INTO `cast_providers` (`cast_id`, `provider`, `provider_id`) VALUES
('c00000001', 'line', 'U11112222333344445555666677778888'),
('c00000002', 'line', 'Uaaaabbbbccccddddeeeeffffgggghhhh'),
('c00000003', 'line', 'Uzzzzxxxxccccvvvvbbbbnnnnmmmmkkkk');

INSERT IGNORE INTO `shops` (`id`, `email`, `status`, `license_status`, `created_at`, `updated_at`) VALUES
('s00000001', 'info@club-luminous.example.com', 1, 3, '2025-12-01 10:00:00', '2025-12-05 10:00:00'),
('s00000002', 'contact@lounge-stella.example.com', 1, 3, '2026-01-15 14:00:00', '2026-01-20 14:00:00');

INSERT IGNORE INTO `shop_profiles` (`shop_id`, `shop_name`, `opened_on`, `pref`, `city`, `addr2`, `addr3`, `station1`, `catch`, `overview`, `message`) VALUES
('s00000001', 'Club Luminous (繝ｫ繝溘リ繧ｹ)', '2015-04-01', '譚ｱ莠ｬ驛ｽ', '貂ｯ蛹ｺ', '蜈ｭ譛ｬ譛ｨ3-1-1', '繝ｫ繝溘リ繧ｹ繝薙Ν2F', '蜈ｭ譛ｬ譛ｨ鬧・蠕呈ｭｩ3蛻・, '關ｽ縺｡逹縺・◆髮ｰ蝗ｲ豌励・鬮倡ｴ壹け繝ｩ繝・, '譛ｪ邨碁ｨ薙°繧峨〒繧ゅ＠縺｣縺九ｊ繧ｵ繝昴・繝医☆繧句ｮ牙ｿ・・迺ｰ蠅・〒縺吶・, '荳邱偵↓讌ｽ縺励￥蜒阪￠繧区婿繧偵♀蠕・■縺励※縺翫ｊ縺ｾ縺呻ｼ・),
('s00000002', 'Lounge Stella (繧ｹ繝・Λ)', '2020-09-15', '譚ｱ莠ｬ驛ｽ', '譁ｰ螳ｿ蛹ｺ', '豁瑚・莨守伴1-2-3', '繧ｹ繝・Λ繧ｿ繝ｯ繝ｼ5F', '譁ｰ螳ｿ鬧・蠕呈ｭｩ5蛻・, '繧｢繝・ヨ繝帙・繝縺ｧ蜒阪″繧・☆縺・Λ繧ｦ繝ｳ繧ｸ', '繝弱Ν繝槭↑縺暦ｼ√≠縺ｪ縺溘・繝壹・繧ｹ縺ｧ蜒阪￠縺ｾ縺吶・, '蟄ｦ逕溘＆繧薙ｄW繝ｯ繝ｼ繧ｯ縺ｮ譁ｹ繧ょ､ｧ豁楢ｿ弱〒縺吶・);

INSERT IGNORE INTO `shop_managers` (`id`, `shop_id`, `name`, `email`, `password`, `role`, `status`, `last_login_at`) VALUES
('m00000001', 's00000001', '菴占陸 蠎鈴聞', 'sato.mgr@club-luminous.example.com', '$2y$10$dummyhashedpasswordM1', 1, 1, '2026-03-12 18:00:00'),
('m00000002', 's00000002', '驤ｴ譛ｨ 繧ｪ繝ｼ繝翫・', 'suzuki.owner@lounge-stella.example.com', '$2y$10$dummyhashedpasswordM2', 1, 1, '2026-03-11 22:30:00');

INSERT IGNORE INTO `shop_jobs` (`id`, `shop_id`, `hourly_wage_regular`, `normal_time`, `has_trial`, `trial_hourly_wage`, `has_help`, `help_hourly_wage`, `job_description`, `created_at`, `updated_at`) VALUES
(1, 's00000001', '5000', 5, 1, '4000', 1, '3500', '縺雁ｮ｢讒倥→讌ｽ縺励￥縺翫＠繧・∋繧翫＠縺ｦ縺企・繧剃ｽ懊ｋ縺贋ｻ穂ｺ九〒縺吶・, '2025-12-05 12:00:00', '2025-12-05 12:00:00'),
(2, 's00000002', '3500', 4, 1, '3000', 0, NULL, '邁｡蜊倥↑繝峨Μ繝ｳ繧ｯ菴懈・縺ｨ謗･螳｢繧偵♀莉ｻ縺帙＠縺ｾ縺吶ゅヮ繝ｫ繝槭↑縺暦ｼ・, '2026-01-20 15:00:00', '2026-01-20 15:00:00');

INSERT IGNORE INTO `shop_job_applications` (`id`, `cast_id`, `shop_job_id`, `status`, `result_date`, `hourly_wage_regular`, `created_at`, `updated_at`) VALUES
(1, 'c00000001', 1, 4, '2026-01-15', '5000', '2026-01-10 15:30:00', '2026-01-15 18:00:00'),
(2, 'c00000002', 2, 3, NULL, '3500', '2026-03-05 18:00:00', '2026-03-06 12:00:00'),
(3, 'c00000003', 1, 1, NULL, '5000', '2026-03-10 21:00:00', '2026-03-10 21:00:00');

INSERT IGNORE INTO `application_deposits` (`id`, `shop_job_application_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 6, '2026-02-15 10:00:00', '2026-02-20 15:00:00');

INSERT IGNORE INTO `application_deposit_histories` (`id`, `application_deposit_id`, `status`, `status_date`) VALUES
(1, 1, 1, '2026-02-15 10:00:00'),
(2, 1, 2, '2026-02-16 11:30:00'),
(3, 1, 6, '2026-02-20 15:00:00');

INSERT IGNORE INTO `favorites` (`cast_id`, `shop_id`, `action_type`, `sender_type`, `created_at`) VALUES
('c00000001', 's00000002', 'KEEP', 'cast', '2026-01-12 20:00:00'),
('c00000002', 's00000001', 'LIKE', 'shop', '2026-02-10 21:15:00');

INSERT IGNORE INTO `messages` (`cast_id`, `shop_id`, `sender_type`, `content`, `is_read`, `created_at`) VALUES
('c00000002', 's00000002', 1, '髱｢謗･繧偵♀鬘倥＞縺励◆縺・〒縺呻ｼ・, 1, '2026-03-05 18:05:00'),
('c00000002', 's00000002', 2, '縺泌ｿ懷供縺ゅｊ縺後→縺・＃縺悶＞縺ｾ縺吶ゆｻ企ｱ縺ｮ蝨滓屆譌･縺ｮ19譎ゅ・縺・°縺後〒縺励ｇ縺・°・・, 1, '2026-03-05 19:00:00'),
('c00000003', 's00000001', 1, '譛ｪ邨碁ｨ薙〒縺吶′蠢懷供蜿ｯ閭ｽ縺ｧ縺励ｇ縺・°・・, 0, '2026-03-10 21:05:00');

INSERT IGNORE INTO `reviews` (`id`, `cast_id`, `shop_id`, `contents`, `created_at`) VALUES
(1, 'c00000001', 's00000001', '繧ｹ繧ｿ繝・ヵ縺ｮ逧・＆繧薙′蜆ｪ縺励￥縺ｦ縲√→縺ｦ繧ょロ縺阪ｄ縺吶＞縺雁ｺ励〒縺励◆・・, '2026-02-28 10:00:00');

INSERT IGNORE INTO `tags` (`id`, `type`, `name`, `created_at`) VALUES
(1, 'salary', '1繝ｶ譛域鴛縺・, '2025-01-14 05:33:11'),
(8, 'salary', '莠､騾夊ｲｻ謾ｯ邨ｦ', '2025-01-14 05:33:12'),
(14, 'howto', '騾ｱ1縺九ｉOK', '2025-01-14 05:33:12'),
(82, 'casttag', '繧ｹ繝ｬ繝ｳ繝繝ｼ', '2025-01-14 05:33:13'),
(89, 'casttag', '繧ｭ繝ｬ繧､邉ｻ', '2025-01-14 05:33:13');

INSERT IGNORE INTO `cast_tag` (`cast_id`, `tag_id`) VALUES
('c00000001', 82), ('c00000001', 89), ('c00000002', 82);

INSERT IGNORE INTO `shop_tag` (`shop_id`, `tag_id`) VALUES
('s00000001', 8), ('s00000002', 14);

INSERT IGNORE INTO `system_accounts` (`name`, `email`, `password`, `role`, `is_active`, `email_verified_at`, `created_at`, `updated_at`) VALUES
('邂｡逅・・い繧ｫ繧ｦ繝ｳ繝茨ｼ・, 'admin@misechoku.jp', '$2y$10$dummyhashedpasswordAdmin01', 'admin', 1, '2025-01-01 00:00:00', '2025-01-01 00:00:00', '2025-01-01 00:00:00');

-- ------------------------------------------------------------------------------
-- 3. 邂｡逅・・繧ｹ繧ｿ繝ｻreview_contents (2026_03_13_000002_create_admin_master_tables)
-- ------------------------------------------------------------------------------

=======
-- -----------------------------------------------------------------------------
-- review_contents  (マスターデータ)
-- -----------------------------------------------------------------------------
>>>>>>> 3022dc1 (refactor: 未使用テーブル・デッドコードの削除とschema.sql再構築)
CREATE TABLE IF NOT EXISTS `review_contents` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '設問内容',
  `del_flg` tinyint(1) NOT NULL DEFAULT '0' COMMENT '削除フラグ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- review_details
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `review_details` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `review_id` bigint UNSIGNED NOT NULL,
  `review_content_id` bigint UNSIGNED DEFAULT NULL,
  `val` bigint UNSIGNED NOT NULL,
  `score` decimal(3,1) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_review_details_review_id_val` (`review_id`, `val`),
  KEY `idx_review_details_val` (`val`),
  KEY `review_details_review_content_id_foreign` (`review_content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- industries  (マスターデータ)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `industries` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '業種名',
  `del_flg` tinyint(1) NOT NULL DEFAULT '0' COMMENT '削除フラグ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_industry
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_industry` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `industry_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shop_industry` (`shop_id`, `industry_id`),
  KEY `idx_shop_industry_shop` (`shop_id`),
  KEY `idx_shop_industry_industry` (`industry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_tags  (マスターデータ)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_tags` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'work_style / atmosphere / facility / welcome / benefit',
  `target` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'shop' COMMENT 'shop: 店舗Profile用 / job: 求人票用',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `del_flg` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shop_tag` (`category`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_tag_relations
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_tag_relations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '店舗ID',
  `tag_id` bigint UNSIGNED NOT NULL COMMENT '各種タグID',
  `tag_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'タグの種類 (例: atmospheres, facilities, salaryなど)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_shop_tag` (`shop_id`, `tag_id`, `tag_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_job_tag_relations
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_job_tag_relations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_job_id` bigint UNSIGNED NOT NULL COMMENT '求人ID (shop_jobs.id)',
  `tag_id` bigint UNSIGNED NOT NULL COMMENT 'shop_tags.id',
  `tag_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'work_style / welcome / benefit',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_job_tag` (`shop_job_id`, `tag_id`, `tag_type`),
  KEY `fk_sj_tag_rel_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- shop_license_documents
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_license_documents` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '種類(business:営業許可証, entertainment:風営法許可等)',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0:アップロード済み, 1:審査中, 2:承認済み, 3:不備・却下',
  `ng_reason` text COLLATE utf8mb4_unicode_ci COMMENT '却下理由',
  `expired_at` date DEFAULT NULL COMMENT '有効期限',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT '承認日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  KEY `idx_shop_license_documents_expired_at` (`expired_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- bank_accounts
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bank_accounts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `holder_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '所有者モデル (casts, shops, users等)',
  `holder_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '所有者のID (c0001, s0001等)',
  `bank_code` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '銀行コード (4桁)',
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '銀行名',
  `bank_name_kana` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '銀行名（カナ）',
  `branch_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支店コード (3桁)',
  `branch_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支店名',
  `branch_name_kana` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支店名（カナ）',
  `account_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '預金種別 (ordinary:普通, current:当座)',
  `account_number` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '口座番号 (7〜8桁)',
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '口座名義 (カナ)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_holder` (`holder_type`, `holder_id`),
  KEY `idx_holder` (`holder_type`, `holder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- ng_words  (マスターデータ)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ng_words` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `word` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '有効フラグ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- system_accounts
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_accounts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '管理者名',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ログインメールアドレス',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ハッシュ化パスワード',
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff' COMMENT '権限(admin:全機能, staff:一部機能)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '有効フラグ(falseでログイン不可)',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

<<<<<<< HEAD
-- ------------------------------------------------------------------------------
-- 7. 繝ｬ繧ｬ繧ｷ繝ｼ繝薙Η繝ｼ (2026_03_14_000000_create_legacy_member_manager_views)
--    members.line_user_id 縺ｯ cast_providers・・rovider=line・峨°繧牙叙蠕暦ｼ・INE Notify 蛻励・蟒・ｭ｢・・
-- ------------------------------------------------------------------------------

DROP VIEW IF EXISTS `members`;
DROP VIEW IF EXISTS `managers`;

CREATE VIEW `members` AS
SELECT
  casts.id, casts.email, casts.password, casts.status, casts.status AS `approval`, casts.identity_status,
  casts.last_login_at, casts.remember_token, casts.created_at, casts.updated_at, casts.deleted_at,
  CASE WHEN casts.deleted_at IS NULL THEN 0 ELSE 1 END AS del_flg,
  cast_profiles.nickname, cast_profiles.name, cast_profiles.name_kana AS kana, cast_profiles.birthday,
  YEAR(cast_profiles.birthday) AS birthday_y, MONTH(cast_profiles.birthday) AS birthday_m, DAY(cast_profiles.birthday) AS birthday_d,
  cast_profiles.gender, cast_profiles.zip, cast_profiles.pref, cast_profiles.city, cast_profiles.addr1, cast_profiles.addr2, cast_profiles.addr3,
  cast_profiles.tel, cast_profiles.height, cast_profiles.weight, cast_profiles.bust AS b, cast_profiles.waist AS w, cast_profiles.hip AS h,
  cast_profiles.shift, cast_profiles.profession, cast_profiles.exp, cast_profiles.years_exp, cast_profiles.where_work,
  cast_profiles.pr, cast_profiles.charm_point, cast_profiles.memo, cast_profiles.ng_reason, cast_profiles.latitude, cast_profiles.longitude,
  (SELECT cp.provider_id FROM cast_providers cp WHERE cp.cast_id = casts.id AND cp.provider = 'line' LIMIT 1) AS line_user_id,
  0 AS `matching`, 0 AS `release`, NULL AS shop_name
FROM casts
LEFT JOIN cast_profiles ON casts.id = cast_profiles.cast_id;

CREATE VIEW `managers` AS
SELECT shop_managers.*, shop_profiles.shop_name
FROM shop_managers
LEFT JOIN shop_profiles ON shop_managers.shop_id = shop_profiles.shop_id;

-- ------------------------------------------------------------------------------
-- 8. 謗｡逕ｨ繝懊・繝翫せ繧ｹ繝翫ャ繝励す繝ｧ繝・ヨ (2026_03_15_000000_add_hired_bonus_snapshot)
-- ------------------------------------------------------------------------------

ALTER TABLE `shop_job_applications`
  ADD COLUMN `hired_bonus_amount` int DEFAULT NULL AFTER `normal_time`,
  ADD COLUMN `hired_bonus_condition` text DEFAULT NULL AFTER `hired_bonus_amount`;

-- 譌｢蟄倥・謗｡逕ｨ貂医∩(status=4)縺ｧ繧ｹ繝翫ャ繝励す繝ｧ繝・ヨ譛ｪ險ｭ螳壹・陦後ｒ豎ゆｺｺ縺九ｉ繝舌ャ繧ｯ繝輔ぅ繝ｫ・亥ｿ・ｦ√↓蠢懊§縺ｦ螳溯｡鯉ｼ・
-- UPDATE shop_job_applications sja
-- INNER JOIN shop_jobs sj ON sja.shop_job_id = sj.id
-- SET sja.hired_bonus_amount = COALESCE(sj.noruma_reward, sj.hourly_wage_regular, 0),
--     sja.hired_bonus_condition = ...
-- WHERE sja.status = 4 AND sja.hired_bonus_amount IS NULL;

-- ------------------------------------------------------------------------------
-- 9. 隲区ｱよ嶌繝・Φ繝励Ξ繝ｼ繝郁ｨｭ螳・(2026_03_16_000000_create_invoice_template_settings_table)
-- ------------------------------------------------------------------------------

=======
-- -----------------------------------------------------------------------------
-- invoice_template_settings
-- -----------------------------------------------------------------------------
>>>>>>> 3022dc1 (refactor: 未使用テーブル・デッドコードの削除とschema.sql再構築)
CREATE TABLE IF NOT EXISTS `invoice_template_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'issuer_name, issuer_email, logo_url, footer_text 等',
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_template_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- notices
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notices` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '本文（プレーンテキスト想定）',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `visible_to_cast` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'キャスト向けに表示',
  `visible_to_shop` tinyint(1) NOT NULL DEFAULT '1' COMMENT '店舗向けに表示',
  `visible_to_guest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '未ログインの/support/noticesに表示',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notices_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- column_categories  (マスターデータ)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `column_categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'カテゴリ名',
  `directory` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL用ディレクトリ',
  `del_flg` tinyint(1) NOT NULL DEFAULT '0' COMMENT '削除フラグ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- column_articles
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `column_articles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `column_category_id` bigint UNSIGNED DEFAULT NULL,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '公開ディレクトリ相対パス',
  `tags` json DEFAULT NULL COMMENT 'タグ文字列のJSON配列',
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '本文（プレーンテキスト想定）',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `visible_to_cast` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'キャスト向けに表示',
  `visible_to_shop` tinyint(1) NOT NULL DEFAULT '1' COMMENT '店舗向けに表示',
  `visible_to_guest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '未ログインの/support/columnに表示',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `column_articles_slug_unique` (`slug`),
  KEY `column_articles_column_category_id_index` (`column_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- notification_preferences
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notification_preferences` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `push_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `line_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `interview_reminder_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `deadline_reminder_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_preferences_user_unique` (`user_type`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- admin_operation_logs
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_operation_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` bigint UNSIGNED NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detail` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_operation_logs_admin_id_index` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- admin_notification_settings
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_notification_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` bigint UNSIGNED NOT NULL,
  `event_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_notification_settings_admin_event_unique` (`admin_id`, `event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- admin_role_permissions
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_role_permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_role_permissions_role_permission_unique` (`role`, `permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- line_messages
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `line_messages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `line_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `line_message_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- character_guide_settings
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `character_guide_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `route_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `screen_label` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `character_guide_settings_route_name_unique` (`route_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

<<<<<<< HEAD
-- ------------------------------------------------------------------------------
-- 既存 favorites の action_type を文字列化し、sender_type を追加するマイグレーション
-- 既存 DB に対して 1 回だけ実行する想定（冪等的に書く）。
--
--   旧スキーマ:
--     action_type tinyint  ... 1=KEEP, 2=FOOTPRINT(廃止), 3=LIKE
--   新スキーマ:
--     action_type varchar(16) ... 'KEEP' | 'LIKE'
--     sender_type varchar(8)  ... 'cast' | 'shop'
--
-- 注意:
-- - 旧 KEEP 行（1）は方向情報を保持していないため一意に sender_type を決められない。
--   ここでは安全策として sender_type を一旦 'shop' にしておく（運用上は店舗側のキープが
--   多い前提）。利用者には再キープを促してください。厳密に整理する場合は事前に
--   DELETE FROM favorites WHERE action_type = 1; を流してください。
-- - 旧 LIKE 行（3）はキャスト→店舗 LIKE が無効化されているため、すべて 'shop' 発信。
-- - 旧 FOOTPRINT 行（2）はすでに廃止のため削除する。
-- ------------------------------------------------------------------------------

-- 1) sender_type を追加（一旦 NULL 許容）
ALTER TABLE `favorites`
  ADD COLUMN IF NOT EXISTS `sender_type` varchar(8) NULL AFTER `action_type`;

-- 2) action_type を文字列カラム化（既存 tinyint からの ALTER は VARCHAR に直接変換可能）
ALTER TABLE `favorites`
  MODIFY COLUMN `action_type` varchar(16) NOT NULL;

-- 3) 値の置換: 1->'KEEP', 3->'LIKE'、2 は削除
DELETE FROM `favorites` WHERE `action_type` = '2';
UPDATE `favorites` SET `action_type` = 'KEEP' WHERE `action_type` = '1';
UPDATE `favorites` SET `action_type` = 'LIKE' WHERE `action_type` = '3';

-- 4) sender_type バックフィル
--    LIKE は店舗発信のみで運用していたので 'shop' を入れる。
--    KEEP は方向不明だが、過渡期は 'shop' で埋める。重複が出る場合は手動で精査。
UPDATE `favorites` SET `sender_type` = 'shop' WHERE `sender_type` IS NULL AND `action_type` = 'LIKE';
UPDATE `favorites` SET `sender_type` = 'shop' WHERE `sender_type` IS NULL AND `action_type` = 'KEEP';

-- 5) sender_type を NOT NULL に締める
ALTER TABLE `favorites`
  MODIFY COLUMN `sender_type` varchar(8) NOT NULL;

-- 6) 重複防止のユニークキー（既に存在するなら無視される）
ALTER TABLE `favorites`
  ADD UNIQUE KEY IF NOT EXISTS `favorites_unique_per_pair_action_sender`
  (`cast_id`, `shop_id`, `action_type`, `sender_type`);
=======
-- -----------------------------------------------------------------------------
-- policy_documents  (コンテンツデータ)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `policy_documents` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'about / terms / privacy',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ページタイトル',
  `lead_title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'リード見出し',
  `lead_body` text COLLATE utf8mb4_unicode_ci COMMENT 'リード本文',
  `meta` json DEFAULT NULL COMMENT '協会概要などの構造化データ',
  `is_locked` tinyint(1) NOT NULL DEFAULT '1' COMMENT '既定はロック状態（編集不可）',
  `updated_by_id` bigint UNSIGNED DEFAULT NULL COMMENT '最終更新者の system_account.id',
  `updated_by_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '最終更新者の表示名',
  `content_updated_at` timestamp NULL DEFAULT NULL COMMENT '最終更新日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `policy_documents_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- policy_chapters  (コンテンツデータ)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `policy_chapters` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `policy_document_id` bigint UNSIGNED NOT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `policy_chapters_doc_sort_index` (`policy_document_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- policy_revisions
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `policy_revisions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `policy_document_id` bigint UNSIGNED NOT NULL,
  `action` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'created / updated / locked / unlocked',
  `summary` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snapshot` json DEFAULT NULL COMMENT '更新後スナップショット',
  `updated_by_id` bigint UNSIGNED DEFAULT NULL,
  `updated_by_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `policy_revisions_doc_created_index` (`policy_document_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- password_reset_tokens
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- failed_jobs  (Laravel キューシステム)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- migrations  (Laravel マイグレーション管理)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- VIEWs
-- =============================================================================

DROP VIEW IF EXISTS `managers`;
CREATE ALGORITHM=UNDEFINED DEFINER=`risadmin`@`%` SQL SECURITY DEFINER VIEW `managers` AS
  SELECT
    `shop_managers`.`id` AS `id`,
    `shop_managers`.`shop_id` AS `shop_id`,
    `shop_managers`.`name` AS `name`,
    `shop_managers`.`email` AS `email`,
    `shop_managers`.`password` AS `password`,
    `shop_managers`.`role` AS `role`,
    `shop_managers`.`status` AS `status`,
    `shop_managers`.`last_login_at` AS `last_login_at`,
    `shop_managers`.`created_at` AS `created_at`,
    `shop_managers`.`updated_at` AS `updated_at`,
    `shop_profiles`.`shop_name` AS `shop_name`
  FROM (`shop_managers`
    LEFT JOIN `shop_profiles` ON (`shop_managers`.`shop_id` = `shop_profiles`.`shop_id`));

DROP VIEW IF EXISTS `members`;
CREATE ALGORITHM=UNDEFINED DEFINER=`risadmin`@`%` SQL SECURITY DEFINER VIEW `members` AS
  SELECT
    `casts`.`id` AS `id`,
    `casts`.`email` AS `email`,
    `casts`.`password` AS `password`,
    `casts`.`status` AS `status`,
    `casts`.`status` AS `approval`,
    `casts`.`identity_status` AS `identity_status`,
    `casts`.`last_login_at` AS `last_login_at`,
    `casts`.`remember_token` AS `remember_token`,
    `casts`.`created_at` AS `created_at`,
    `casts`.`updated_at` AS `updated_at`,
    `casts`.`deleted_at` AS `deleted_at`,
    (CASE WHEN (`casts`.`deleted_at` IS NULL) THEN 0 ELSE 1 END) AS `del_flg`,
    `cast_profiles`.`nickname` AS `nickname`,
    `cast_profiles`.`name` AS `name`,
    `cast_profiles`.`name_kana` AS `kana`,
    `cast_profiles`.`birthday` AS `birthday`,
    YEAR(`cast_profiles`.`birthday`) AS `birthday_y`,
    MONTH(`cast_profiles`.`birthday`) AS `birthday_m`,
    DAYOFMONTH(`cast_profiles`.`birthday`) AS `birthday_d`,
    `cast_profiles`.`zip` AS `zip`,
    `cast_profiles`.`pref` AS `pref`,
    `cast_profiles`.`city` AS `city`,
    `cast_profiles`.`tel` AS `tel`,
    `cast_profiles`.`height` AS `height`,
    `cast_profiles`.`weight` AS `weight`,
    `cast_profiles`.`bust` AS `b`,
    `cast_profiles`.`waist` AS `w`,
    `cast_profiles`.`hip` AS `h`,
    `cast_profiles`.`profession` AS `profession`,
    `cast_profiles`.`exp` AS `exp`,
    `cast_profiles`.`work_where` AS `where_work`,
    `cast_profiles`.`pr` AS `pr`,
    `cast_profiles`.`latitude` AS `latitude`,
    `cast_profiles`.`longitude` AS `longitude`,
    (SELECT `cp`.`provider_id` FROM `cast_providers` `cp`
     WHERE (`cp`.`cast_id` = `casts`.`id` AND `cp`.`provider` = 'line') LIMIT 1) AS `line_user_id`,
    0 AS `matching`,
    0 AS `release`,
    0 AS `footprints`,
    NULL AS `shop_name`
  FROM (`casts`
    LEFT JOIN `cast_profiles` ON (`casts`.`id` = `cast_profiles`.`cast_id`));

-- =============================================================================
-- 外部キー制約
-- =============================================================================

ALTER TABLE `cast_profiles`
  ADD CONSTRAINT `cast_profiles_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cast_profiles_industry` FOREIGN KEY (`industry_id`) REFERENCES `industries` (`id`) ON DELETE SET NULL;

ALTER TABLE `cast_providers`
  ADD CONSTRAINT `cast_providers_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE;

ALTER TABLE `cast_images`
  ADD CONSTRAINT `cast_images_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE;

ALTER TABLE `cast_identity_documents`
  ADD CONSTRAINT `cast_identity_documents_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE;

ALTER TABLE `cast_posts`
  ADD CONSTRAINT `cast_posts_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE;

ALTER TABLE `cast_shop_relation`
  ADD CONSTRAINT `c_s_rel_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `c_s_rel_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `cast_tag_relations`
  ADD CONSTRAINT `fk_cast_tag_relations_cast` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE;

ALTER TABLE `shop_profiles`
  ADD CONSTRAINT `shop_profiles_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_shop_profiles_industry` FOREIGN KEY (`industry_id`) REFERENCES `industries` (`id`) ON DELETE SET NULL;

ALTER TABLE `shop_managers`
  ADD CONSTRAINT `shop_managers_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `shop_images`
  ADD CONSTRAINT `shop_images_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `shop_posts`
  ADD CONSTRAINT `shop_posts_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `shop_stations`
  ADD CONSTRAINT `fk_shop_stations_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `shop_jobs`
  ADD CONSTRAINT `shop_jobs_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `shop_job_applications`
  ADD CONSTRAINT `s_j_app_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `s_j_app_job_id_foreign` FOREIGN KEY (`shop_job_id`) REFERENCES `shop_jobs` (`id`) ON DELETE CASCADE;

ALTER TABLE `shop_job_tag_relations`
  ADD CONSTRAINT `fk_sj_tag_rel_job` FOREIGN KEY (`shop_job_id`) REFERENCES `shop_jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sj_tag_rel_tag` FOREIGN KEY (`tag_id`) REFERENCES `shop_tags` (`id`) ON DELETE CASCADE;

ALTER TABLE `shop_license_documents`
  ADD CONSTRAINT `shop_license_documents_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `shop_tag_relations`
  ADD CONSTRAINT `fk_shop_tag_relations_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `application_deposits`
  ADD CONSTRAINT `app_dep_app_id_foreign` FOREIGN KEY (`shop_job_application_id`) REFERENCES `shop_job_applications` (`id`) ON DELETE CASCADE;

ALTER TABLE `application_deposit_histories`
  ADD CONSTRAINT `app_dep_hist_dep_id_foreign` FOREIGN KEY (`application_deposit_id`) REFERENCES `application_deposits` (`id`) ON DELETE CASCADE;

ALTER TABLE `payment_tasks`
  ADD CONSTRAINT `payment_tasks_application_deposit_id_foreign` FOREIGN KEY (`application_deposit_id`) REFERENCES `application_deposits` (`id`) ON DELETE CASCADE;

ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `keeps`
  ADD CONSTRAINT `keeps_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keeps_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `footprints`
  ADD CONSTRAINT `footprints_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `footprints_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `messages`
  ADD CONSTRAINT `messages_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `talk_blocks`
  ADD CONSTRAINT `talk_blocks_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `talk_blocks_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE;

ALTER TABLE `review_details`
  ADD CONSTRAINT `fk_review_details_review_id` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_details_val` FOREIGN KEY (`val`) REFERENCES `review_contents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_details_review_content_id_foreign` FOREIGN KEY (`review_content_id`) REFERENCES `review_contents` (`id`) ON DELETE CASCADE;

ALTER TABLE `column_articles`
  ADD CONSTRAINT `column_articles_column_category_id_foreign` FOREIGN KEY (`column_category_id`) REFERENCES `column_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `policy_chapters`
  ADD CONSTRAINT `policy_chapters_policy_document_id_foreign` FOREIGN KEY (`policy_document_id`) REFERENCES `policy_documents` (`id`) ON DELETE CASCADE;

ALTER TABLE `policy_revisions`
  ADD CONSTRAINT `policy_revisions_policy_document_id_foreign` FOREIGN KEY (`policy_document_id`) REFERENCES `policy_documents` (`id`) ON DELETE CASCADE;

SET foreign_key_checks = 1;
>>>>>>> 3022dc1 (refactor: 未使用テーブル・デッドコードの削除とschema.sql再構築)
