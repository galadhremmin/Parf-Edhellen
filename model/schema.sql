-- MySQL dump 10.13  Distrib 5.7.17, for linux-glibc2.5 (x86_64)
--
-- Host: localhost    Database: elfdict_v2
-- ------------------------------------------------------
-- Server version	5.7.17

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES UTF8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account_role_rels`
--

DROP TABLE IF EXISTS `account_role_rels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_role_rels` (
  `account_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`account_id`,`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `authorization_provider_id` int(10) unsigned DEFAULT NULL,
  `identity` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `nickname` varchar(32) COLLATE utf8_swedish_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `is_configured` tinyint(1) NOT NULL DEFAULT '0',
  `tengwar` varchar(64) COLLATE utf8_swedish_ci DEFAULT NULL,
  `profile` text COLLATE utf8_swedish_ci,
  `remember_token` varchar(100) COLLATE utf8_swedish_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `has_avatar` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Identity` (`identity`)
) ENGINE=MyISAM AUTO_INCREMENT=505 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `authorization_providers`
--

DROP TABLE IF EXISTS `authorization_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authorization_providers` (
  `id` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(48) COLLATE utf8_swedish_ci NOT NULL,
  `logo_file_name` varchar(128) COLLATE utf8_swedish_ci NOT NULL,
  `name_identifier` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `favourites`
--

DROP TABLE IF EXISTS `favourites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favourites` (
  `account_id` int(5) unsigned NOT NULL,
  `translation_id` int(8) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=219 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inflections`
--

DROP TABLE IF EXISTS `inflections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inflections` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `group_name` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=140 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `keywords`
--

DROP TABLE IF EXISTS `keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keywords` (
  `keyword` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `normalized_keyword` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `reversed_normalized_keyword` varchar(255) COLLATE utf8_swedish_ci DEFAULT NULL,
  `namespace_id_deprecated` int(10) unsigned DEFAULT NULL,
  `translation_id` int(8) unsigned DEFAULT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `word_id` int(8) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sense_id` int(11) DEFAULT NULL,
  `is_sense` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `WordNamespaceRelation` (`keyword`,`namespace_id_deprecated`),
  UNIQUE KEY `WordTranslationRelation` (`keyword`,`translation_id`),
  KEY `WordID` (`word_id`)
) ENGINE=MyISAM AUTO_INCREMENT=55164 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(24) COLLATE utf8_swedish_ci NOT NULL,
  `order` int(2) NOT NULL,
  `is_invented` tinyint(1) NOT NULL DEFAULT '0',
  `tengwar` varchar(128) COLLATE utf8_swedish_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `tengwar_mode` varchar(32) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=92 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `name` varchar(32) COLLATE utf8_swedish_ci NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `senses`
--

DROP TABLE IF EXISTS `senses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `senses` (
  `id` int(11) NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_swedish_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sentence_fragment_inflection_rels`
--

DROP TABLE IF EXISTS `sentence_fragment_inflection_rels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentence_fragment_inflection_rels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sentence_fragment_id` int(11) NOT NULL,
  `inflection_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_fragment_id` (`sentence_fragment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sentence_fragments`
--

DROP TABLE IF EXISTS `sentence_fragments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentence_fragments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sentence_id` int(11) NOT NULL,
  `translation_id` int(11) DEFAULT NULL,
  `fragment` varchar(48) COLLATE utf8_swedish_ci NOT NULL,
  `tengwar` varchar(128) COLLATE utf8_swedish_ci DEFAULT NULL,
  `comments` text COLLATE utf8_swedish_ci NOT NULL,
  `order` int(11) NOT NULL,
  `speech_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `is_linebreak` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=325 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sentences`
--

DROP TABLE IF EXISTS `sentences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` longtext COLLATE utf8_swedish_ci NOT NULL,
  `language_id` int(11) NOT NULL,
  `source` varchar(64) COLLATE utf8_swedish_ci DEFAULT NULL,
  `is_neologism` tinyint(1) DEFAULT '0',
  `is_approved` tinyint(1) DEFAULT '0',
  `account_id` int(11) DEFAULT NULL,
  `long_description` longtext COLLATE utf8_swedish_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(128) COLLATE utf8_swedish_ci NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `speeches`
--

DROP TABLE IF EXISTS `speeches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `speeches` (
  `name` varchar(32) COLLATE utf8_swedish_ci NOT NULL,
  `order` int(3) unsigned NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `translation_groups`
--

DROP TABLE IF EXISTS `translation_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translation_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_swedish_ci NOT NULL,
  `external_link_format` varchar(1024) COLLATE utf8_swedish_ci DEFAULT NULL,
  `is_canon` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=72 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `translation_reviews`
--

DROP TABLE IF EXISTS `translation_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translation_reviews` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(6) unsigned NOT NULL,
  `language_id` int(1) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `word` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `payload` text COLLATE utf8_swedish_ci NOT NULL,
  `date_reviewed` datetime DEFAULT NULL,
  `reviewed_by_account_id` int(6) unsigned DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT NULL,
  `justification` text COLLATE utf8_swedish_ci,
  `translation_id` int(8) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translations` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` int(1) unsigned DEFAULT NULL,
  `translation_group_id` int(11) DEFAULT NULL,
  `translation` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `is_uncertain` tinyint(1) DEFAULT '0',
  `etymology` varchar(512) COLLATE utf8_swedish_ci DEFAULT NULL,
  `source` text COLLATE utf8_swedish_ci,
  `comments` text COLLATE utf8_swedish_ci,
  `word_id` int(8) unsigned NOT NULL,
  `is_latest` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `account_id` int(6) unsigned NOT NULL,
  `tengwar` varchar(128) COLLATE utf8_swedish_ci DEFAULT NULL,
  `phonetic` varchar(128) COLLATE utf8_swedish_ci DEFAULT NULL,
  `child_translation_id` int(10) unsigned DEFAULT NULL,
  `origin_translation_id` int(10) unsigned DEFAULT NULL,
  `is_index` tinyint(1) NOT NULL DEFAULT '0',
  `external_id` varchar(128) COLLATE utf8_swedish_ci DEFAULT NULL,
  `sense_id` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_rejected` smallint(1) NOT NULL DEFAULT '0',
  `speech_id` smallint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `WordIDIndex` (`word_id`),
  KEY `LanguageIDIndex` (`language_id`),
  KEY `ExternalID_2` (`external_id`),
  KEY `idx_senseID` (`sense_id`)
) ENGINE=MyISAM AUTO_INCREMENT=250735 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `version`
--

DROP TABLE IF EXISTS `version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `version` (
  `number` float NOT NULL,
  `date` datetime NOT NULL,
  UNIQUE KEY `number` (`number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `words`
--

DROP TABLE IF EXISTS `words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `words` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `account_id` int(5) unsigned NOT NULL,
  `normalized_word` varchar(64) COLLATE utf8_swedish_ci DEFAULT NULL,
  `reversed_normalized_word` varchar(255) COLLATE utf8_swedish_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `KeyIndex` (`normalized_word`)
) ENGINE=MyISAM AUTO_INCREMENT=60991 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-05-12 22:25:02
