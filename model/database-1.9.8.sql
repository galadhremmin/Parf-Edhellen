CREATE TABLE `flashcards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` int(10) unsigned NOT NULL,
  `description` text COLLATE utf8_swedish_ci,
  `translation_group_id` int(10) unsigned NOT NULL,
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
