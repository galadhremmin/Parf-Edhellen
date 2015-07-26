ALTER TABLE  `translation` ADD  `Uncertain` BIT(1) DEFAULT b'0' AFTER  `Translation` ;

insert into `language` (`ID`, `Name`, `Order`, `Invented`, `Tengwar`)
 values (91, 'Gnomish', 91, '1', NULL);

insert into `version` (`number`, `date`) values (1.5, NOW());