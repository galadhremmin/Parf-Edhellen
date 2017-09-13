ALTER TABLE `languages` ADD `category` varchar(64) NULL;
ALTER TABLE `languages` ADD `is_unusual` smallint(1) NOT NULL DEFAULT 0;
ALTER TABLE `words` MODIFY `word` varchar(128) NOT NULL;
ALTER TABLE `words` MODIFY `normalized_word` varchar(128) NOT NULL;
ALTER TABLE `words` MODIFY `reversed_normalized_word` varchar(128) NOT NULL;

UPDATE `languages` SET `category` = 'Late Period (1950-1973)' 
    WHERE `id` IN(1, 2, 5, 6, 7, 8, 9, 10, 11, 12, 20, 25, 30, 35,
        40, 45, 50, 55, 60, 65, 70, 71, 75, 80);

UPDATE `languages` SET `category` = 'Middle Period (1930-1950)' 
    WHERE `id` IN(4, 85, 90);

UPDATE `languages` SET `category` = 'Early Period (1910-1930)' 
    WHERE `id` IN(91);

UPDATE `languages` SET `category` = 'Real-world languages' 
    WHERE `id` IN(3);    

INSERT INTO `languages` (`name`, `order`, `is_invented`, `tengwar`, `created_at`, `category`)
    VALUES ('Ossriandric', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Early Ilkorin', 0, 1, NULL, NOW(), 'Early Period (1910-1930)'),
        ('Early Noldorin', 0, 1, NULL, NOW(), 'Early Period (1910-1930)'),
        ('Old Noldorin', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Middle Primitive Elvish', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Taliska', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Lemberin', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Middle Telerin', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Solosimpi', 0, 1, NULL, NOW(), 'Early Period (1910-1930)'),
        ('Early Primitive Elvish', 0, 1, NULL, NOW(), 'Early Period (1910-1930)');

UPDATE `languages` SET `order` = 10 WHERE `category` = 'Real-world languages';
UPDATE `languages` SET `order` = 20 WHERE `category` = 'Early Period (1910-1930)';
UPDATE `languages` SET `order` = 30 WHERE `category` = 'Middle Period (1930-1950)';
UPDATE `languages` SET `order` = 40 WHERE `category` = 'Late Period (1950-1973)';

UPDATE `languages` SET `order` = 49 WHERE `name` = 'Quenya';
UPDATE `languages` SET `order` = 48 WHERE `name` = 'Sindarin';
UPDATE `languages` SET `order` = 41 WHERE `name` = 'Telerin';

UPDATE `languages` SET `is_unusual` = 1 WHERE `id` NOT IN(1,2,4,5,6,7,9,10,11);

INSERT INTO `version` (`number`, `date`) VALUES (1.96, NOW());

DROP INDEX `WordTranslationRelation` ON `keywords`;
ALTER TABLE `keywords` ADD UNIQUE INDEX `WordTranslationRelation` (`word_id`, `translation_id`);

INSERT INTO `version` (`number`, `date`) VALUES (1.97, NOW());

CREATE TABLE `flashcards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` int(10) unsigned NOT NULL,
  `description` text COLLATE utf8_swedish_ci,
  `translation_group_id` int(10) unsigned NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

CREATE TABLE `flashcard_results`(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `flashcard_id` int(10) unsigned NOT NULL,
  `account_id` int(6) unsigned NOT NULL,
  `translation_id` int(8) DEFAULT NULL,
  `expected` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `actual` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `correct` int(1) unsigned NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

INSERT INTO `flashcards` (`language_id`, `translation_group_id`, `description`)
  SELECT l.id, tg.id,
    'Quenya, also called High-elven, is the language of the Quendi, the elves of the West. It was brought to Middle Earth during the exile of the Noldor.' 
  FROM `languages` AS l
    INNER JOIN `translation_groups` AS tg ON tg.`name` = 'Eldamo'
  WHERE l.`name` = 'Quenya';

INSERT INTO `flashcards` (`language_id`, `translation_group_id`, `description`)
  SELECT l.id, tg.id,
    'Sindarin is the main Eldarin tongue in Middle-earth, the living vernacular of the Grey-elves.' 
  FROM `languages` AS l
    INNER JOIN `translation_groups` AS tg ON tg.`name` = 'Eldamo'
  WHERE l.`name` = 'Sindarin';

INSERT INTO `version` (`number`, `date`) VALUES (1.98, NOW());


INSERT INTO `flashcards` (`language_id`, `translation_group_id`, `description`)
  SELECT l.id, tg.id,
    'Adûnaic was the official language of the Númenoreans, the kingly denizens of Númenor.' 
  FROM `languages` AS l
    INNER JOIN `translation_groups` AS tg ON tg.`name` = 'Eldamo'
  WHERE l.`name` like 'Ad%naic';

INSERT INTO `flashcards` (`language_id`, `translation_group_id`, `description`)
  SELECT l.id, tg.id,
    'Primitive Elvish is the origin of all Elvish languages. It is believed to have been formed during the Elves\' stay by Cuiviénen, the Water of Awakening.' 
  FROM `languages` AS l
    INNER JOIN `translation_groups` AS tg ON tg.`name` = 'Eldamo'
  WHERE l.`name` = 'Primitive Elvish';

INSERT INTO `version` (`number`, `date`) VALUES (1.981, NOW());

DROP TABLE IF EXISTS `forum_contexts`;
CREATE TABLE `forum_contexts` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(16) NOT NULL COLLATE utf8_swedish_ci,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

INSERT INTO `forum_contexts` (`id`, `name`)
  VALUES (1, 'forum');
INSERT INTO `forum_contexts` (`id`, `name`)
  VALUES (2, 'translation');
INSERT INTO `forum_contexts` (`id`, `name`)
  VALUES (3, 'sentence');
INSERT INTO `forum_contexts` (`id`, `name`)
  VALUES (4, 'account');

DROP TABLE IF EXISTS `forum_posts`;
CREATE TABLE `forum_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forum_context_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `parent_forum_post_id` int(10) unsigned NULL,
  `number_of_likes` int(10) unsigned NOT NULL DEFAULT 0,
  `account_id` int(5) unsigned NOT NULL,
  `content` text COLLATE utf8_swedish_ci,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `ContextEntity` (`forum_context_id`, `entity_id`)
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

DROP TABLE IF EXISTS `audit_trails`;
CREATE TABLE `audit_trails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(5) unsigned NOT NULL,
  `entity_type` varchar(16) NOT NULL COLLATE utf8_swedish_ci ,
  `entity_id` int(10) unsigned NULL,
  `action_id` int(10) unsigned NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

ALTER TABLE `account_role_rels` MODIFY `account_id` int(5) unsigned NOT NULL;
ALTER TABLE `sentences` MODIFY `account_id` int(5) unsigned NULL;
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

DROP TABLE IF EXISTS `system_errors`;
CREATE TABLE `system_errors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `message` varchar(128) NOT NULL COLLATE utf8_swedish_ci,
  `url` varchar(1024) NOT NULL COLLATE utf8_swedish_ci,
  `error` text NULL COLLATE utf8_swedish_ci,
  `account_id` int(5) unsigned NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

INSERT INTO `version` (`number`, `date`) VALUES (1.991, NOW());

ALTER TABLE `system_errors` ADD `ip` VARBINARY(16) NULL;
ALTER TABLE `system_errors` ADD `line` int(6) NULL;
ALTER TABLE `system_errors` ADD `file` varchar(64) NULL;
ALTER TABLE `system_errors` MODIFY `message` varchar(1024) NOT NULL;

INSERT INTO `version` (`number`, `date`) VALUES (1.992, NOW());

ALTER TABLE `sentence_fragments` ADD `type` int(3) NOT NULL DEFAULT 0;

UPDATE `sentence_fragments` SET `type` = 10 -- linebreak
  WHERE `is_linebreak` = 1;
UPDATE `sentence_fragments` SET `type` = 31 -- unit separator = interpunctuation
  WHERE `is_linebreak` = 0 AND `fragment` in( '!', '.', ',', '?' );
UPDATE `sentence_fragments` SET `type` = 45 -- hyphen = word connection
  WHERE `is_linebreak` = 0 AND `fragment` in( '-', '·' );

INSERT INTO `version` (`number`, `date`) VALUES (1.993, NOW());

ALTER TABLE `translation_reviews` ADD `keywords` text NULL COLLATE utf8_swedish_ci;
ALTER TABLE `translation_reviews` ADD `notes` text NULL COLLATE utf8_swedish_ci;
ALTER TABLE `translation_reviews` ADD `sense` varchar(128) NULL COLLATE utf8_swedish_ci;

INSERT INTO `version` (`number`, `date`) VALUES (1.994, NOW());

ALTER TABLE `audit_trails` ADD `is_admin` int(1) DEFAULT 0;
ALTER TABLE `system_errors` ADD `is_common` int(1) DEFAULT 0;

UPDATE `system_errors` SET `is_common` = 1 
    WHERE `message` LIKE 'Illuminate\\\\Auth\\\\AuthenticationException%' OR
          `message` LIKE 'Illuminate\\\\Session\\\\TokenMismatchException%' OR
          `message` LIKE 'Symfony\\\\Component\\\\HttpKernel\\\\Exception\\\\NotFoundHttpException%';


INSERT INTO `version` (`number`, `date`) VALUES (1.995, NOW());

ALTER TABLE `translation_groups` ADD `is_old` INT(1) DEFAULT 0;
UPDATE `translation_groups` 
    SET `is_old` = 1
    WHERE `id` IN (10, 20, 30);

ALTER TABLE `keywords` ADD `is_old` INT(1) DEFAULT 0;
ALTER TABLE `keywords` ADD `normalized_keyword_length` INT(3) DEFAULT 0 AFTER `normalized_keyword`;
ALTER TABLE `keywords` ADD `reversed_normalized_keyword_length` INT(3) DEFAULT 0 AFTER `reversed_normalized_keyword`;
ALTER TABLE `keywords` ADD `normalized_keyword_unaccented_length` INT(3) DEFAULT 0 AFTER `normalized_keyword_unaccented`;
ALTER TABLE `keywords` ADD `reversed_normalized_keyword_unaccented_length` INT(3) DEFAULT 0 AFTER `reversed_normalized_keyword_unaccented`;

UPDATE `keywords` 
    SET `normalized_keyword_length` = CHAR_LENGTH(`normalized_keyword`),
        `reversed_normalized_keyword_length` = CHAR_LENGTH(`reversed_normalized_keyword`),
        `normalized_keyword_unaccented_length` = CHAR_LENGTH(`normalized_keyword_unaccented`),
        `reversed_normalized_keyword_unaccented_length` = CHAR_LENGTH(`reversed_normalized_keyword_unaccented`);

UPDATE `keywords` k
    INNER JOIN `translations` t ON t.`id` = k.`translation_id`
    INNER JOIN `translation_groups` tg ON tg.`id` = t.`translation_group_id`
    SET k.`is_old` = tg.`is_old`;

INSERT INTO `version` (`number`, `date`) VALUES (1.996, NOW());
