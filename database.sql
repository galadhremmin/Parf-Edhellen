-- phpMyAdmin SQL Dump
-- version 2.11.8.1
-- http://www.phpmyadmin.net
--
-- Host: mysql.u0228621.fsdata.se
-- Generation Time: Aug 21, 2011 at 03:00 PM
-- Server version: 5.0.67
-- PHP Version: 5.2.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `u0228621_7`
--

-- --------------------------------------------------------

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=7 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `grammar_type`
--

CREATE TABLE IF NOT EXISTS `grammar_type` (
  `GrammarTypeID` tinyint(2) unsigned NOT NULL auto_increment,
  `Name` varchar(32) collate utf8_swedish_ci NOT NULL,
  `Order` smallint(2) unsigned NOT NULL,
  PRIMARY KEY  (`GrammarTypeID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- Table structure for table `inflection`
--

CREATE TABLE IF NOT EXISTS `inflection` (
  `InflectionID` int(8) unsigned NOT NULL auto_increment,
  `WordID` int(6) unsigned NOT NULL,
  `TranslationID` int(8) unsigned NOT NULL,
  `GrammarTypeID` tinyint(2) unsigned NOT NULL,
  `Mutation` enum('sm','nm','mm','lm','stm') collate utf8_swedish_ci default NULL,
  `Source` varchar(128) collate utf8_swedish_ci default NULL,
  `Phonetic` varchar(128) collate utf8_swedish_ci default NULL,
  `Comments` text collate utf8_swedish_ci,
  PRIMARY KEY  (`InflectionID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=3 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `namespace`
--

CREATE TABLE IF NOT EXISTS `namespace` (
  `NamespaceID` int(10) unsigned NOT NULL auto_increment,
  `IdentifierID` int(8) NOT NULL,
  PRIMARY KEY  (`NamespaceID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=2693 ;

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
  PRIMARY KEY  (`TranslationID`),
  KEY `NamespaceIDIndex` (`NamespaceID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=27409 ;

-- --------------------------------------------------------

--
-- Table structure for table `word`
--

CREATE TABLE IF NOT EXISTS `word` (
  `KeyID` int(8) unsigned NOT NULL auto_increment,
  `Key` varchar(48) collate utf8_swedish_ci NOT NULL,
  `AuthorID` int(5) unsigned NOT NULL,
  PRIMARY KEY  (`KeyID`),
  KEY `KeyIndex` (`Key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=15714 ;