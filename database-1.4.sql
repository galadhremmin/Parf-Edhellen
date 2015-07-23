ALTER TABLE  `language` CHANGE  `Name`  `Name` VARCHAR( 24 ) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL ;
ALTER TABLE  `translation` CHANGE  `Type`  `Type` ENUM(  'n',  'ger',  'pref',  'adj',  'n/adj',  'prep',  'prep/conj',  'conj',  'pron',  'v',  'interj',  'adv',  'aux',  'v/impers',  'adj/num',  'art',  'der',  'adj|adv',  'art/pron',  'suff',  'theon',  'topon',  'gen',  'name',  'unset' ) CHARACTER SET utf8 COLLATE utf8_swedish_ci NULL DEFAULT NULL ;

INSERT INTO `language`
  (`ID`, `Name`, `Order`, `Invented`, `Tengwar`)
  VALUES
  (20, 'Primitive elvish', 20, '1', NULL),
  (25, 'Ancient quenya', 25, '1', NULL),
  (30, 'Ancient telerin', 30, '1', NULL),
  (35, 'Old sindarin', 35, '1', NULL),
  (40, 'North sindarin', 40, '1', NULL),
  (45, 'Avarin', 45, '1', '`CyE7T5'),
  (50, 'Edain', 50, '1', NULL),
  (55, 'Primitive adûnaic', 55, '1', NULL),
  (60, 'Rohirric', 60, '1', NULL),
  (65, 'Wose', 65, '1', NULL),
  (70, 'Easterling', 70, '1', NULL),
  (71, 'Dunlending', 71, '1', NULL),
  (75, 'Valarin', 75, '1', 'yEjE7T5'),
  (80, 'Entish', 80, '1', NULL),
  (85, 'Qenya', 85, '1', 'zR5Ì#'),
  (90, 'Doriathrin', 90, '1', NULL);

insert into `version` (`number`, `date`) values (1.4, NOW());
