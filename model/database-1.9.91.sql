DROP TABLE IF EXISTS `system_errors`;
CREATE TABLE `system_errors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `message` varchar(128) NOT NULL COLLATE utf8_swedish_ci,
  `url` varchar(1024) NOT NULL COLLATE utf8_swedish_ci,
  `error` text NULL COLLATE utf8_swedish_ci,
  `account_id` int(5) unsigned NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

INSERT INTO `version` (`number`, `date`) VALUES (1.991, NOW());
