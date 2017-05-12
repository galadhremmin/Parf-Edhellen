ALTER TABLE `accounts` ADD `has_avatar` smallint(1) NOT NULL DEFAULT 0;
ALTER TABLE `translations` ADD `is_rejected` smallint(1) NOT NULL DEFAULT 0;
ALTER TABLE `translations` ADD `speech_id` smallint(1) NULL;

INSERT INTO `speeches` (`name`) VALUES ('gerund noun'), ('noun/adjective'), 
    ('preposition/conjugation'), ('auxillary verb'), ('verb (impersonal)'),
    ('article'), ('theology'), ('name'), ('participle'), ('interrogative'),
    ('conjugation');

UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'noun'
) WHERE `type` = 'n';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'gerund noun'
) WHERE `type` = 'ger';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'prefix'
) WHERE `type` = 'pref';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'adjective'
) WHERE `type` = 'adj';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'noun/adjective'
) WHERE `type` = 'n/adj';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'preposition'
) WHERE `type` = 'prep';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'preposition/conjugation'
) WHERE `type` = 'prep/conj';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'conjugation'
) WHERE `type` = 'conj';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'pronoun'
) WHERE `type` = 'pron';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'verb'
) WHERE `type` = 'v';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'interjection'
) WHERE `type` = 'interj';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'adverb'
) WHERE `type` = 'adv';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'auxillary verb'
) WHERE `type` = 'aux';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'verb (impersonal)'
) WHERE `type` = 'v/impers';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'cardinal'
) WHERE `translation` IN ('one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve',
    '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', 'eleven, (lit.) fresh one', 'fifteen',
    'eighteen', 'fourteen', 'nineteen', 'seventeen', 'sixteen', 'thirteen', 'thousand', 'twenty four');
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'fraction'
) WHERE `type` = 'adj/num' AND `speech_id` IS NULL AND (`translation` LIKE 'one %th' OR `translation` = 'one (first of a series)');
UPDATE `translations` SET `speech_id` = (UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'conjugation'
) WHERE `type` = 'conj';
    SELECT `id` FROM `speeches` WHERE `name` = 'ordinal'
) WHERE `type` = 'adj/num' AND `speech_id` IS NULL;
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'article'
) WHERE `type` = 'art';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'adjective'
) WHERE `type` = 'adj|adv';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'definite article'
) WHERE `type` = 'art/pron';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'suffix'
) WHERE `type` = 'suff';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'theology'
) WHERE `type` = 'theon';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'place name'
) WHERE `type` = 'topon';
UPDATE `translations` SET `speech_id` = NULL -- express genitive through inflections instead
WHERE `type` = 'gen';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'name'
) WHERE `type` = 'name';
UPDATE `translations` SET `speech_id` = NULL
WHERE `type` = 'unset';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'particle'
) WHERE `type` = 'part';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'participle'
) WHERE `type` = 'participle';
UPDATE `translations` SET `speech_id` = (
    SELECT `id` FROM `speeches` WHERE `name` = 'interrogative'
) WHERE `type` = 'interrog';

ALTER TABLE `translations` DROP `namespace_id`;
ALTER TABLE `translations` DROP `type`;
ALTER TABLE `translations` DROP `gender`;
DROP TABLE `namespace`;

-- 'n','ger','pref','adj','n/adj','prep','prep/conj','conj','pron','v','interj',
-- 'adv','aux','v/impers','adj/num','art','der','adj|adv','art/pron','suff','theon',
-- 'topon','gen','name','unset','part','participle','interrog'
