ALTER TABLE  `translation` ADD  `Uncertain` BIT(1) DEFAULT b'0' AFTER  `Translation` ;

insert into `language` (`ID`, `Name`, `Order`, `Invented`, `Tengwar`)
 values (91, 'Gnomish', 91, '1', NULL);

ALTER TABLE  `translation_group` ADD  `ExternalLinkFormat` VARCHAR( 1024 ) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL AFTER  `Name` ;
UPDATE  `translation_group` SET  `ExternalLinkFormat` =  'http://eldamo.org/content/words/word-{ExternalID}.html', `Canon` = b '1' WHERE `TranslationGroupID` =70;

insert into `version` (`number`, `date`) values (1.5, NOW());