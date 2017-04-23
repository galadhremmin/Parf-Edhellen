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
  `Approved` bit(1) NULL,
  `Justification` text NULL,
  PRIMARY KEY (`ReviewID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

ALTER TABLE  `auth_accounts` CHANGE  `Identity`  `Identity` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL ;
ALTER TABLE  `auth_accounts` ADD  `Email` nvarchar(255) NULL AFTER  `AccountID` ;
ALTER TABLE  `auth_accounts` ADD  `ProviderID` INT UNSIGNED NULL AFTER  `Email` ;

CREATE TABLE IF NOT EXISTS `translation_group` (
  `TranslationGroupID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(128) COLLATE utf8_swedish_ci NOT NULL,
  `Canon` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`TranslationGroupID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

ALTER TABLE `translation` ADD `TranslationGroupID` INT(11) NULL AFTER `LanguageID`;
ALTER TABLE `translation` ADD `Deleted` BIT(1) DEFAULT B'0' AFTER `Latest`;

INSERT INTO `translation_group` (`TranslationGroupID`, `Name`, `Canon`)
  VALUES (1, 'Verified and confirmed', b'1'),
         (2, 'Neologism', b'0'),
         (10, 'Quettaparma Quenyallo', b'1'),
         (20, 'Parviphith', b'0'),
         (30, 'Hiswelókë''s Sindarin Dictionary', b'1'),
         (40, 'Parma Eldalamberon 17 Sindarin Corpus', b'1'),
         (50, 'Mellonath Daeron', b'1'),
         (60, 'Tolkiendil Compound Sindarin Names', b'1'),
         (70, 'Eldamo', b'1');

UPDATE `translation` SET `TranslationGroupID` = 10 WHERE `EnforcedOwner` = 2;
UPDATE `translation` SET `TranslationGroupID` = 20 WHERE `EnforcedOwner` = 25;
UPDATE `translation` SET `TranslationGroupID` = 30 WHERE `EnforcedOwner` = 1;
UPDATE `translation` SET `TranslationGroupID` = 40 WHERE `EnforcedOwner` = 3 AND `LanguageID` = 1 AND `DateCreated` >= '2012-04-23 00:00' AND `DateCreated` < '2012-04-24 00:00';
UPDATE `translation` SET `TranslationGroupID` = 50 WHERE `EnforcedOwner` = 3 AND `DateCreated` >= '2012-03-18 00:00' AND `DateCreated` < '2012-03-19 00:00';
UPDATE `translation` SET `TranslationGroupID` = 60 WHERE `EnforcedOwner` = 30;

insert into `version` (`number`, `date`) values (1.3, NOW());
