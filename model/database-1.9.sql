ALTER TABLE `auth_accounts` CHANGE `AccountID` `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT, 
    CHANGE `Email` `email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    CHANGE `ProviderID` `authorization_provider_id` INT(10) UNSIGNED NULL DEFAULT NULL, 
    CHANGE `Identity` `identity` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `Nickname` `nickname` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `DateRegistered` `created_at` DATETIME NOT NULL, 
    CHANGE `Configured` `is_configured` TINYINT(1) NOT NULL DEFAULT '0', 
    CHANGE `Tengwar` `tengwar` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `Profile` `profile` TEXT CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `RememberToken` `remember_token` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `auth_accounts` ADD `updated_at` DATETIME NULL;
RENAME TABLE `auth_accounts` TO `accounts`;

ALTER TABLE `auth_accounts_groups` CHANGE `AccountID` `account_id` INT(10) UNSIGNED NOT NULL, 
    CHANGE `GroupID` `role_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `auth_accounts_groups` ADD `updated_at` DATETIME NULL,
    ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
RENAME TABLE `auth_accounts_groups` TO `account_role_rels`;

ALTER TABLE `auth_groups` CHANGE `Name` `name` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `ID` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `auth_groups` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `updated_at` DATETIME NULL;
RENAME TABLE `auth_groups` TO `roles`;

DROP TABLE `auth_logins`;

ALTER TABLE `auth_providers` CHANGE `ProviderID` `id` INT(2) UNSIGNED NOT NULL AUTO_INCREMENT, 
    CHANGE `Name` `name` VARCHAR(48) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
    CHANGE `Logo` `logo_file_name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `URL` `name_identifier` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL;

ALTER TABLE `auth_providers` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `updated_at` DATETIME NULL;
RENAME TABLE `auth_providers` TO `authorization_providers`;

DROP TABLE `cache`;
DROP TABLE IF EXISTS `experience_message`;
DROP TABLE IF EXISTS `experience_world`;

ALTER TABLE `favourite` CHANGE `AccountID` `account_id` INT(5) UNSIGNED NOT NULL, 
    CHANGE `TranslationID` `translation_id` INT(8) UNSIGNED NOT NULL, 
    CHANGE `ID` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, 
    CHANGE `DateCreated` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `favourite` ADD `updated_at` DATETIME NULL;
RENAME TABLE `favourite` TO `favourites`;

ALTER TABLE `inflection` CHANGE `InflectionID` `id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
    CHANGE `Name` `name` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `Group` `group_name` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL;
ALTER TABLE `inflection` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `updated_at` DATETIME NULL;
RENAME TABLE `inflection` TO `inflections`;

ALTER TABLE `keywords` CHANGE `Keyword` `keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `NormalizedKeyword` `normalized_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `ReversedNormalizedKeyword` `reversed_normalized_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `NamespaceID` `namespace_id_deprecated` INT(10) UNSIGNED NULL DEFAULT NULL, 
    CHANGE `TranslationID` `translation_id` INT(8) UNSIGNED NULL DEFAULT NULL, 
    CHANGE `RelationID` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, 
    CHANGE `WordID` `word_id` INT(8) UNSIGNED NULL DEFAULT NULL, 
    CHANGE `CreationDate` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    CHANGE `SenseID` `sense_id` INT(11) NULL DEFAULT NULL, 
    CHANGE `IsSense` `is_sense` BIT(1) NOT NULL DEFAULT b'0';
ALTER TABLE `keywords` ADD `updated_at` DATETIME NULL;

ALTER TABLE `language` CHANGE `ID` `id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT, 
    CHANGE `Name` `name` VARCHAR(24) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `Order` `order` INT(2) NOT NULL, 
    CHANGE `Invented` `is_invented` BIT(1) NOT NULL DEFAULT b'0', 
    CHANGE `Tengwar` `tengwar` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `language` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `updated_at` DATETIME NULL;
RENAME TABLE `language` TO `languages`;

-- Ignoring namespace, as it is deprecated and will be deleted

ALTER TABLE `sense` CHANGE `SenseID` `id` INT(11) NOT NULL, 
    CHANGE `Description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `sense` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `updated_at` DATETIME NULL;
RENAME TABLE `sense` TO `senses`;

ALTER TABLE `sentence` CHANGE `SentenceID` `id` INT(11) NOT NULL AUTO_INCREMENT, 
    CHANGE `Description` `description` LONGTEXT CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `LanguageID` `language_id` INT(11) NOT NULL, 
    CHANGE `Source` `source` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `Neologism` `is_neologism` BIT(1) NULL DEFAULT b'0', 
    CHANGE `Approved` `is_approved` BIT(1) NULL DEFAULT b'0', 
    CHANGE `AuthorID` `account_id` INT(11) NULL DEFAULT NULL, 
    CHANGE `LongDescription` `long_description` LONGTEXT CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL,
    CHANGE `DateCreated` `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP, 
    CHANGE `Name` `name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL;
ALTER TABLE `sentence` ADD `updated_at` DATETIME NULL;
RENAME TABLE `sentence` TO `sentences`;

