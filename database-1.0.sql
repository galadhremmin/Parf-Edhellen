-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE IF NOT EXISTS `cache` (
  `token` varchar(255) collate utf8_swedish_ci NOT NULL,
  `content` text collate utf8_swedish_ci NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  PRIMARY KEY  (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;


--
-- Table structure for table `auth_accounts`
--

CREATE TABLE IF NOT EXISTS `auth_accounts` (
  `AccountID` int(5) unsigned NOT NULL auto_increment,
  `Identity` varchar(40) collate utf8_swedish_ci NOT NULL,
  `Nickname` varchar(32) collate utf8_swedish_ci NOT NULL,
  `DateRegistered` datetime NOT NULL,
  `Configured` tinyint(1) NOT NULL default '0',
  `PrivilegeGroup` int(1) unsigned NOT NULL default '0',
  `Tengwar` varchar(64) collate utf8_swedish_ci default NULL,
  `Profile` text collate utf8_swedish_ci,
  PRIMARY KEY  (`AccountID`),
  UNIQUE KEY `Identity` (`Identity`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `auth_logins`
--

CREATE TABLE IF NOT EXISTS `auth_logins` (
  `Date` int(11) NOT NULL,
  `IP` varchar(40) collate utf8_swedish_ci NOT NULL,
  `AccountID` int(5) NOT NULL,
  PRIMARY KEY  (`Date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth_providers`
--

CREATE TABLE IF NOT EXISTS `auth_providers` (
  `ProviderID` int(2) unsigned NOT NULL auto_increment,
  `Name` varchar(48) collate utf8_swedish_ci NOT NULL,
  `Logo` varchar(128) collate utf8_swedish_ci NOT NULL,
  `URL` varchar(255) collate utf8_swedish_ci NOT NULL,
  PRIMARY KEY  (`ProviderID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `experience_message`
--

CREATE TABLE IF NOT EXISTS `experience_message` (
  `MessageID` int(10) unsigned NOT NULL auto_increment,
  `WorldID` int(10) unsigned NOT NULL,
  `Date` int(10) unsigned NOT NULL,
  `Nick` varchar(16) collate utf8_swedish_ci NOT NULL,
  `Message` text collate utf8_swedish_ci NOT NULL,
  `Type` varchar(12) collate utf8_swedish_ci default NULL,
  PRIMARY KEY  (`MessageID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `experience_world`
--

CREATE TABLE IF NOT EXISTS `experience_world` (
  `WorldID` int(8) NOT NULL auto_increment,
  `AuthorIP` varchar(15) collate utf8_swedish_ci NOT NULL,
  `Password` varchar(64) collate utf8_swedish_ci NOT NULL,
  `AdminPassword` varchar(64) collate utf8_swedish_ci NOT NULL,
  `CreationDate` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`WorldID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `ID` int(1) unsigned NOT NULL auto_increment,
  `Name` varchar(16) collate utf8_swedish_ci NOT NULL,
  `Order` int(2) NOT NULL,
  `Invented` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `namespace`
--

CREATE TABLE IF NOT EXISTS `namespace` (
  `NamespaceID` int(10) unsigned NOT NULL auto_increment,
  `IdentifierID` int(8) NOT NULL,
  PRIMARY KEY  (`NamespaceID`),
  KEY `IdentifierIDIndex` (`IdentifierID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `translation`
--

CREATE TABLE IF NOT EXISTS `translation` (
  `TranslationID` int(8) unsigned NOT NULL auto_increment,
  `LanguageID` int(1) unsigned default NULL,
  `Translation` varchar(255) collate utf8_swedish_ci NOT NULL,
  `Etymology` varchar(128) collate utf8_swedish_ci default NULL,
  `Type` enum('n','ger','pref','adj','n/adj','prep','prep/conj','conj','pron','v','interj','adv','aux','v/impers','adj/num','art','der','adj|adv','art/pron','suff','theon','topon','gen','unset') collate utf8_swedish_ci default NULL,
  `Source` varchar(48) collate utf8_swedish_ci default NULL,
  `Comments` text collate utf8_swedish_ci,
  `WordID` int(8) unsigned NOT NULL,
  `Latest` tinyint(1) NOT NULL default '1',
  `DateCreated` datetime NOT NULL,
  `AuthorID` int(6) unsigned NOT NULL,
  `Tengwar` varchar(128) collate utf8_swedish_ci default NULL,
  `Gender` enum('masc','fem','none') collate utf8_swedish_ci NOT NULL default 'none',
  `Phonetic` varchar(128) collate utf8_swedish_ci default NULL,
  `NamespaceID` int(11) NOT NULL,
  `Index` tinyint(1) NOT NULL default '0',
  `EnforcedOwner` int(6) NOT NULL default '0',
  PRIMARY KEY  (`TranslationID`),
  KEY `NamespaceIDIndex` (`NamespaceID`),
  KEY `WordIDIndex` (`WordID`),
  KEY `LanguageIDIndex` (`LanguageID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `word`
--

CREATE TABLE IF NOT EXISTS `word` (
  `KeyID` int(8) unsigned NOT NULL auto_increment,
  `Key` varchar(64) collate utf8_swedish_ci NOT NULL,
  `AuthorID` int(5) unsigned NOT NULL,
  `NormalizedKey` varchar(64) collate utf8_swedish_ci default NULL,
  PRIMARY KEY  (`KeyID`),
  KEY `KeyIndex` (`NormalizedKey`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;
