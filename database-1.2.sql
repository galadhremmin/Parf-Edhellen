CREATE TABLE IF NOT EXISTS `auth_groups` (
  `Name` varchar(32) COLLATE utf8_swedish_ci NOT NULL,
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`Name`),
  KEY `id` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `auth_groups`
--

INSERT INTO `auth_groups` (`Name`, `ID`) VALUES
('Administrators', 1),
('Users', 2);

CREATE TABLE IF NOT EXISTS `auth_accounts_groups` (
  `AccountID` int(10) unsigned NOT NULL,
  `GroupID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`AccountID`,`GroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `auth_accounts_groups` (`AccountID`, `GroupID`)
  SELECT `AccountID`, 2 FROM `auth_accounts` 

ALTER TABLE  `auth_accounts` DROP  `PrivilegeGroup` ;

insert into `version` (`number`, `date`) values (1.2, NOW())
