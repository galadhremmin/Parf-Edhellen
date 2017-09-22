
CREATE INDEX `WordsWordIndex` ON `words` (`word`);
CREATE INDEX `WordsReversedNormalizedWordIndex` ON `words` (`reversed_normalized_word`); 

DROP INDEX `WordNamespaceRelation` ON `keywords`;
DROP INDEX `WordID` ON `keywords`;
CREATE INDEX `KeywordsNormalizedKeyword` ON `keywords` (`normalized_keyword`);
CREATE INDEX `KeywordsReversedNormalizedKeyword` ON `keywords` (`reversed_normalized_keyword`);
CREATE INDEX `KeywordsTranslationId` ON `keywords` (`translation_id`);
CREATE INDEX `KeywordsSenseId` ON `keywords` (`sense_id`);

CREATE INDEX `TranslationsIsLatest` ON `translations` (`is_latest`, `is_deleted`);
CREATE INDEX `TranslationsGroupId` ON `translations` (`translation_group_id`);

-- WIP!

ALTER TABLE `translation_reviews` ADD `sentence_id` int(11) NULL;
ALTER TABLE `translation_reviews` ADD `type` varchar(16) NOT NULL DEFAULT 'translation';

RENAME TABLE `translation_reviews` TO `contributions`;

ALTER TABLE `forum_contexts` ADD `is_elevated` int(1) DEFAULT 0;
INSERT INTO `forum_contexts` (`id`, `name`, `is_elevated`) VALUES (5, 'contribution', 1); 

INSERT INTO `version` (`number`, `date`) VALUES (2.0, NOW());
