ALTER TABLE  `translation` ADD  `ParentTranslationID` INT UNSIGNED NULL AFTER  `Phonetic` ;
ALTER TABLE  `translation` ADD  `EldestTranslationID` INT UNSIGNED NULL AFTER  `ParentTranslationID` ;

CREATE TABLE IF NOT EXISTS `favourite` (
  `AccountID` int(5) unsigned NOT NULL,
  `TranslationID` int(8) unsigned NOT NULL,
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `translation_review` (
  `ReviewID` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `AuthorID` int(6) unsigned NOT NULL,
  `LanguageID` int(1) unsigned NOT NULL,
  `DateCreated` datetime NOT NULL,
  `Word` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `Data` text COLLATE utf8_swedish_ci NOT NULL,
  `Reviewed` datetime NULL,
  `ReviewedBy` int(6) unsigned DEFAULT NULL,
  `Approved` bit(1) DEFAULT b'0',
  `Justification` text NULL,
  PRIMARY KEY (`ReviewID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

ALTER TABLE  `auth_accounts` CHANGE  `Identity`  `Identity` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL ;
ALTER TABLE  `auth_accounts` ADD  `Email` nvarchar(255) NULL AFTER  `AccountID` ;
ALTER TABLE  `auth_accounts` ADD  `ProviderID` INT UNSIGNED NULL AFTER  `Email` ;

insert into `version` (`number`, `date`) values (1.3, NOW());
