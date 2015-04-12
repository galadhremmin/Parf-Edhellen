ALTER TABLE  `translation` ADD  `ParentTranslationID` INT UNSIGNED NULL AFTER  `Phonetic` ;

CREATE TABLE IF NOT EXISTS `favourite` (
  `AccountID` int(5) unsigned NOT NULL,
  `TranslationID` int(8) unsigned NOT NULL,
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

insert into `version` (`number`, `date`) values (1.3, NOW());