ALTER TABLE `sentence_fragment` CHANGE `FragmentID` `id` INT(11) NOT NULL AUTO_INCREMENT, 
    CHANGE `SentenceID` `sentence_id` INT(11) NOT NULL, 
    CHANGE `TranslationID` `translation_id` INT(11) NULL DEFAULT NULL, 
    CHANGE `Fragment` `fragment` VARCHAR(48) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `Tengwar` `tengwar` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `Comments` `comments` TEXT CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `Order` `order` INT(11) NOT NULL, 
    CHANGE `SpeechID` `speech_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `sentence_fragment` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `updated_at` DATETIME NULL;
RENAME TABLE `sentence_fragment` TO `sentence_fragments`;

ALTER TABLE `sentence_fragment_inflection` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, 
    CHANGE `FragmentID` `fragment_id` INT(11) NOT NULL, 
    CHANGE `InflectionID` `inflection_id` INT(11) NOT NULL;
ALTER TABLE `sentence_fragment_inflection` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `updated_at` DATETIME NULL;
RENAME TABLE `sentence_fragment_inflection` TO `sentence_fragment_inflection_rels`;

ALTER TABLE `speech` CHANGE `Name` `name` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `Order` `order` INT(3) UNSIGNED NOT NULL DEFAULT 0, 
    CHANGE `SpeechID` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `speech` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `updated_at` DATETIME NULL;
RENAME TABLE `speech` TO `speeches`;

ALTER TABLE `translation` CHANGE `TranslationID` `id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
    CHANGE `LanguageID` `language_id` INT(1) UNSIGNED NULL DEFAULT NULL, 
    CHANGE `TranslationGroupID` `translation_group_id` INT(11) NULL DEFAULT NULL, 
    CHANGE `Translation` `translation` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `Uncertain` `is_uncertain` BIT(1) NULL DEFAULT b'0', 
    CHANGE `Etymology` `etymology` VARCHAR(512) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `Type` `type` ENUM('n','ger','pref','adj','n/adj','prep','prep/conj','conj','pron','v','interj','adv','aux','v/impers','adj/num','art','der','adj|adv','art/pron','suff','theon','topon','gen','name','unset','part','participle','interrog') CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `Source` `source` TEXT CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `Comments` `comments` TEXT CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `WordID` `word_id` INT(8) UNSIGNED NOT NULL, 
    CHANGE `Latest` `is_latest` TINYINT(1) NOT NULL DEFAULT '1', 
    CHANGE `Deleted` `is_deleted` BIT(1) NULL DEFAULT b'0', 
    CHANGE `DateCreated` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    CHANGE `AuthorID` `account_id` INT(6) UNSIGNED NOT NULL, 
    CHANGE `Tengwar` `tengwar` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `Gender` `gender` ENUM('masc','fem','none') CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL DEFAULT 'none', 
    CHANGE `Phonetic` `phonetic` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `ParentTranslationID` `parent_translation_id` INT(10) UNSIGNED NULL DEFAULT NULL, 
    CHANGE `EldestTranslationID` `origin_translation_id` INT(10) UNSIGNED NULL DEFAULT NULL, 
    CHANGE `NamespaceID` `namespace_id` INT(11) NOT NULL, 
    CHANGE `Index` `is_index` TINYINT(1) NOT NULL DEFAULT '0', 
    CHANGE `ExternalID` `external_id` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `SenseID` `sense_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `translation` ADD `updated_at` DATETIME NULL;
RENAME TABLE `translation` TO `translations`;

ALTER TABLE `translation_group` CHANGE `TranslationGroupID` `id` INT(11) NOT NULL AUTO_INCREMENT, 
    CHANGE `Name` `name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `ExternalLinkFormat` `external_link_format` VARCHAR(1024) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `Canon` `is_canon` BIT(1) NOT NULL DEFAULT b'0';
ALTER TABLE `translation_group` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `updated_at` DATETIME NULL;
RENAME TABLE `translation_group` TO `translation_groups`;

ALTER TABLE `translation_review` CHANGE `ReviewID` `id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
    CHANGE `AuthorID` `account_id` INT(6) UNSIGNED NOT NULL, 
    CHANGE `LanguageID` `language_id` INT(1) UNSIGNED NOT NULL, 
    CHANGE `DateCreated` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    CHANGE `Word` `word` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `Data` `payload` TEXT CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `Reviewed` `date_reviewed` DATETIME NULL DEFAULT NULL, 
    CHANGE `ReviewedBy` `reviewed_by_account_id` INT(6) UNSIGNED NULL DEFAULT NULL, 
    CHANGE `Approved` `is_approved` BIT(1) NULL DEFAULT NULL, 
    CHANGE `Justification` `justification` TEXT CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `TranslationID` `translation_id` INT(8) NULL DEFAULT NULL;
ALTER TABLE `translation_review` ADD `updated_at` DATETIME NULL;
RENAME TABLE `translation_review` TO `translation_reviews`;

ALTER TABLE `word` CHANGE `KeyID` `id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT, 
    CHANGE `Key` `word` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL, 
    CHANGE `AuthorID` `account_id` INT(5) UNSIGNED NOT NULL, 
    CHANGE `NormalizedKey` `normalized_word` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL, 
    CHANGE `ReversedNormalizedKey` `reversed_normalized_word` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `word` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD `updated_at` DATETIME NULL;
RENAME TABLE `word` TO `words`;

INSERT INTO `version` (`number`, `date`) VALUES (1.9, CURRENT_TIMESTAMP);
