DROP INDEX `WordTranslationRelation` ON `keywords`;
ALTER TABLE `keywords` ADD UNIQUE INDEX `WordTranslationRelation` (`word_id`, `translation_id`);

INSERT INTO `version` (`number`, `date`) VALUES (1.97, NOW());
