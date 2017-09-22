
CREATE INDEX `WordsWordIndex` ON `words` (`word`);
CREATE INDEX `WordsReversedNormalizedWordIndex` ON `words` (`reversed_normalized_word`); 

DROP INDEX `WordNamespaceRelation` ON `keywords`;
DROP INDEX `WordID` ON `keywords`;
CREATE INDEX `KeywordsNormalizedKeyword` ON `keywords` (`normalized_keyword`);
CREATE INDEX `KeywordsReversedNormalizedKeyword` ON `keywords` (`reversed_normalized_keyword`);
CREATE INDEX `KeywordsTranslationId` ON `keywords` (`translation_id`);
CREATE INDEX `KeywordsSenseId` ON `keywords` (`sense_id`);

CREATE INDEX `TranslationsIsLatest` ON `translations` (`is_latest`, `is_deleted`);

-- WIP!

INSERT INTO `version` (`number`, `date`) VALUES (2.0, NOW());
