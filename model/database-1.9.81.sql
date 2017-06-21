
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
