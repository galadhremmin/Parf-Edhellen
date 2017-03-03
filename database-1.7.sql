ALTER TABLE `translation` CHANGE `ParentTranslationID` `ChildTranslationID` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `version` ADD UNIQUE(`number`);

insert into `version` (`number`, `date`) values (1.7, NOW());