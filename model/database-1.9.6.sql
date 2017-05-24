ALTER TABLE `languages` ADD `category` varchar(64) NULL;
ALTER TABLE `languages` ADD `is_unusual` smallint(1) NOT NULL DEFAULT 0;
ALTER TABLE `words` MODIFY `word` varchar(128) NOT NULL;
ALTER TABLE `words` MODIFY `normalized_word` varchar(128) NOT NULL;
ALTER TABLE `words` MODIFY `reversed_normalized_word` varchar(128) NOT NULL;

UPDATE `languages` SET `category` = 'Late Period (1950-1973)' 
    WHERE `id` IN(1, 2, 5, 6, 7, 8, 9, 10, 11, 12, 20, 25, 30, 35,
        40, 45, 50, 55, 60, 65, 70, 71, 75, 80);

UPDATE `languages` SET `category` = 'Middle Period (1930-1950)' 
    WHERE `id` IN(4, 85, 90);

UPDATE `languages` SET `category` = 'Early Period (1910-1930)' 
    WHERE `id` IN(91);

UPDATE `languages` SET `category` = 'Real-world languages' 
    WHERE `id` IN(3);    

INSERT INTO `languages` (`name`, `order`, `is_invented`, `tengwar`, `created_at`, `category`)
    VALUES ('Ossriandric', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Early Ilkorin', 0, 1, NULL, NOW(), 'Early Period (1910-1930)'),
        ('Early Noldorin', 0, 1, NULL, NOW(), 'Early Period (1910-1930)'),
        ('Old Noldorin', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Middle Primitive Elvish', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Taliska', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Lemberin', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Middle Telerin', 0, 1, NULL, NOW(), 'Middle Period (1930-1950)'),
        ('Solosimpi', 0, 1, NULL, NOW(), 'Early Period (1910-1930)'),
        ('Early Primitive Elvish', 0, 1, NULL, NOW(), 'Early Period (1910-1930)');

UPDATE `languages` SET `order` = 10 WHERE `category` = 'Real-world languages';
UPDATE `languages` SET `order` = 20 WHERE `category` = 'Early Period (1910-1930)';
UPDATE `languages` SET `order` = 30 WHERE `category` = 'Middle Period (1930-1950)';
UPDATE `languages` SET `order` = 40 WHERE `category` = 'Late Period (1950-1973)';

UPDATE `languages` SET `order` = 49 WHERE `name` = 'Quenya';
UPDATE `languages` SET `order` = 48 WHERE `name` = 'Sindarin';
UPDATE `languages` SET `order` = 41 WHERE `name` = 'Telerin';

UPDATE `languages` SET `is_unusual` = 1 WHERE `id` NOT IN(1,2,4,5,6,7,9,10,11);

INSERT INTO `version` (`number`, `date`) VALUES (1.96, NOW());
