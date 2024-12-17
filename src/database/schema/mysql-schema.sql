/*!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `__latest_sentence_fragments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `__latest_sentence_fragments` (
  `id` int(11) DEFAULT NULL,
  `old_gloss_id` int(11) DEFAULT NULL,
  `new_gloss_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `account_feed_refresh_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_feed_refresh_times` (
  `account_id` bigint(20) unsigned NOT NULL,
  `feed_content_type` varchar(32) NOT NULL,
  `oldest_happened_at` timestamp NULL DEFAULT NULL,
  `newest_happened_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`account_id`,`feed_content_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `account_feeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_feeds` (
  `id` char(36) NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `happened_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `content_type` varchar(32) DEFAULT NULL,
  `content_id` bigint(20) unsigned DEFAULT NULL,
  `audit_trail_action_id` int(10) unsigned DEFAULT NULL,
  `audit_trail_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_feeds_account_id_happened_at_index` (`account_id`,`happened_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `account_merge_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_merge_requests` (
  `id` char(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `account_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`account_ids`)),
  `verification_token` varchar(255) NOT NULL,
  `requester_account_id` bigint(20) unsigned NOT NULL,
  `requester_ip` varchar(16) NOT NULL,
  `is_fulfilled` tinyint(1) NOT NULL DEFAULT 0,
  `error` text DEFAULT NULL,
  `is_error` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_merge_requests_account_id_verification_token_unique` (`account_id`,`verification_token`),
  KEY `account_merge_requests_requester_account_id_foreign` (`requester_account_id`),
  CONSTRAINT `account_merge_requests_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `account_merge_requests_requester_account_id_foreign` FOREIGN KEY (`requester_account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `account_role_rels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_role_rels` (
  `account_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`account_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(128) DEFAULT NULL,
  `authorization_provider_id` int(10) unsigned DEFAULT NULL,
  `identity` varchar(64) NOT NULL,
  `nickname` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `tengwar` varchar(64) DEFAULT NULL,
  `profile` mediumtext DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `has_avatar` smallint(1) NOT NULL DEFAULT 0,
  `feature_background_url` varchar(128) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) DEFAULT NULL,
  `is_passworded` tinyint(1) NOT NULL DEFAULT 0,
  `is_master_account` tinyint(1) NOT NULL DEFAULT 0,
  `master_account_id` bigint(20) unsigned DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Identity` (`identity`),
  KEY `accounts_master_account_id_foreign` (`master_account_id`),
  CONSTRAINT `accounts_master_account_id_foreign` FOREIGN KEY (`master_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audit_trails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_trails` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `entity_type` varchar(16) NOT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `action_id` int(10) unsigned NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_admin` int(1) DEFAULT 0,
  `entity_name` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `authorization_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authorization_providers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(48) NOT NULL,
  `logo_file_name` varchar(128) NOT NULL,
  `name_identifier` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `language_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `word` varchar(128) DEFAULT NULL,
  `payload` mediumtext NOT NULL,
  `date_reviewed` datetime DEFAULT NULL,
  `reviewed_by_account_id` bigint(20) unsigned DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT NULL,
  `justification` mediumtext DEFAULT NULL,
  `gloss_id` bigint(20) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `keywords` mediumtext DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `sense` varchar(128) DEFAULT NULL,
  `sentence_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(16) NOT NULL,
  `approved_as_entity_id` bigint(20) unsigned DEFAULT NULL,
  `dependent_on_contribution_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `flashcard_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flashcard_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `flashcard_id` int(10) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `gloss_id` bigint(20) unsigned DEFAULT NULL,
  `expected` varchar(255) NOT NULL,
  `actual` varchar(255) NOT NULL,
  `correct` int(1) unsigned NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `flashcards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flashcards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` bigint(20) unsigned NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `gloss_group_id` bigint(20) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `forum_discussions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_discussions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `forum_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text NOT NULL,
  `role` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category` varchar(128) DEFAULT NULL,
  `is_readonly` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `forum_post_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_post_likes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `forum_post_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `AccountForumPost` (`forum_post_id`,`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `forum_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_forum_post_id` bigint(20) unsigned DEFAULT NULL,
  `number_of_likes` int(10) unsigned NOT NULL DEFAULT 0,
  `account_id` bigint(20) unsigned NOT NULL,
  `content` mediumtext DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `forum_thread_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `forum_threads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_threads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(16) DEFAULT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `subject` varchar(512) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `account_id` bigint(20) unsigned DEFAULT NULL,
  `number_of_posts` int(10) unsigned DEFAULT 0,
  `number_of_likes` int(10) unsigned DEFAULT 0,
  `normalized_subject` varchar(512) DEFAULT NULL,
  `is_sticky` smallint(6) NOT NULL DEFAULT 0,
  `forum_group_id` bigint(20) unsigned NOT NULL,
  `is_empty` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_thread_entity` (`entity_id`,`entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `game_word_finder_gloss_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_word_finder_gloss_groups` (
  `gloss_group_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`gloss_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `game_word_finder_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_word_finder_languages` (
  `language_id` int(10) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `gloss_detail_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gloss_detail_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gloss_version_id` bigint(20) unsigned NOT NULL,
  `order` int(10) unsigned NOT NULL,
  `category` varchar(128) NOT NULL,
  `text` mediumtext NOT NULL,
  `type` varchar(16) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gloss_detail_versions_gloss_version_id_foreign` (`gloss_version_id`),
  CONSTRAINT `gloss_detail_versions_gloss_version_id_foreign` FOREIGN KEY (`gloss_version_id`) REFERENCES `gloss_versions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `gloss_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gloss_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gloss_id` bigint(20) unsigned NOT NULL,
  `order` int(10) unsigned NOT NULL,
  `category` varchar(128) NOT NULL,
  `text` mediumtext NOT NULL,
  `type` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_glosses` (`gloss_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `gloss_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gloss_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `external_link_format` varchar(1024) DEFAULT NULL,
  `is_canon` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_old` int(1) DEFAULT 0,
  `label` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `gloss_inflections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gloss_inflections` (
  `inflection_group_uuid` char(36) NOT NULL,
  `gloss_id` bigint(20) unsigned NOT NULL,
  `language_id` bigint(20) unsigned NOT NULL,
  `inflection_id` bigint(20) unsigned DEFAULT NULL,
  `speech_id` bigint(20) unsigned DEFAULT NULL,
  `account_id` bigint(20) unsigned DEFAULT NULL,
  `sentence_id` bigint(20) unsigned DEFAULT NULL,
  `sentence_fragment_id` bigint(20) unsigned DEFAULT NULL,
  `is_neologism` tinyint(1) NOT NULL DEFAULT 0,
  `is_rejected` tinyint(1) NOT NULL DEFAULT 0,
  `source` varchar(196) DEFAULT NULL,
  `word` varchar(196) NOT NULL,
  `order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `gloss_inflections_gloss_id_index` (`gloss_id`),
  KEY `gloss_inflections_sentence_fragment_id_index` (`sentence_fragment_id`),
  KEY `gloss_inflections_inflection_group_uuid_index` (`inflection_group_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `gloss_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gloss_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version_change_flags` int(10) unsigned DEFAULT NULL,
  `gloss_id` bigint(20) unsigned NOT NULL,
  `language_id` bigint(20) unsigned NOT NULL,
  `word_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `sense_id` bigint(20) unsigned NOT NULL,
  `gloss_group_id` bigint(20) unsigned DEFAULT NULL,
  `speech_id` bigint(20) unsigned DEFAULT NULL,
  `is_uncertain` tinyint(1) DEFAULT 0,
  `is_rejected` tinyint(1) DEFAULT 0,
  `has_details` tinyint(1) NOT NULL DEFAULT 0,
  `etymology` varchar(512) DEFAULT NULL,
  `tengwar` varchar(128) DEFAULT NULL,
  `source` mediumtext DEFAULT NULL,
  `comments` mediumtext DEFAULT NULL,
  `external_id` varchar(128) DEFAULT NULL,
  `label` varchar(16) DEFAULT NULL,
  `__migration_gloss_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gloss_versions_gloss_id_foreign` (`gloss_id`),
  KEY `gloss_versions_language_id_foreign` (`language_id`),
  KEY `gloss_versions_word_id_foreign` (`word_id`),
  KEY `gloss_versions_account_id_foreign` (`account_id`),
  KEY `gloss_versions_sense_id_foreign` (`sense_id`),
  KEY `gloss_versions_gloss_group_id_foreign` (`gloss_group_id`),
  KEY `gloss_versions_speech_id_foreign` (`speech_id`),
  KEY `gloss_versions_created_at_gloss_id_index` (`created_at`,`gloss_id`),
  KEY `gloss_versions___migration_gloss_id_index` (`__migration_gloss_id`),
  CONSTRAINT `gloss_versions_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  CONSTRAINT `gloss_versions_gloss_group_id_foreign` FOREIGN KEY (`gloss_group_id`) REFERENCES `gloss_groups` (`id`),
  CONSTRAINT `gloss_versions_gloss_id_foreign` FOREIGN KEY (`gloss_id`) REFERENCES `glosses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gloss_versions_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `gloss_versions_sense_id_foreign` FOREIGN KEY (`sense_id`) REFERENCES `senses` (`id`),
  CONSTRAINT `gloss_versions_speech_id_foreign` FOREIGN KEY (`speech_id`) REFERENCES `speeches` (`id`),
  CONSTRAINT `gloss_versions_word_id_foreign` FOREIGN KEY (`word_id`) REFERENCES `words` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `glosses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glosses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` bigint(20) unsigned NOT NULL,
  `gloss_group_id` bigint(20) unsigned DEFAULT NULL,
  `is_uncertain` tinyint(1) DEFAULT 0,
  `etymology` varchar(512) DEFAULT NULL,
  `source` mediumtext DEFAULT NULL,
  `comments` mediumtext DEFAULT NULL,
  `word_id` bigint(20) unsigned NOT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `account_id` bigint(20) unsigned NOT NULL,
  `tengwar` varchar(128) DEFAULT NULL,
  `external_id` varchar(128) DEFAULT NULL,
  `sense_id` bigint(20) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_rejected` smallint(1) NOT NULL DEFAULT 0,
  `speech_id` bigint(20) unsigned DEFAULT NULL,
  `has_details` smallint(6) NOT NULL DEFAULT 0,
  `label` varchar(16) DEFAULT NULL,
  `latest_gloss_version_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `WordIDIndex` (`word_id`),
  KEY `LanguageIDIndex` (`language_id`),
  KEY `ExternalID_2` (`external_id`),
  KEY `idx_senseID` (`sense_id`),
  KEY `TranslationsGroupId` (`gloss_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inflections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inflections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `group_name` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_restricted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keywords` (
  `keyword` varchar(255) NOT NULL,
  `normalized_keyword` varchar(255) NOT NULL,
  `normalized_keyword_length` int(3) DEFAULT 0,
  `reversed_normalized_keyword` varchar(255) DEFAULT NULL,
  `reversed_normalized_keyword_length` int(3) DEFAULT 0,
  `namespace_id_deprecated` int(10) unsigned DEFAULT NULL,
  `gloss_id` bigint(20) unsigned DEFAULT NULL,
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `word_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `sense_id` bigint(20) unsigned DEFAULT NULL,
  `is_sense` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT NULL,
  `normalized_keyword_unaccented` varchar(255) DEFAULT NULL,
  `normalized_keyword_unaccented_length` int(3) DEFAULT 0,
  `reversed_normalized_keyword_unaccented` varchar(255) DEFAULT NULL,
  `reversed_normalized_keyword_unaccented_length` int(3) DEFAULT 0,
  `is_old` int(1) DEFAULT 0,
  `sentence_fragment_id` bigint(20) unsigned DEFAULT NULL,
  `word` varchar(191) DEFAULT NULL,
  `keyword_language_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `WordGlossFragmentRelation` (`word_id`,`gloss_id`,`sentence_fragment_id`),
  KEY `KeywordsTranslationId` (`gloss_id`),
  KEY `KeywordsSenseId` (`sense_id`),
  KEY `keywords_keyword_language_id_foreign` (`keyword_language_id`),
  CONSTRAINT `keywords_keyword_language_id_foreign` FOREIGN KEY (`keyword_language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(24) NOT NULL,
  `order` int(2) NOT NULL,
  `is_invented` tinyint(1) NOT NULL DEFAULT 0,
  `tengwar` varchar(128) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `tengwar_mode` varchar(32) DEFAULT NULL,
  `category` varchar(64) DEFAULT NULL,
  `is_unusual` smallint(1) NOT NULL DEFAULT 0,
  `short_name` varchar(6) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mail_setting_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_setting_overrides` (
  `account_id` bigint(20) unsigned NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  `entity_type` varchar(16) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `disabled` int(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`account_id`,`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mail_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_settings` (
  `account_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `forum_post_created` int(1) unsigned NOT NULL DEFAULT 1,
  `forum_contribution_approved` int(1) unsigned NOT NULL DEFAULT 1,
  `forum_contribution_rejected` int(1) unsigned NOT NULL DEFAULT 1,
  `forum_posted_on_profile` int(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `name` varchar(32) NOT NULL,
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `search_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_keywords` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `search_group` int(10) unsigned NOT NULL,
  `keyword` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `normalized_keyword` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `normalized_keyword_reversed` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `normalized_keyword_unaccented` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `normalized_keyword_reversed_unaccented` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `keyword_length` int(11) NOT NULL,
  `normalized_keyword_length` int(11) NOT NULL,
  `normalized_keyword_reversed_length` int(11) NOT NULL,
  `normalized_keyword_unaccented_length` int(11) NOT NULL,
  `normalized_keyword_reversed_unaccented_length` int(11) NOT NULL,
  `entity_name` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  `word` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `word_id` bigint(20) unsigned NOT NULL,
  `language_id` bigint(20) unsigned DEFAULT NULL,
  `speech_id` bigint(20) unsigned DEFAULT NULL,
  `gloss_group_id` bigint(20) unsigned DEFAULT NULL,
  `is_old` tinyint(1) DEFAULT NULL,
  `keyword_language_id` bigint(20) unsigned DEFAULT NULL,
  `is_keyword_language_invented` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `search_keywords_entity_name_entity_id_index` (`entity_name`,`entity_id`),
  KEY `search_keywords_normalized_keyword_unaccented_index` (`normalized_keyword_unaccented`),
  KEY `search_keywords_normalized_keyword_reversed_unaccented_index` (`normalized_keyword_reversed_unaccented`),
  KEY `search_keywords_keyword_language_id_foreign` (`keyword_language_id`),
  CONSTRAINT `search_keywords_keyword_language_id_foreign` FOREIGN KEY (`keyword_language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `senses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `senses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` mediumtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sentence_fragment_inflection_rels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentence_fragment_inflection_rels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sentence_fragment_id` bigint(20) unsigned NOT NULL,
  `inflection_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_fragment_id` (`sentence_fragment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sentence_fragments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentence_fragments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sentence_id` bigint(20) unsigned NOT NULL,
  `gloss_id` bigint(20) unsigned DEFAULT NULL,
  `fragment` varchar(48) NOT NULL,
  `tengwar` varchar(128) DEFAULT NULL,
  `comments` mediumtext NOT NULL,
  `order` int(11) NOT NULL,
  `speech_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_linebreak` smallint(1) NOT NULL DEFAULT 0,
  `type` int(3) NOT NULL DEFAULT 0,
  `sentence_number` int(11) DEFAULT NULL,
  `paragraph_number` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sentence_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentence_translations` (
  `sentence_id` bigint(20) unsigned NOT NULL,
  `sentence_number` int(11) NOT NULL,
  `translation` text NOT NULL,
  `paragraph_number` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sentence_id`,`sentence_number`,`paragraph_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sentences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` longtext NOT NULL,
  `language_id` bigint(20) unsigned NOT NULL,
  `source` varchar(64) DEFAULT NULL,
  `is_neologism` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 0,
  `account_id` bigint(20) unsigned DEFAULT NULL,
  `long_description` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `name` varchar(128) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `speeches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `speeches` (
  `name` varchar(32) NOT NULL,
  `order` int(3) unsigned NOT NULL DEFAULT 0,
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_verb` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_errors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message` varchar(1024) NOT NULL,
  `url` varchar(1024) NOT NULL,
  `error` mediumtext DEFAULT NULL,
  `account_id` bigint(20) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varbinary(16) DEFAULT NULL,
  `line` int(6) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `is_common` int(1) DEFAULT 0,
  `category` varchar(64) DEFAULT NULL,
  `user_agent` varchar(196) DEFAULT NULL,
  `session_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_errors_category_index` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `translation_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translation_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gloss_version_id` bigint(20) unsigned NOT NULL,
  `translation` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `translation_versions_gloss_version_id_foreign` (`gloss_version_id`),
  CONSTRAINT `translation_versions_gloss_version_id_foreign` FOREIGN KEY (`gloss_version_id`) REFERENCES `gloss_versions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gloss_id` bigint(20) unsigned NOT NULL,
  `translation` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `translations_gloss_id_index` (`gloss_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `version` (
  `number` float NOT NULL,
  `date` datetime NOT NULL,
  UNIQUE KEY `number` (`number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `words` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(250) NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `normalized_word` varchar(250) NOT NULL,
  `reversed_normalized_word` varchar(250) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `KeyIndex` (`normalized_word`),
  KEY `WordsWordIndex` (`word`),
  KEY `WordsReversedNormalizedWordIndex` (`reversed_normalized_word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*!999999\- enable the sandbox mode */ 
