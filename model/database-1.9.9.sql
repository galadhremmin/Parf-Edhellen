DROP TABLE IF EXISTS `forum_contexts`;
CREATE TABLE `forum_contexts` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(16) NOT NULL COLLATE utf8_swedish_ci,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

INSERT INTO `forum_contexts` (`id`, `name`)
  VALUES (1, 'Forum');
INSERT INTO `forum_contexts` (`id`, `name`)
  VALUES (2, 'Translations');
INSERT INTO `forum_contexts` (`id`, `name`)
  VALUES (3, 'Sentences');

DROP TABLE IF EXISTS `forum_posts`;
CREATE TABLE `forum_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `context_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `parent_form_post_id` int(10) unsigned NOT NULL,
  `number_of_likes` int(10) unsigned NOT NULL DEFAULT 0,
  `account_id` int(5) unsigned NOT NULL,
  `content` text COLLATE utf8_swedish_ci,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `ContextEntity` (`context_id`, `entity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

DROP TABLE IF EXISTS `forum_post_likes`;
CREATE TABLE `forum_post_likes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forum_post_id` int(10) unsigned NOT NULL,
  `account_id` int(5) unsigned NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `AccountForumPost` (`forum_post_id`, `account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

ALTER TABLE `account_role_rels` MODIFY `account_id` int(5) unsigned NOT NULL;
ALTER TABLE `sentences` MODIFY `account_id` int(5) unsigned NOT NULL;
ALTER TABLE `translation_reviews` MODIFY `account_id` int(5) unsigned NOT NULL;
ALTER TABLE `translations` MODIFY `account_id` int(5) unsigned NOT NULL;

ALTER TABLE `translation_reviews` MODIFY `translation_id` int(8) unsigned NULL;
ALTER TABLE `sentence_fragments` MODIFY `translation_id` int(8) unsigned NULL;
ALTER TABLE `translations` MODIFY `child_translation_id` int(8) unsigned NULL;
ALTER TABLE `translations` MODIFY `origin_translation_id` int(8) unsigned NULL;

ALTER TABLE `keywords` ADD `normalized_keyword_unaccented` varchar(255) NULL;
ALTER TABLE `keywords` ADD `reversed_normalized_keyword_unaccented` varchar(255) NULL;

CREATE INDEX `NormalizedKeywordUnaccentedIndex` ON `keywords` (`normalized_keyword_unaccented`);
CREATE INDEX `ReversedNormalizedKeywordUnaccentedIndex` ON `keywords` (`reversed_normalized_keyword_unaccented`);

INSERT INTO `version` (`number`, `date`) VALUES (1.99, NOW());
