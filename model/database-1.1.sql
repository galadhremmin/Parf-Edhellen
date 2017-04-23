ALTER TABLE  `language` ADD  `Tengwar` VARCHAR( 128 ) NULL ;

UPDATE `language` SET  `Tengwar` =  'iT2#7T5' WHERE  `language`.`ID` =1;
UPDATE `language` SET  `Tengwar` =  'zR5Ì#' WHERE  `language`.`ID` =2;
UPDATE `language` SET  `Tengwar` =  '5^mY7T5' WHERE  `language`.`ID` =4;
UPDATE `language` SET  `Tengwar` =  '1RjR7T5' WHERE  `language`.`ID` =5;
UPDATE `language` SET  `Tengwar` =  '5#2^7T5' WHERE  `language`.`ID` =7;

--
-- Table structure for table `sentence`
--

CREATE TABLE IF NOT EXISTS `sentence` (
  `SentenceID` int(11) NOT NULL AUTO_INCREMENT,
  `Description` longtext COLLATE utf8_swedish_ci NOT NULL,
  `LanguageID` int(11) NOT NULL,
  `Source` varchar(64) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`SentenceID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `sentence`
--

INSERT INTO `sentence` (`SentenceID`, `Description`, `LanguageID`, `Source`) VALUES
(1, 'Well met! Glorfindel''s greeting to Aragorn.', 1, 'LotR1/I ch. 12'),
(2, 'Elvish gate open now for us; doorway of the Dwarf-folk listen to the word of my tongue!', 1, 'LotR1/II ch. 4');

-- --------------------------------------------------------

--
-- Table structure for table `sentence_fragment`
--

CREATE TABLE IF NOT EXISTS `sentence_fragment` (
  `FragmentID` int(11) NOT NULL AUTO_INCREMENT,
  `SentenceID` int(11) NOT NULL,
  `TranslationID` int(11) DEFAULT NULL,
  `Fragment` varchar(48) COLLATE utf8_swedish_ci NOT NULL,
  `Tengwar` varchar(128) COLLATE utf8_swedish_ci DEFAULT NULL,
  `Comments` text COLLATE utf8_swedish_ci NOT NULL,
  `Order` int(11) NOT NULL,
  PRIMARY KEY (`FragmentID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=17 ;

--
-- Dumping data for table `sentence_fragment`
--

INSERT INTO `sentence_fragment` (`FragmentID`, `SentenceID`, `TranslationID`, `Fragment`, `Tengwar`, `Comments`, `Order`) VALUES
(1, 1, 14566, 'Mae', 'tlE', 'Adverb. Adverbs appear to be positioned after the verb they describe. This is an exception, perhaps for emphasis.', 10),
(2, 1, 21582, 'govannen', 'xrH5#{5$', 'Past participle. "Thou met" hence _ci_ ("thou") + _govan-nen_ ("met"). _ci_ is mutated ("lenited") by the adverb _mae_.', 20),
(3, 2, 11171, 'Annon', '5#{5^', '', 10),
(4, 2, 12349, 'edhellen', '4FjR¸5$', '', 20),
(5, 2, NULL, ',', '=', '', 30),
(6, 2, 21317, 'edro', '2$7`N', 'Imperative form.', 40),
(7, 2, 21635, 'hi', '9`B', '', 50),
(8, 2, 11096, 'ammen', 't#{5$', '_an + men_ for us.', 60),
(9, 2, NULL, '!', 'Á', '', 70),
(10, 2, 12712, 'Fennas', 'e5${iE', '', 80),
(11, 2, 15263, 'nogothrim', '5x^3Y7t%', '', 90),
(12, 2, NULL, ',', '=', '', 100),
(13, 2, 21715, 'lasto', 'jiE1`N', 'Imperative form.', 110),
(14, 2, 15649, 'beth', 'w3R', 'The P has undergone lenition as the word is the object of the verb.', 120),
(15, 2, 131749, 'lammen', 'jt#{5$', '', 130),
(16, 2, NULL, '!', 'Á', '', 140);

CREATE TABLE IF NOT EXISTS `version` (
  `number` float NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

insert into `version` (`number`, `date`) values (1.1, NOW())

