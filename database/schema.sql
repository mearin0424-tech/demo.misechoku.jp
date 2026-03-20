-- ==============================================================================
-- ミセチョク DB スキーマ・初期データ（マイグレーション相当）
-- 実行順序の通り、サーバーで直接実行してください。
--
-- 注意:
-- - 新規DBの場合は上から順にそのまま実行可能です。
-- - 既存DBの場合は、既に存在するテーブル/カラムがあると ALTER や CREATE で
--   エラーになることがあります。その場合は該当ブロックをコメントアウトするか、
--   必要な部分だけを抜き出して実行してください。
-- - INSERT は IGNORE にしてあるため、重複時はスキップされます。
-- - LINE 通知は Messaging API のみ（LINE Notify は使用しない）。
--   キャストの LINE ユーザーIDは cast_providers、店舗マネージャーは shop_managers.line_user_id。
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- 1. push_subscriptions (2025_03_08_000001)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `endpoint` varchar(500) NOT NULL,
  `public_key` varchar(255) DEFAULT NULL,
  `auth_token` varchar(255) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. コアスキーマ (2026_03_13_000000_create_misechoku_core_schema)
-- ------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `casts` (
  `id` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `identity_status` tinyint NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `casts_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cast_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) NOT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `name_kana` varchar(100) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` tinyint DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `pref` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `addr1` varchar(100) DEFAULT NULL,
  `addr2` varchar(100) DEFAULT NULL,
  `addr3` varchar(100) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `height` smallint DEFAULT NULL,
  `weight` smallint DEFAULT NULL,
  `bust` smallint DEFAULT NULL,
  `waist` smallint DEFAULT NULL,
  `hip` smallint DEFAULT NULL,
  `shift` int DEFAULT NULL,
  `profession` varchar(1000) DEFAULT NULL,
  `exp` tinyint DEFAULT NULL,
  `years_exp` varchar(100) DEFAULT NULL,
  `where_work` varchar(500) DEFAULT NULL,
  `pr` text,
  `charm_point` text,
  `memo` text,
  `ng_reason` text,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cast_profiles_cast_id_foreign` (`cast_id`),
  CONSTRAINT `cast_profiles_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cast_providers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) NOT NULL,
  `provider` varchar(50) NOT NULL DEFAULT 'line',
  `provider_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cast_providers_provider_id_unique` (`provider`, `provider_id`),
  KEY `cast_providers_cast_id_foreign` (`cast_id`),
  CONSTRAINT `cast_providers_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shops` (
  `id` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `license_status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(20) NOT NULL,
  `shop_name` varchar(255) NOT NULL,
  `opened_on` date DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `pref` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `addr2` varchar(255) NOT NULL,
  `addr3` varchar(255) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `station1` varchar(255) DEFAULT NULL,
  `station2` varchar(255) DEFAULT NULL,
  `catch` varchar(255) DEFAULT NULL,
  `overview` varchar(255) DEFAULT NULL,
  `message` text,
  `memo` text,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_profiles_shop_id_foreign` (`shop_id`),
  CONSTRAINT `shop_profiles_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_managers` (
  `id` varchar(20) NOT NULL,
  `shop_id` varchar(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `line_user_id` varchar(255) DEFAULT NULL COMMENT 'LINE Login ユーザーID（Messaging API push 用）',
  `role` tinyint NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_managers_shop_id_foreign` (`shop_id`),
  CONSTRAINT `shop_managers_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(20) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `hourly_wage_regular` varchar(255) DEFAULT NULL,
  `normal_time` int DEFAULT NULL,
  `noruma_reward` int DEFAULT NULL,
  `noruma_reward2` varchar(255) DEFAULT NULL,
  `hours_day` int DEFAULT NULL,
  `noruma_cond` varchar(2000) DEFAULT NULL,
  `has_trial` tinyint(1) NOT NULL DEFAULT '0',
  `trial_hourly_wage` varchar(255) DEFAULT NULL,
  `has_help` tinyint(1) NOT NULL DEFAULT '0',
  `help_hourly_wage` varchar(255) DEFAULT NULL,
  `job_description` varchar(255) DEFAULT NULL,
  `salary` varchar(255) DEFAULT NULL,
  `atmosphere` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_jobs_shop_id_foreign` (`shop_id`),
  CONSTRAINT `shop_jobs_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_job_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) NOT NULL,
  `shop_job_id` bigint unsigned NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `result_date` date DEFAULT NULL,
  `real_start_date` date DEFAULT NULL,
  `hourly_wage_regular` varchar(255) DEFAULT NULL,
  `normal_time` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_job_applications_cast_id_foreign` (`cast_id`),
  KEY `shop_job_applications_shop_job_id_foreign` (`shop_job_id`),
  CONSTRAINT `shop_job_applications_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shop_job_applications_shop_job_id_foreign` FOREIGN KEY (`shop_job_id`) REFERENCES `shop_jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `application_deposits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shop_job_application_id` bigint unsigned NOT NULL,
  `status` tinyint NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_deposits_shop_job_application_id_foreign` (`shop_job_application_id`),
  CONSTRAINT `application_deposits_shop_job_application_id_foreign` FOREIGN KEY (`shop_job_application_id`) REFERENCES `shop_job_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `application_deposit_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_deposit_id` bigint unsigned NOT NULL,
  `status` tinyint NOT NULL,
  `status_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_deposit_histories_application_deposit_id_foreign` (`application_deposit_id`),
  CONSTRAINT `application_deposit_histories_application_deposit_id_foreign` FOREIGN KEY (`application_deposit_id`) REFERENCES `application_deposits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `favorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) DEFAULT NULL,
  `shop_id` varchar(20) DEFAULT NULL,
  `action_type` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `favorites_cast_id_foreign` (`cast_id`),
  KEY `favorites_shop_id_foreign` (`shop_id`),
  CONSTRAINT `favorites_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) NOT NULL,
  `shop_id` varchar(20) NOT NULL,
  `sender_type` tinyint NOT NULL,
  `type` tinyint NOT NULL DEFAULT '1',
  `content` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_cast_id_foreign` (`cast_id`),
  KEY `messages_shop_id_foreign` (`shop_id`),
  CONSTRAINT `messages_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) NOT NULL,
  `shop_id` varchar(20) NOT NULL,
  `contents` text,
  `eva` decimal(3,1) NOT NULL DEFAULT '0.0',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_cast_id_foreign` (`cast_id`),
  KEY `reviews_shop_id_foreign` (`shop_id`),
  CONSTRAINT `reviews_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `body` text COMMENT 'ひとこと',
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
  `name` varchar(100) COMMENT '管理者名',
  `email` varchar(255) NOT NULL COMMENT 'ログインメールアドレス',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL COMMENT 'ハッシュ化パスワード',
  `role` varchar(20) NOT NULL DEFAULT 'staff' COMMENT '権限(admin:全機能, staff:一部機能)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '有効フラグ(falseでログイン不可)',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_accounts_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- コア初期データ
INSERT IGNORE INTO `casts` (`id`, `email`, `password`, `status`, `identity_status`, `last_login_at`, `created_at`, `updated_at`) VALUES
('c00000001', 'cast01@example.com', '$2y$10$dummyhashedpassword01', 1, 3, '2026-03-01 10:00:00', '2026-01-10 12:00:00', '2026-03-01 10:00:00'),
('c00000002', 'cast02@example.com', '$2y$10$dummyhashedpassword02', 1, 3, '2026-03-05 15:30:00', '2026-02-15 09:00:00', '2026-03-05 15:30:00'),
('c00000003', 'cast03@example.com', '$2y$10$dummyhashedpassword03', 1, 1, '2026-03-10 20:15:00', '2026-03-01 18:45:00', '2026-03-10 20:15:00');

INSERT IGNORE INTO `cast_profiles` (`cast_id`, `nickname`, `name`, `birthday`, `gender`, `pref`, `city`, `height`, `weight`, `bust`, `waist`, `hip`, `profession`, `exp`, `pr`) VALUES
('c00000001', 'みさき', '桜井美咲', '2001-05-15', 1, '東京都', '港区', 160, 48, 85, 58, 86, 'アパレル店員', 1, '楽しくお話しするのが大好きです！よろしくお願いします。'),
('c00000002', 'あい', '山田愛', '1999-10-22', 1, '神奈川県', '横浜市', 155, 45, 82, 56, 84, '美容師', 1, '週末メインで働きたいです！'),
('c00000003', 'ユナ', '佐藤結衣', '2003-02-14', 1, '埼玉県', 'さいたま市', 165, 50, 88, 60, 89, '大学生', 0, '未経験ですが一生懸命頑張ります！');

INSERT IGNORE INTO `cast_providers` (`cast_id`, `provider`, `provider_id`) VALUES
('c00000001', 'line', 'U11112222333344445555666677778888'),
('c00000002', 'line', 'Uaaaabbbbccccddddeeeeffffgggghhhh'),
('c00000003', 'line', 'Uzzzzxxxxccccvvvvbbbbnnnnmmmmkkkk');

INSERT IGNORE INTO `shops` (`id`, `email`, `status`, `license_status`, `created_at`, `updated_at`) VALUES
('s00000001', 'info@club-luminous.example.com', 1, 3, '2025-12-01 10:00:00', '2025-12-05 10:00:00'),
('s00000002', 'contact@lounge-stella.example.com', 1, 3, '2026-01-15 14:00:00', '2026-01-20 14:00:00');

INSERT IGNORE INTO `shop_profiles` (`shop_id`, `shop_name`, `opened_on`, `pref`, `city`, `addr2`, `addr3`, `station1`, `catch`, `overview`, `message`) VALUES
('s00000001', 'Club Luminous (ルミナス)', '2015-04-01', '東京都', '港区', '六本木3-1-1', 'ルミナスビル2F', '六本木駅 徒歩3分', '落ち着いた雰囲気の高級クラブ', '未経験からでもしっかりサポートする安心の環境です。', '一緒に楽しく働ける方をお待ちしております！'),
('s00000002', 'Lounge Stella (ステラ)', '2020-09-15', '東京都', '新宿区', '歌舞伎町1-2-3', 'ステラタワー5F', '新宿駅 徒歩5分', 'アットホームで働きやすいラウンジ', 'ノルマなし！あなたのペースで働けます。', '学生さんやWワークの方も大歓迎です。');

INSERT IGNORE INTO `shop_managers` (`id`, `shop_id`, `name`, `email`, `password`, `role`, `status`, `last_login_at`) VALUES
('m00000001', 's00000001', '佐藤 店長', 'sato.mgr@club-luminous.example.com', '$2y$10$dummyhashedpasswordM1', 1, 1, '2026-03-12 18:00:00'),
('m00000002', 's00000002', '鈴木 オーナー', 'suzuki.owner@lounge-stella.example.com', '$2y$10$dummyhashedpasswordM2', 1, 1, '2026-03-11 22:30:00');

INSERT IGNORE INTO `shop_jobs` (`id`, `shop_id`, `hourly_wage_regular`, `normal_time`, `has_trial`, `trial_hourly_wage`, `has_help`, `help_hourly_wage`, `job_description`, `created_at`, `updated_at`) VALUES
(1, 's00000001', '5000', 5, 1, '4000', 1, '3500', 'お客様と楽しくおしゃべりしてお酒を作るお仕事です。', '2025-12-05 12:00:00', '2025-12-05 12:00:00'),
(2, 's00000002', '3500', 4, 1, '3000', 0, NULL, '簡単なドリンク作成と接客をお任せします。ノルマなし！', '2026-01-20 15:00:00', '2026-01-20 15:00:00');

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

INSERT IGNORE INTO `favorites` (`cast_id`, `shop_id`, `action_type`, `created_at`) VALUES
('c00000001', 's00000002', 1, '2026-01-12 20:00:00'),
('c00000002', 's00000001', 3, '2026-02-10 21:15:00');

INSERT IGNORE INTO `messages` (`cast_id`, `shop_id`, `sender_type`, `content`, `is_read`, `created_at`) VALUES
('c00000002', 's00000002', 1, '面接をお願いしたいです！', 1, '2026-03-05 18:05:00'),
('c00000002', 's00000002', 2, 'ご応募ありがとうございます。今週の土曜日の19時はいかがでしょうか？', 1, '2026-03-05 19:00:00'),
('c00000003', 's00000001', 1, '未経験ですが応募可能でしょうか？', 0, '2026-03-10 21:05:00');

INSERT IGNORE INTO `reviews` (`id`, `cast_id`, `shop_id`, `contents`, `created_at`) VALUES
(1, 'c00000001', 's00000001', 'スタッフの皆さんが優しくて、とても働きやすいお店でした！', '2026-02-28 10:00:00');

INSERT IGNORE INTO `tags` (`id`, `type`, `name`, `created_at`) VALUES
(1, 'salary', '1ヶ月払い', '2025-01-14 05:33:11'),
(8, 'salary', '交通費支給', '2025-01-14 05:33:12'),
(14, 'howto', '週1からOK', '2025-01-14 05:33:12'),
(82, 'casttag', 'スレンダー', '2025-01-14 05:33:13'),
(89, 'casttag', 'キレイ系', '2025-01-14 05:33:13');

INSERT IGNORE INTO `cast_tag` (`cast_id`, `tag_id`) VALUES
('c00000001', 82), ('c00000001', 89), ('c00000002', 82);

INSERT IGNORE INTO `shop_tag` (`shop_id`, `tag_id`) VALUES
('s00000001', 8), ('s00000002', 14);

INSERT IGNORE INTO `system_accounts` (`name`, `email`, `password`, `role`, `is_active`, `email_verified_at`, `created_at`, `updated_at`) VALUES
('管理者アカウント１', 'admin@misechoku.jp', '$2y$10$dummyhashedpasswordAdmin01', 'admin', 1, '2025-01-01 00:00:00', '2025-01-01 00:00:00', '2025-01-01 00:00:00');

-- ------------------------------------------------------------------------------
-- 3. 管理マスタ・review_contents (2026_03_13_000002_create_admin_master_tables)
-- ------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `review_contents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL COMMENT '設問内容',
  `del_flg` tinyint(1) NOT NULL DEFAULT '0' COMMENT '削除フラグ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ng_words` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ng_words_word_unique` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `review_contents` (`id`, `content`, `del_flg`, `created_at`, `updated_at`) VALUES
(1, '接客', 0, '2025-01-14 05:33:10', '2025-01-14 05:33:10'),
(2, '雰囲気', 0, '2025-01-14 05:33:10', '2025-01-14 05:33:10'),
(3, '給与条件', 0, '2025-01-14 05:33:10', '2025-01-14 05:33:10'),
(4, '働きやすさ', 0, '2025-01-14 05:33:10', '2025-01-14 05:33:10');

INSERT IGNORE INTO `ng_words` (`word`, `is_active`, `created_at`, `updated_at`) VALUES
('個人連絡先', 1, '2025-01-14 05:33:10', '2025-01-14 05:33:10'),
('直引き', 1, '2025-01-14 05:33:10', '2025-01-14 05:33:10'),
('裏オプ', 1, '2025-01-14 05:33:10', '2025-01-14 05:33:10');

INSERT IGNORE INTO `review_details` (`review_id`, `review_content_id`, `score`) VALUES
(1, 1, 5.0), (1, 2, 5.0), (1, 3, 4.0), (1, 4, 5.0);

ALTER TABLE `review_details`
  ADD CONSTRAINT `review_details_review_content_id_foreign` FOREIGN KEY (`review_content_id`) REFERENCES `review_contents` (`id`) ON DELETE CASCADE;

-- ------------------------------------------------------------------------------
-- 4. 請求・入金サポート (2026_03_13_010000_add_billing_management_support_tables)
-- ------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `bank_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `holder_type` varchar(255) NOT NULL COMMENT '所有者モデル (casts, shops, users等)',
  `holder_id` varchar(20) NOT NULL COMMENT '所有者のID (c0001, s0001等)',
  `bank_code` varchar(4) NOT NULL COMMENT '銀行コード (4桁)',
  `bank_name` varchar(100) NOT NULL,
  `bank_name_kana` varchar(100) NOT NULL,
  `branch_code` varchar(3) NOT NULL COMMENT '支店コード (3桁)',
  `branch_name` varchar(100) NOT NULL,
  `branch_name_kana` varchar(100) NOT NULL,
  `account_type` varchar(20) NOT NULL,
  `account_number` varchar(8) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_holder` (`holder_type`, `holder_id`),
  KEY `idx_holder` (`holder_type`, `holder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `application_deposits`
  ADD COLUMN `invoice_number` varchar(50) DEFAULT NULL AFTER `is_read`,
  ADD COLUMN `bonus_amount` int DEFAULT NULL AFTER `invoice_number`,
  ADD COLUMN `system_fee_amount` int DEFAULT NULL AFTER `bonus_amount`,
  ADD COLUMN `invoice_amount` int DEFAULT NULL AFTER `system_fee_amount`,
  ADD COLUMN `cast_transfer_amount` int DEFAULT NULL AFTER `invoice_amount`,
  ADD COLUMN `invoice_issued_at` timestamp NULL DEFAULT NULL AFTER `cast_transfer_amount`,
  ADD COLUMN `invoice_due_date` date DEFAULT NULL AFTER `invoice_issued_at`,
  ADD COLUMN `invoice_sent_at` timestamp NULL DEFAULT NULL AFTER `invoice_due_date`,
  ADD COLUMN `shop_payment_reported_at` timestamp NULL DEFAULT NULL AFTER `invoice_sent_at`,
  ADD COLUMN `shop_payment_reported_amount` int DEFAULT NULL AFTER `shop_payment_reported_at`,
  ADD COLUMN `shop_payment_reference` varchar(255) DEFAULT NULL AFTER `shop_payment_reported_amount`,
  ADD COLUMN `shop_payment_confirmed_at` timestamp NULL DEFAULT NULL AFTER `shop_payment_reference`,
  ADD COLUMN `cast_transferred_at` timestamp NULL DEFAULT NULL AFTER `shop_payment_confirmed_at`,
  ADD COLUMN `cast_transfer_reference` varchar(255) DEFAULT NULL AFTER `cast_transferred_at`,
  ADD COLUMN `cast_transfer_note` text AFTER `cast_transfer_reference`,
  ADD COLUMN `completed_at` timestamp NULL DEFAULT NULL AFTER `cast_transfer_note`;

INSERT IGNORE INTO `bank_accounts` (`holder_type`, `holder_id`, `bank_code`, `bank_name`, `bank_name_kana`, `branch_code`, `branch_name`, `branch_name_kana`, `account_type`, `account_number`, `account_name`, `created_at`, `updated_at`) VALUES
('system_accounts', '1', '0001', 'みせちょく銀行', 'ﾐｾﾁｮｸ', '001', '本店営業部', 'ﾎﾝﾃﾝ', 'ordinary', '1234567', 'ﾐｾﾁｮｸｳﾝｴｲ', NOW(), NOW()),
('shops', 's00000001', '0002', '六本木銀行', 'ﾛｯﾎﾟﾝｷﾞ', '101', '六本木支店', 'ﾛｯﾎﾟﾝｷﾞ', 'ordinary', '7654321', 'ｸﾗﾌﾞﾙﾐﾅｽ', NOW(), NOW()),
('shops', 's00000002', '0003', '新宿銀行', 'ｼﾝｼﾞｭｸ', '102', '歌舞伎町支店', 'ｶﾌﾞｷﾁｮｳ', 'ordinary', '1122334', 'ﾗｳﾝｼﾞｽﾃﾗ', NOW(), NOW()),
('casts', 'c00000001', '0004', '渋谷銀行', 'ｼﾌﾞﾔ', '201', '青山支店', 'ｱｵﾔﾏ', 'ordinary', '2200113', 'ｻｸﾗｲﾐｻｷ', NOW(), NOW()),
('casts', 'c00000002', '0005', '横浜銀行', 'ﾖｺﾊﾏ', '202', '横浜中央支店', 'ﾖｺﾊﾏﾁｭｳｵｳ', 'ordinary', '3344556', 'ﾔﾏﾀﾞｱｲ', NOW(), NOW());

UPDATE `application_deposits` SET
  `invoice_number` = 'INV-202602-0001',
  `bonus_amount` = 30000,
  `system_fee_amount` = 3000,
  `invoice_amount` = 33000,
  `cast_transfer_amount` = 30000,
  `invoice_issued_at` = '2026-02-16 11:30:00',
  `invoice_due_date` = '2026-02-23',
  `invoice_sent_at` = '2026-02-16 11:35:00',
  `shop_payment_reported_at` = '2026-02-19 10:15:00',
  `shop_payment_reported_amount` = 33000,
  `shop_payment_reference` = 'RCP-20260219-01',
  `shop_payment_confirmed_at` = '2026-02-20 10:00:00',
  `cast_transferred_at` = '2026-02-20 14:30:00',
  `cast_transfer_reference` = 'TRF-20260220-01',
  `cast_transfer_note` = '窓口振込を実施済み'
WHERE `id` = 1;

-- ------------------------------------------------------------------------------
-- 5. 2026_03_13_020000_add_account_holder_name_to_bank_tables → 何もしない(no-op)
-- ------------------------------------------------------------------------------

-- ------------------------------------------------------------------------------
-- 6. talk_blocks (2026_03_13_020000_create_talk_blocks_table)
-- ------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `talk_blocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) NOT NULL,
  `shop_id` varchar(20) NOT NULL,
  `blocked_by` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `talk_blocks_cast_id_shop_id_unique` (`cast_id`, `shop_id`),
  KEY `talk_blocks_cast_id_foreign` (`cast_id`),
  KEY `talk_blocks_shop_id_foreign` (`shop_id`),
  CONSTRAINT `talk_blocks_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `talk_blocks_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 7. レガシービュー (2026_03_14_000000_create_legacy_member_manager_views)
--    members.line_user_id は cast_providers（provider=line）から取得（LINE Notify 列は廃止）
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
  0 AS `matching`, 0 AS `release`, 0 AS `footprints`, NULL AS shop_name
FROM casts
LEFT JOIN cast_profiles ON casts.id = cast_profiles.cast_id;

CREATE VIEW `managers` AS
SELECT shop_managers.*, shop_profiles.shop_name
FROM shop_managers
LEFT JOIN shop_profiles ON shop_managers.shop_id = shop_profiles.shop_id;

-- ------------------------------------------------------------------------------
-- 8. 採用ボーナススナップショット (2026_03_15_000000_add_hired_bonus_snapshot)
-- ------------------------------------------------------------------------------

ALTER TABLE `shop_job_applications`
  ADD COLUMN `hired_bonus_amount` int DEFAULT NULL AFTER `normal_time`,
  ADD COLUMN `hired_bonus_condition` text DEFAULT NULL AFTER `hired_bonus_amount`;

-- 既存の採用済み(status=4)でスナップショット未設定の行を求人からバックフィル（必要に応じて実行）
-- UPDATE shop_job_applications sja
-- INNER JOIN shop_jobs sj ON sja.shop_job_id = sj.id
-- SET sja.hired_bonus_amount = COALESCE(sj.noruma_reward, sj.hourly_wage_regular, 0),
--     sja.hired_bonus_condition = ...
-- WHERE sja.status = 4 AND sja.hired_bonus_amount IS NULL;

-- ------------------------------------------------------------------------------
-- 9. 請求書テンプレート設定 (2026_03_16_000000_create_invoice_template_settings_table)
-- ------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `invoice_template_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL COMMENT 'issuer_name, issuer_email, logo_url, footer_text 等',
  `value` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_template_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 10. 振込作業フェイルセーフ (2026_03_17_000000_create_payment_tasks_table)
-- 1 application_deposit = 1 PaymentTask（UNIQUE で二重支払い防止）
-- ------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `payment_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_deposit_id` bigint unsigned NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=待機 1=支払準備中 2=振込中 3=支払済 4=無効',
  `shop_received_amount` int unsigned NOT NULL COMMENT '店舗入金額スナップショット',
  `platform_fee_amount` int unsigned NOT NULL DEFAULT '0' COMMENT 'プラットフォーム手数料',
  `bank_fee_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '銀行振込手数料',
  `payout_amount` int unsigned NOT NULL COMMENT 'キャスト振込額（自動計算）',
  `transferred_at` timestamp NULL DEFAULT NULL COMMENT '振込作業完了日時',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '支払済確定日時',
  `evidence_file_path` varchar(500) DEFAULT NULL COMMENT '振込完了証跡画像',
  `checklist_confirmed_account` tinyint(1) NOT NULL DEFAULT '0',
  `checklist_confirmed_amount` tinyint(1) NOT NULL DEFAULT '0',
  `operator_id` varchar(20) DEFAULT NULL COMMENT '振込作業担当者ID',
  `refund_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '要返金フラグ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_tasks_application_deposit_id_unique` (`application_deposit_id`),
  KEY `payment_tasks_application_deposit_id_foreign` (`application_deposit_id`),
  CONSTRAINT `payment_tasks_application_deposit_id_foreign` FOREIGN KEY (`application_deposit_id`) REFERENCES `application_deposits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
