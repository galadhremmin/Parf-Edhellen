alter table `languages` add `tengwar_mode` varchar(32) null;

update `languages` set `tengwar_mode` = 'quenya' 
    where `name` in ('Quenya', 'Qenya', 'Ancient quenya');
update `languages` set `tengwar_mode` = 'sindarin' 
    where `name` in ('Sindarin', 'Nandorin');
update `languages` set `tengwar_mode` = 'sindarin-beleriand' 
    where `name` in ('Noldorin', 'North sindarin', 'Doriathrin', 'Old sindarin');
update `languages` set `tengwar_mode` = 'adunaic' 
    where `name` in ('Adunaic', 'Primitive adûnaic', 'Adûnaic');
update `languages` set `tengwar_mode` = 'telerin'
    where `name` in ('Telerin', 'Ancient telerin');
update `languages` set `tengwar_mode` = 'westron' 
    where `name` in ('Westron');
update `languages` set `tengwar_mode` = 'blackspeech'  
    where `name` in ('Black speech');

