DROP TABLE IF EXISTS `forum_posts`;
CREATE TABLE `forum_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic` varchar(16) NOT NULL COLLATE utf8_swedish_ci,
  `account_id` int(5) unsigned NOT NULL,
  `content` text COLLATE utf8_swedish_ci,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Topic` (`topic`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

ALTER TABLE `account_role_rels` MODIFY `account_id` int(5) unsigned NOT NULL;
ALTER TABLE `sentences` MODIFY `account_id` int(5) unsigned NOT NULL;
ALTER TABLE `translation_reviews` MODIFY `account_id` int(5) unsigned NOT NULL;
ALTER TABLE `translations` MODIFY `account_id` int(5) unsigned NOT NULL;

ALTER TABLE `translation_reviews` MODIFY `translation_id` int(8) unsigned NULL;
ALTER TABLE `sentence_fragments` MODIFY `translation_id` int(8) unsigned NULL;
ALTER TABLE `translations` MODIFY `child_translation_id` int(8) unsigned NULL;
ALTER TABLE `translations` MODIFY `origin_translation_id` int(8) unsigned NULL;
