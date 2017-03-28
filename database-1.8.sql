create table if not exists `sense` (
  `SenseID` int not null,
  `Description` text collate utf8_swedish_ci,
  primary key(`SenseID`)
);

-- Trim whitespace
update `word` set 
  `Key` = trim(`Key`), 
  `NormalizedKey` = trim(`NormalizedKey`), 
  `ReversedNormalizedKey` = trim(`ReversedNormalizedKey`);

-- Create unique senses
insert into `sense` (`SenseID`, `Description`)
  select distinct `IdentifierID`, null 
  from `namespace` 
  order by `IdentifierID` asc;

-- Move deprecated namespaces to new senses
update `translation` as t 
  inner join `namespace` as n on n.`NamespaceID` = t.`NamespaceID`
  left join `namespace` as n0 on n0.`NamespaceID` = (
    select min(n1.`NamespaceID`)
      from `namespace` n1
      where n1.IdentifierID = n.IdentifierID
  )
  set t.`SenseID` = n0.IdentifierID;

-- Delete deprecated translations
delete t from `translation` as t 
  where t.`Latest` = 0 and not exists(select 1 from word where `KeyID` = t.`WordID`);

-- Add foreign key to keywords and translation
alter table `keywords` add `SenseID` int null;
alter table `keywords` add `IsSense` bit null null default 0;
alter table `keywords` add `IsActive` bit null null default 1;
alter table `translation` add `SenseID` int null;
alter table `translation` add index `idx_senseID` (`SenseID`);

-- Transition from indexes to keywords
insert into `keywords` (`IsActive`, `IsSense`, `SenseID`, `WordID`, `Keyword`, `NormalizedKeyword`, `ReversedNormalizedKeyword`)
  select distinct 
    1, 0, n.`IdentifierID`, w.`KeyID`, w.`Key`, w.`NormalizedKey`, w.`ReversedNormalizedKey`
  from `translation` as t
    inner join `word` as w on w.`KeyID` = t.`WordID`
    inner join `namespace` as n on n.`NamespaceID` = t.`NamespaceID`
  where t.`Deleted` = 0 and t.`Index` = 1 and w.`KeyID` <> n.`IdentifierID`;

-- Add keywords to senses
insert into `keywords` (`IsActive`, `IsSense`, `SenseID`, `WordID`, `Keyword`, `NormalizedKeyword`, `ReversedNormalizedKeyword`)
  select distinct
    coalesce((select 1 from `translation` as t where t.`Latest` = 1 and t.`Deleted` = 0 and t.`SenseID` = s.`SenseID` limit 1), 0), 1, s.`SenseID`, w.`KeyID`, w.`Key`, w.`NormalizedKey`, w.`ReversedNormalizedKey`
  from `sense` as s
    inner join `word` as w on w.`KeyID` = s.`SenseID`;

insert into `version` (`number`, `date`) values (1.8, NOW());