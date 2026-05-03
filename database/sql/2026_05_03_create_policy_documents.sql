-- ==============================================================================
-- 規約管理（運営協会／利用規約／プライバシーポリシー） テーブル群（手動実行用）
-- DB: MySQL 5.7+ / MariaDB 10.3+ 想定
-- ==============================================================================

CREATE TABLE IF NOT EXISTS `policy_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL COMMENT 'about / terms / privacy',
  `title` varchar(200) NOT NULL COMMENT 'ページタイトル',
  `lead_title` varchar(200) DEFAULT NULL COMMENT 'リード見出し（例: GREETING / 理事長 挨拶）',
  `lead_body` text DEFAULT NULL COMMENT 'リード本文（運営協会の挨拶文等）',
  `meta` json DEFAULT NULL COMMENT '協会概要などの構造化データ',
  `is_locked` tinyint(1) NOT NULL DEFAULT '1' COMMENT '既定はロック状態（編集不可）',
  `updated_by_id` bigint unsigned DEFAULT NULL COMMENT '最終更新者の system_account.id',
  `updated_by_name` varchar(120) DEFAULT NULL COMMENT '最終更新者の表示名',
  `content_updated_at` timestamp NULL DEFAULT NULL COMMENT '最終更新日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `policy_documents_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `policy_chapters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `policy_document_id` bigint unsigned NOT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL,
  `body` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `policy_chapters_doc_sort_index` (`policy_document_id`, `sort_order`),
  CONSTRAINT `policy_chapters_policy_document_id_foreign`
    FOREIGN KEY (`policy_document_id`) REFERENCES `policy_documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `policy_revisions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `policy_document_id` bigint unsigned NOT NULL,
  `action` varchar(32) NOT NULL COMMENT 'created / updated / locked / unlocked',
  `summary` varchar(500) DEFAULT NULL,
  `snapshot` json DEFAULT NULL COMMENT '更新後スナップショット',
  `updated_by_id` bigint unsigned DEFAULT NULL,
  `updated_by_name` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `policy_revisions_doc_created_index` (`policy_document_id`, `created_at`),
  CONSTRAINT `policy_revisions_policy_document_id_foreign`
    FOREIGN KEY (`policy_document_id`) REFERENCES `policy_documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
