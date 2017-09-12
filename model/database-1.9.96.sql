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
