ALTER TABLE `sentence_fragments` ADD `type` int(3) NOT NULL DEFAULT 0;

UPDATE `sentence_fragments` SET `type` = 10 -- linebreak
  WHERE `is_linebreak` = 1;
UPDATE `sentence_fragments` SET `type` = 31 -- unit separator = interpunctuation
  WHERE `is_linebreak` = 0 AND `fragment` in( '!', '.', ',', '?' );
UPDATE `sentence_fragments` SET `type` = 45 -- hyphen = word connection
  WHERE `is_linebreak` = 0 AND `fragment` in( '-', '·' );

INSERT INTO `version` (`number`, `date`) VALUES (1.993, NOW());
