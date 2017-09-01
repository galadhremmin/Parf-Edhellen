ALTER TABLE `translation_reviews` ADD `keywords` text NULL COLLATE utf8_swedish_ci;
ALTER TABLE `translation_reviews` ADD `notes` text NULL COLLATE utf8_swedish_ci;
ALTER TABLE `translation_reviews` ADD `sense` varchar(128) NULL COLLATE utf8_swedish_ci;

INSERT INTO `version` (`number`, `date`) VALUES (1.994, NOW());
