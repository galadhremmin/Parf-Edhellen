RENAME TABLE `translations` TO `glosses`;
RENAME TABLE `translation_groups` TO `gloss_groups`;

CREATE TABLE `translations`(
    `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
    `gloss_id` int(8) unsigned NOT NULL,
    `translation` varchar(255) BINARY COLLATE utf8_swedish_ci NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
);

INSERT INTO `translations` (`gloss_id`, `translation`)
    SELECT `id`, `translation` FROM `glosses` WHERE `is_index` = 0;

ALTER TABLE `glosses` DROP `translation`;
ALTER TABLE `glosses` CHANGE `translation_group_id` `gloss_group_id` int(11) NULL;
ALTER TABLE `glosses` CHANGE `child_translation_id` `child_gloss_id` int(8) unsigned NULL;
ALTER TABLE `glosses` CHANGE `origin_translation_id` `origin_gloss_id` int(8) unsigned NULL;

UPDATE `audit_trails` SET `entity_type` = 'gloss' 
    WHERE `entity_type` = 'translation';
UPDATE `forum_threads` SET `entity_type` = 'gloss' 
    WHERE `entity_type` = 'translation';
UPDATE `contributions` SET `type` = 'gloss' 
    WHERE `type` = 'translation';

ALTER TABLE `contributions` CHANGE `translation_id` `gloss_id` int(8) unsigned NULL;
ALTER TABLE `favourites` CHANGE `translation_id` `gloss_id` int(8) unsigned NOT NULL;
ALTER TABLE `flashcards` CHANGE `translation_group_id` `gloss_group_id` int(11) NULL;
ALTER TABLE `flashcard_results` CHANGE `translation_id` `gloss_id` int(8) unsigned NULL;
ALTER TABLE `keywords` CHANGE `translation_id` `gloss_id` int(8) unsigned NULL;
ALTER TABLE `sentence_fragments` CHANGE `translation_id` `gloss_id` int(8) unsigned NULL;

OPTIMIZE TABLE `audit_trails`, `contributions`, `favourites`, `forum_threads`, `flashcard_results`, 
    `keywords`, `sentence_fragments`;

INSERT INTO `version` VALUES (4.0, NOW());

ALTER TABLE `languages` MODIFY `id` int(11) unsigned not null AUTO_INCREMENT;
ALTER TABLE `glosses` MODIFY `language_id` int(11) unsigned NOT NULL;
ALTER TABLE `contributions` MODIFY `language_id` int(11) unsigned NOT NULL;
ALTER TABLE `glosses` MODIFY `speech_id` int(11) DEFAULT NULL;
ALTER TABLE `forum_threads` MODIFY `subject` varchar(512) NOT NULL COLLATE utf8_swedish_ci;

INSERT INTO `version` VALUES (4.1, NOW());

CREATE TABLE `mail_settings`(
    `account_id` int(5) unsigned NOT NULL,
    `forum_post_created` int(1) unsigned NOT NULL DEFAULT 1,
    `forum_contribution_approved` int(1) unsigned NOT NULL DEFAULT 1,
    `forum_contribution_rejected` int(1) unsigned NOT NULL DEFAULT 1,
    PRIMARY KEY (`account_id`)
);

CREATE TABLE `mail_setting_overrides`(
    `account_id` int(5) unsigned NOT NULL,
    `entity_id` int(11) unsigned NOT NULL,
    `entity_type` varchar(16) NOT NULL COLLATE utf8_swedish_ci,
    `disabled` int(1) unsigned NOT NULL DEFAULT 1,
    PRIMARY KEY (`account_id`, `entity_type`, `entity_id`)
);


INSERT INTO `version` VALUES (4.2, NOW());
