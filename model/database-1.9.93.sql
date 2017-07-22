ALTER TABLE `sentence_fragments` ADD `is_excluded` int(1) NOT NULL DEFAULT 0;

INSERT INTO `version` (`number`, `date`) VALUES (1.993, NOW());
