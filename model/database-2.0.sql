
CREATE INDEX `WordsWordIndex` ON `words` (`word`);
CREATE INDEX `WordsReversedNormalizedWordIndex` ON `words` (`reversed_normalized_word`); 

DROP INDEX `WordNamespaceRelation` ON `keywords`;
DROP INDEX `WordID` ON `keywords`;
CREATE INDEX `KeywordsNormalizedKeyword` ON `keywords` (`normalized_keyword`);
CREATE INDEX `KeywordsReversedNormalizedKeyword` ON `keywords` (`reversed_normalized_keyword`);
CREATE INDEX `KeywordsTranslationId` ON `keywords` (`translation_id`);
CREATE INDEX `KeywordsSenseId` ON `keywords` (`sense_id`);

CREATE INDEX `TranslationsIsLatest` ON `translations` (`is_latest`, `is_deleted`);
CREATE INDEX `TranslationsGroupId` ON `translations` (`translation_group_id`);

-- WIP!

ALTER TABLE `translation_reviews` ADD `sentence_id` int(11) NULL;
ALTER TABLE `translation_reviews` ADD `type` varchar(16) NOT NULL;
UPDATE `translation_reviews` SET `type` = 'translation';

RENAME TABLE `translation_reviews` TO `contributions`;

ALTER TABLE `forum_contexts` ADD `is_elevated` int(1) DEFAULT 0;
INSERT INTO `forum_contexts` (`id`, `name`, `is_elevated`) VALUES (5, 'contribution', 1); 

INSERT INTO `version` (`number`, `date`) VALUES (2.0, NOW());

ALTER TABLE `forum_contexts` ADD `friendly_name` varchar(64) NOT NULL;
ALTER TABLE `forum_contexts` ADD `icon` varchar(32) NOT NULL;
ALTER TABLE `forum_posts` ADD `context_name` varchar(64) NOT NULL;
ALTER TABLE `forum_posts` ADD `entity_name` varchar(64) NOT NULL;

UPDATE `forum_contexts` SET `friendly_name` = 'Forum', `icon` = 'envelope' where `id` = 1;
UPDATE `forum_contexts` SET `friendly_name` = 'Gloss', `icon` = 'book' where `id` = 2;
UPDATE `forum_contexts` SET `friendly_name` = 'Phrase', `icon` = 'align-justify' where `id` = 3;
UPDATE `forum_contexts` SET `friendly_name` = 'Author', `icon` = 'user' where `id` = 4;
UPDATE `forum_contexts` SET `friendly_name` = 'Contribution', `icon` = 'plus' where `id` = 5;

UPDATE `forum_posts` fp
    INNER JOIN `forum_contexts` fc ON fp.`forum_context_id` = fc.`id`
    LEFT JOIN `translations` t on fp.`entity_id` = t.`id`
    LEFT JOIN `words` w on t.`word_id` = w.`id`
    LEFT JOIN `sentences` s on fp.`entity_id` = s.`id`
    LEFT JOIN `accounts` a on fp.`entity_id` = a.`id`
    LEFT JOIN `contributions` c on fp.`entity_id` = c.`id`
SET `context_name` = fc.`friendly_name`,
    `entity_name` = coalesce(w.word, s.name, a.nickname, c.word); 

INSERT INTO `version` (`number`, `date`) VALUES (2.1, NOW());

CREATE TABLE `forum_threads` (
    `id` int(16) unsigned NOT NULL AUTO_INCREMENT,
    `entity_type` varchar(16) NULL COLLATE utf8_swedish_ci,
    `entity_id` int(10) unsigned NULL,
    `subject` varchar(128) NOT NULL COLLATE utf8_swedish_ci,
    `roles` varchar(192) NULL COLLATE utf8_swedish_ci,
    `updated_at` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

ALTER TABLE `forum_posts`
    ADD `forum_thread_id` int(16) NOT NULL;

INSERT INTO `forum_threads` (`entity_type`, `entity_id`, `subject`, `roles`)
    SELECT DISTINCT 'translation', fp.`entity_id`, concat('Gloss “', fp.`entity_name`, '”'), NULL
        FROM `forum_posts` fp 
        WHERE fp.`forum_context_id` = 2;

INSERT INTO `forum_threads` (`entity_type`, `entity_id`, `subject`, `roles`)
    SELECT DISTINCT 'sentence', fp.`entity_id`, concat('Phrase “', fp.`entity_name`, '”'), NULL
        FROM `forum_posts` fp 
        WHERE fp.`forum_context_id` = 3;
        
INSERT INTO `forum_threads` (`entity_type`, `entity_id`, `subject`, `roles`)
    SELECT DISTINCT 'account', fp.`entity_id`, concat('Account “', fp.`entity_name`, '”'), NULL
        FROM `forum_posts` fp 
        WHERE fp.`forum_context_id` = 4;
        
INSERT INTO `forum_threads` (`entity_type`, `entity_id`, `subject`, `roles`)
    SELECT DISTINCT 'contribution', fp.`entity_id`, concat('Contribution “', c.`word`, '” by ', a.nickname), 'Administrators'
        FROM `forum_posts` fp 
        INNER JOIN `contributions` c on c.`id` = fp.`entity_id`
        INNER JOIN `accounts` a on a.`id` = fp.`account_id`
        WHERE fp.`forum_context_id` = 5;

UPDATE `forum_posts` fp
    INNER JOIN `forum_threads` ft on ft.`entity_type` = 'translation' AND ft.`entity_id` = fp.`entity_id`
    SET fp.`forum_thread_id` = ft.`id`
    WHERE ft.`entity_type` = 'translation'
    WHERE fp.`forum_context_id` = 2;

UPDATE `forum_posts` fp
    INNER JOIN `forum_threads` ft on ft.`entity_type` = 'sentence' AND ft.`entity_id` = fp.`entity_id`
    SET fp.`forum_thread_id` = ft.`id`
    WHERE fp.`forum_context_id` = 3;

UPDATE `forum_posts` fp
    INNER JOIN `forum_threads` ft on ft.`entity_type` = 'account' AND ft.`entity_id` = fp.`entity_id`
    SET fp.`forum_thread_id` = ft.`id`
    WHERE fp.`forum_context_id` = 4;

UPDATE `forum_posts` fp
    INNER JOIN `forum_threads` ft on ft.`entity_type` = 'contribution' AND ft.`entity_id` = fp.`entity_id`
    SET fp.`forum_thread_id` = ft.`id`
    WHERE fp.`forum_context_id` = 5;

DROP TABLE `forum_contexts`;
ALTER TABLE `forum_posts` DROP `forum_context_id`;
ALTER TABLE `forum_posts` DROP `context_name`;
ALTER TABLE `forum_posts` DROP `entity_name`;
ALTER TABLE `forum_posts` DROP `entity_id`;

INSERT INTO `authorization_providers` (`name`, `logo_file_name`, `name_identifier`) VALUES ('Microsoft', 'microsoft.png', 'live');

INSERT INTO `version` (`number`, `date`) VALUES (2.2, NOW());
