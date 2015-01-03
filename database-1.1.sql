ALTER TABLE  `language` ADD  `Tengwar` VARCHAR( 128 ) NULL ;

UPDATE `language` SET  `Tengwar` =  'iT2#7T5' WHERE  `language`.`ID` =1;
UPDATE `language` SET  `Tengwar` =  'zR5Ì#' WHERE  `language`.`ID` =2;
UPDATE `language` SET  `Tengwar` =  '5^mY7T5' WHERE  `language`.`ID` =4;
UPDATE `language` SET  `Tengwar` =  '1RjR7T5' WHERE  `language`.`ID` =5;
UPDATE `language` SET  `Tengwar` =  '5#2^7T5' WHERE  `language`.`ID` =7;

CREATE TABLE IF NOT EXISTS `sentence` (
  `SentenceID` int(11) NOT NULL AUTO_INCREMENT,
  `Description` longtext COLLATE utf8_swedish_ci NOT NULL,
  `LanguageID` int(11) NOT NULL,
  `Source` varchar(32) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`SentenceID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

CREATE TABLE IF NOT EXISTS `sentence_fragment` (
  `FragmentID` int(11) NOT NULL AUTO_INCREMENT,
  `SentenceID` int(11) NOT NULL,
  `TranslationID` int(11) DEFAULT NULL,
  `Fragment` varchar(48) COLLATE utf8_swedish_ci NOT NULL,
  `Comments` text COLLATE utf8_swedish_ci NOT NULL,
  `Order` int(11) NOT NULL,
  PRIMARY KEY (`FragmentID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
