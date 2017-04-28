alter table `languages` add `tengwar_mode` varchar(16) null;

update `languages` set `tengwar_mode` = 'classical' where `name` in ('Quenya', 'Qenya');
update `languages` set `tengwar_mode` = 'general-use' where `name` in ('Sindarin', 'Noldorin');
