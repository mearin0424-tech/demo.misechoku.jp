-- ==============================================================================
-- 繝溘そ繝√Ι繧ｯ DB 繧ｹ繧ｭ繝ｼ繝槭・蛻晄悄繝・・繧ｿ・医・繧､繧ｰ繝ｬ繝ｼ繧ｷ繝ｧ繝ｳ逶ｸ蠖難ｼ・
-- 螳溯｡碁・ｺ上・騾壹ｊ縲√し繝ｼ繝舌・縺ｧ逶ｴ謗･螳溯｡後＠縺ｦ縺上□縺輔＞縲・
--
-- 豕ｨ諢・
-- - 譁ｰ隕愁B縺ｮ蝣ｴ蜷医・荳翫°繧蛾・↓縺昴・縺ｾ縺ｾ螳溯｡悟庄閭ｽ縺ｧ縺吶・
-- - 譌｢蟄魯B縺ｮ蝣ｴ蜷医・縲∵里縺ｫ蟄伜惠縺吶ｋ繝・・繝悶Ν/繧ｫ繝ｩ繝縺後≠繧九→ ALTER 繧・CREATE 縺ｧ
--   繧ｨ繝ｩ繝ｼ縺ｫ縺ｪ繧九％縺ｨ縺後≠繧翫∪縺吶ゅ◎縺ｮ蝣ｴ蜷医・隧ｲ蠖薙ヶ繝ｭ繝・け繧偵さ繝｡繝ｳ繝医い繧ｦ繝医☆繧九°縲・
--   蠢・ｦ√↑驛ｨ蛻・□縺代ｒ謚懊″蜃ｺ縺励※螳溯｡後＠縺ｦ縺上□縺輔＞縲・
-- - INSERT 縺ｯ IGNORE 縺ｫ縺励※縺ゅｋ縺溘ａ縲・㍾隍・凾縺ｯ繧ｹ繧ｭ繝・・縺輔ｌ縺ｾ縺吶・
-- - LINE 騾夂衍縺ｯ Messaging API 縺ｮ縺ｿ・・INE Notify 縺ｯ菴ｿ逕ｨ縺励↑縺・ｼ峨・
--   繧ｭ繝｣繧ｹ繝医・ LINE 繝ｦ繝ｼ繧ｶ繝ｼID縺ｯ cast_providers縲∝ｺ苓・繝槭ロ繝ｼ繧ｸ繝｣繝ｼ縺ｯ shop_managers.line_user_id縲・
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
-- 2. 繧ｳ繧｢繧ｹ繧ｭ繝ｼ繝・(2026_03_13_000000_create_misechoku_core_schema)
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
  -- 以下は Eloquent 暗号化キャストの対象（AES-256-CBC ciphertext を格納するため TEXT に拡張）
  `name` text DEFAULT NULL,
  `name_kana` text DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` tinyint DEFAULT NULL,
  `zip` text DEFAULT NULL,
  `pref` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `addr1` text DEFAULT NULL,
  `addr2` text DEFAULT NULL,
  `addr3` text DEFAULT NULL,
  `tel` text DEFAULT NULL,
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
  -- 以下は Eloquent 暗号化キャストの対象（TEXT に拡張）
  `zip` text DEFAULT NULL,
  `pref` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `addr2` text NOT NULL,
  `addr3` text DEFAULT NULL,
  `tel` text DEFAULT NULL,
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
  `name` text DEFAULT NULL COMMENT 'Eloquent 暗号化対象（本名）',
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `line_user_id` text DEFAULT NULL COMMENT 'Eloquent 暗号化対象（LINE Login ユーザID）',
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

INSERT IGNORE INTO `favorites` (`cast_id`, `shop_id`, `action_type`, `created_at`) VALUES
('c00000001', 's00000002', 1, '2026-01-12 20:00:00'),
('c00000002', 's00000001', 3, '2026-02-10 21:15:00');

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

CREATE TABLE IF NOT EXISTS `review_contents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL COMMENT '險ｭ蝠丞・螳ｹ',
  `del_flg` tinyint(1) NOT NULL DEFAULT '0' COMMENT '蜑企勁繝輔Λ繧ｰ',
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
(1, '謗･螳｢', 0, '2025-01-14 05:33:10', '2025-01-14 05:33:10'),
(2, '髮ｰ蝗ｲ豌・, 0, '2025-01-14 05:33:10', '2025-01-14 05:33:10'),
(3, '邨ｦ荳取擅莉ｶ', 0, '2025-01-14 05:33:10', '2025-01-14 05:33:10'),
(4, '蜒阪″繧・☆縺・, 0, '2025-01-14 05:33:10', '2025-01-14 05:33:10');

INSERT IGNORE INTO `ng_words` (`word`, `is_active`, `created_at`, `updated_at`) VALUES
('蛟倶ｺｺ騾｣邨｡蜈・, 1, '2025-01-14 05:33:10', '2025-01-14 05:33:10'),
('逶ｴ蠑輔″', 1, '2025-01-14 05:33:10', '2025-01-14 05:33:10'),
('陬上が繝・, 1, '2025-01-14 05:33:10', '2025-01-14 05:33:10');

INSERT IGNORE INTO `review_details` (`review_id`, `review_content_id`, `score`) VALUES
(1, 1, 5.0), (1, 2, 5.0), (1, 3, 4.0), (1, 4, 5.0);

ALTER TABLE `review_details`
  ADD CONSTRAINT `review_details_review_content_id_foreign` FOREIGN KEY (`review_content_id`) REFERENCES `review_contents` (`id`) ON DELETE CASCADE;

-- ------------------------------------------------------------------------------
-- 4. 隲区ｱゅ・蜈･驥代し繝昴・繝・(2026_03_13_010000_add_billing_management_support_tables)
-- ------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `bank_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `holder_type` varchar(255) NOT NULL COMMENT '謇譛芽・Δ繝・Ν (casts, shops, users遲・',
  `holder_id` varchar(20) NOT NULL COMMENT '謇譛芽・・ID (c0001, s0001遲・',
  `bank_code` varchar(4) NOT NULL COMMENT '驫陦後さ繝ｼ繝・(4譯・',
  `bank_name` varchar(100) NOT NULL,
  `bank_name_kana` varchar(100) NOT NULL,
  `branch_code` varchar(3) NOT NULL COMMENT '謾ｯ蠎励さ繝ｼ繝・(3譯・',
  `branch_name` varchar(100) NOT NULL,
  `branch_name_kana` varchar(100) NOT NULL,
  `account_type` varchar(20) NOT NULL,
  -- Eloquent 暗号化対象（口座番号・口座名義）
  `account_number` text NOT NULL,
  `account_name` text NOT NULL,
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
('system_accounts', '1', '0001', '縺ｿ縺帙■繧・￥驫陦・, '・撰ｽｾ・・ｽｮ・ｸ', '001', '譛ｬ蠎怜霧讌ｭ驛ｨ', '・趣ｾ晢ｾ・ｾ・, 'ordinary', '1234567', '・撰ｽｾ・・ｽｮ・ｸ・ｳ・晢ｽｴ・ｲ', NOW(), NOW()),
('shops', 's00000001', '0002', '蜈ｭ譛ｬ譛ｨ驫陦・, '・幢ｽｯ・趣ｾ滂ｾ晢ｽｷ・・, '101', '蜈ｭ譛ｬ譛ｨ謾ｯ蠎・, '・幢ｽｯ・趣ｾ滂ｾ晢ｽｷ・・, 'ordinary', '7654321', '・ｸ・暦ｾ鯉ｾ橸ｾ呻ｾ撰ｾ・ｽｽ', NOW(), NOW()),
('shops', 's00000002', '0003', '譁ｰ螳ｿ驫陦・, '・ｼ・晢ｽｼ・橸ｽｭ・ｸ', '102', '豁瑚・莨守伴謾ｯ蠎・, '・ｶ・鯉ｾ橸ｽｷ・・ｽｮ・ｳ', 'ordinary', '1122334', '・暦ｽｳ・晢ｽｼ・橸ｽｽ・・ｾ・, NOW(), NOW()),
('casts', 'c00000001', '0004', '貂玖ｰｷ驫陦・, '・ｼ・鯉ｾ橸ｾ・, '201', '髱貞ｱｱ謾ｯ蠎・, '・ｱ・ｵ・費ｾ・, 'ordinary', '2200113', '・ｻ・ｸ・暦ｽｲ・撰ｽｻ・ｷ', NOW(), NOW()),
('casts', 'c00000002', '0005', '讓ｪ豬憺橿陦・, '・厄ｽｺ・奇ｾ・, '202', '讓ｪ豬應ｸｭ螟ｮ謾ｯ蠎・, '・厄ｽｺ・奇ｾ擾ｾ・ｽｭ・ｳ・ｵ・ｳ', 'ordinary', '3344556', '・費ｾ擾ｾ・橸ｽｱ・ｲ', NOW(), NOW());

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
  `cast_transfer_note` = '遯灘哨謖ｯ霎ｼ繧貞ｮ滓命貂医∩'
WHERE `id` = 1;

-- ------------------------------------------------------------------------------
-- 5. 2026_03_13_020000_add_account_holder_name_to_bank_tables 竊・菴輔ｂ縺励↑縺・no-op)
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
  0 AS `matching`, 0 AS `release`, 0 AS `footprints`, NULL AS shop_name
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

CREATE TABLE IF NOT EXISTS `invoice_template_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL COMMENT 'issuer_name, issuer_email, logo_url, footer_text 遲・,
  `value` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_template_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 10. 謖ｯ霎ｼ菴懈･ｭ繝輔ぉ繧､繝ｫ繧ｻ繝ｼ繝・(2026_03_17_000000_create_payment_tasks_table)
-- 1 application_deposit = 1 PaymentTask・・NIQUE 縺ｧ莠碁㍾謾ｯ謇輔＞髦ｲ豁｢・・
-- ------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `payment_tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_deposit_id` bigint unsigned NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=蠕・ｩ・1=謾ｯ謇墓ｺ門ｙ荳ｭ 2=謖ｯ霎ｼ荳ｭ 3=謾ｯ謇墓ｸ・4=辟｡蜉ｹ',
  `shop_received_amount` int unsigned NOT NULL COMMENT '蠎苓・蜈･驥鷹｡阪せ繝翫ャ繝励す繝ｧ繝・ヨ',
  `platform_fee_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '繝励Λ繝・ヨ繝輔か繝ｼ繝謇区焚譁・,
  `bank_fee_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '驫陦梧険霎ｼ謇区焚譁・,
  `payout_amount` int unsigned NOT NULL COMMENT '繧ｭ繝｣繧ｹ繝域険霎ｼ鬘搾ｼ郁・蜍戊ｨ育ｮ暦ｼ・,
  `transferred_at` timestamp NULL DEFAULT NULL COMMENT '謖ｯ霎ｼ菴懈･ｭ螳御ｺ・律譎・,
  `completed_at` timestamp NULL DEFAULT NULL COMMENT '謾ｯ謇墓ｸ育｢ｺ螳壽律譎・,
  `evidence_file_path` varchar(500) DEFAULT NULL COMMENT '謖ｯ霎ｼ螳御ｺ・ｨｼ霍｡逕ｻ蜒・,
  `checklist_confirmed_account` tinyint(1) NOT NULL DEFAULT '0',
  `checklist_confirmed_amount` tinyint(1) NOT NULL DEFAULT '0',
  `operator_id` varchar(20) DEFAULT NULL COMMENT '謖ｯ霎ｼ菴懈･ｭ諡・ｽ楢・D',
  `refund_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '隕∬ｿ秘≡繝輔Λ繧ｰ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_tasks_application_deposit_id_unique` (`application_deposit_id`),
  KEY `payment_tasks_application_deposit_id_foreign` (`application_deposit_id`),
  CONSTRAINT `payment_tasks_application_deposit_id_foreign` FOREIGN KEY (`application_deposit_id`) REFERENCES `application_deposits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 11. PWA/LINE 繝ｪ繝槭う繝ｳ繝繝ｼ驕狗畑險ｭ螳・(2026_03_20)
-- ------------------------------------------------------------------------------

ALTER TABLE `push_subscriptions`
  ADD COLUMN `user_type` varchar(32) DEFAULT NULL AFTER `id`,
  ADD COLUMN `user_id` varchar(32) DEFAULT NULL AFTER `user_type`,
  ADD INDEX `push_subscriptions_user_idx` (`user_type`, `user_id`);

-- ------------------------------------------------------------------------------
-- 運営操作ログ（書類審査・アカウント停止・ロール変更・振込実行などを追跡）
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_operation_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `operator_id` bigint unsigned DEFAULT NULL COMMENT 'system_accounts.id',
  `operator_email` varchar(255) DEFAULT NULL,
  `operator_role` varchar(20) DEFAULT NULL,
  `action` varchar(64) NOT NULL COMMENT 'cast.suspend / role.update など',
  `target_type` varchar(40) DEFAULT NULL COMMENT 'cast / shop / cast_identity_document / role 等',
  `target_id` varchar(64) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `payload` text COMMENT 'before/after などのJSON',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_operation_logs_operator_idx` (`operator_id`),
  KEY `admin_operation_logs_action_idx` (`action`),
  KEY `admin_operation_logs_target_idx` (`target_type`, `target_id`),
  KEY `admin_operation_logs_created_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 運営管理：通知・リマインダー・未済タスクの仕様設定
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_notification_settings` (
  `type` varchar(20) NOT NULL COMMENT 'notification / reminder / task',
  `key` varchar(80) NOT NULL COMMENT 'カタログ上のユニークキー',
  `enabled` tinyint(1) DEFAULT NULL COMMENT 'ON/OFF（通知のみ）',
  `offset_value` int DEFAULT NULL COMMENT '発火タイミング（リマインダーのみ。単位はカタログ依存）',
  `title` varchar(255) DEFAULT NULL,
  `body` text,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`type`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 運営アカウントのロール別権限設定
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_role_permissions` (
  `role` varchar(32) NOT NULL COMMENT 'system_accounts.role と対応',
  `permissions` text NOT NULL COMMENT '許可する権限キーのJSON配列',
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notification_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_type` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `push_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `line_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `interview_reminder_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `deadline_reminder_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_preferences_user_unique` (`user_type`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 本人確認書類・店舗書類（Eloquent 暗号化対象列を含むため新規/再作成は TEXT 型）
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cast_identity_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cast_id` varchar(20) NOT NULL,
  `category` varchar(32) NOT NULL DEFAULT 'photo_id'
    COMMENT 'photo_id:顔写真付身分証 / non_photo_id:顔写真なし身分証 / address_proof:住所確認書類',
  `type` varchar(40) NOT NULL
    COMMENT 'photo_id: driver_license/passport/mynumber_card/residence_card | non_photo_id: health_insurance/pension_book | address_proof: residence_certificate/utility_bill',
  `image_path_front` text DEFAULT NULL COMMENT 'Eloquent 暗号化対象',
  `image_path_back`  text DEFAULT NULL COMMENT 'Eloquent 暗号化対象',
  `status` tinyint NOT NULL DEFAULT 1,
  `ng_reason` text DEFAULT NULL COMMENT 'Eloquent 暗号化対象',
  `expired_at` date DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  -- 1キャストあたり同一カテゴリは1書類のみ
  UNIQUE KEY `cast_identity_documents_cast_category_unique` (`cast_id`, `category`),
  CONSTRAINT `cast_identity_documents_cast_id_foreign` FOREIGN KEY (`cast_id`) REFERENCES `casts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_license_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(20) NOT NULL,
  `type` varchar(40) NOT NULL,
  `image_path` text DEFAULT NULL COMMENT 'Eloquent 暗号化対象',
  `status` tinyint NOT NULL DEFAULT 0,
  `ng_reason` text DEFAULT NULL COMMENT 'Eloquent 暗号化対象',
  `expired_at` date DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_license_documents_unique` (`shop_id`, `type`),
  CONSTRAINT `shop_license_documents_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 既存テーブル向け：暗号化対応のためのカラム拡張（既存 DB に対しても流せるよう冪等的に書く）
-- AES-256-CBC + base64+JSON 包装の ciphertext は 短い PII でも 150 文字超になるため TEXT へ変更する
-- ------------------------------------------------------------------------------
ALTER TABLE `cast_profiles`
  MODIFY COLUMN `name`      text DEFAULT NULL,
  MODIFY COLUMN `name_kana` text DEFAULT NULL,
  MODIFY COLUMN `zip`       text DEFAULT NULL,
  MODIFY COLUMN `addr1`     text DEFAULT NULL,
  MODIFY COLUMN `addr2`     text DEFAULT NULL,
  MODIFY COLUMN `addr3`     text DEFAULT NULL,
  MODIFY COLUMN `tel`       text DEFAULT NULL;

ALTER TABLE `shop_profiles`
  MODIFY COLUMN `zip`       text DEFAULT NULL,
  MODIFY COLUMN `addr2`     text NOT NULL,
  MODIFY COLUMN `addr3`     text DEFAULT NULL,
  MODIFY COLUMN `tel`       text DEFAULT NULL;

ALTER TABLE `shop_managers`
  MODIFY COLUMN `name`         text DEFAULT NULL,
  MODIFY COLUMN `line_user_id` text DEFAULT NULL;

ALTER TABLE `bank_accounts`
  MODIFY COLUMN `account_number` text NOT NULL,
  MODIFY COLUMN `account_name`   text NOT NULL;
