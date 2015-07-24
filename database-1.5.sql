ALTER TABLE  `translation` ADD  `Uncertain` BIT(1) DEFAULT b'0' AFTER  `Translation` ;

insert into `version` (`number`, `date`) values (1.5, NOW());