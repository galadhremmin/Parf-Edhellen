 ALTER TABLE `translation_review` ADD `TranslationID` INT( 8 ) NULL;

insert into `version` (`number`, `date`) values (1.6, NOW());