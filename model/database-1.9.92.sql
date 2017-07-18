ALTER TABLE `system_errors` ADD `ip` VARBINARY(16) NULL;
ALTER TABLE `system_errors` ADD `line` int(6) NULL;
ALTER TABLE `system_errors` ADD `file` varchar(64) NULL;

INSERT INTO `version` (`number`, `date`) VALUES (1.992, NOW());
