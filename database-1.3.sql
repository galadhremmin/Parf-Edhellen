ALTER TABLE  `translation` ADD  `ParentTranslationID` INT UNSIGNED NULL AFTER  `Phonetic` ;

insert into `version` (`number`, `date`) values (1.3, NOW());
