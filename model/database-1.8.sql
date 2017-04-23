update keywords set creationdate = now() where creationdate < '1970-01-01 00:00:00';

create table if not exists `sense` (
  `SenseID` int not null,
  `Description` text collate utf8_swedish_ci,
  primary key(`SenseID`)
);

create table if not exists `sentence_fragment_inflection` (
  `id` int not null auto_increment,
  `FragmentID` int not null,
  `InflectionID` int not null,
  primary key (`id`)
);

alter table `sentence_fragment_inflection` add index `ix_fragment_id`(`FragmentID`);

-- Add foreign key to keywords and translation
alter table `keywords` add `SenseID` int null;
alter table `keywords` add `IsSense` bit not null default 0;
alter table `translation` add `SenseID` int null;
alter table `translation` add index `idx_senseID` (`SenseID`);

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

truncate `keywords`;

-- Transition from indexes to keywords
insert into `keywords` (`IsSense`, `SenseID`, `WordID`, `Keyword`, `NormalizedKeyword`, `ReversedNormalizedKeyword`)
  select distinct 
    0, n.`IdentifierID`, w.`KeyID`, w.`Key`, w.`NormalizedKey`, w.`ReversedNormalizedKey`
  from `translation` as t
    inner join `word` as w on w.`KeyID` = t.`WordID`
    inner join `namespace` as n on n.`NamespaceID` = t.`NamespaceID`
  where t.`Deleted` = 0 and t.`Index` = 1 and w.`KeyID` <> n.`IdentifierID`;

-- Add keywords to senses
insert into `keywords` (`IsSense`, `SenseID`, `WordID`, `Keyword`, `NormalizedKeyword`, `ReversedNormalizedKeyword`)
  select distinct
    1, s.`SenseID`, w.`KeyID`, w.`Key`, w.`NormalizedKey`, w.`ReversedNormalizedKey`
  from `sense` as s
    inner join `word` as w on w.`KeyID` = s.`SenseID`
  where exists (
    select 1 from `translation` as t where t.`Latest` = 1 and t.`Deleted` = 0 and t.`SenseID` = s.`SenseID` limit 1
  );

insert into `keywords` (`IsSense`, `SenseID`, `TranslationID`, `WordID`, `Keyword`, `NormalizedKeyword`, `ReversedNormalizedKeyword`)
  select distinct
    0, t.`SenseID`, t.`TranslationID`, t.`WordID`, w.`Key`, w.`NormalizedKey`, w.`ReversedNormalizedKey`
  from `translation` as t
    inner join `word` as w on w.`KeyID` = t.`WordID`
  where t.`Latest` = 1 and t.`Deleted` = 0 and not exists(
    select 1 from `keywords` as k where k.`SenseID` = t.`SenseID` and k.`Keyword` = w.`Key` and k.`WordID` = w.`KeyID` limit 1
  );

-- Transition existing definitions to markdown format
update translation set comments = replace(comments, '~', '**');
update translation set comments = replace(comments, '`', '**');

-- Transition providers from Hybridauth to Laravel Socialite
update `auth_providers` set `URL` = lower(`URL`);

-- Remember tokens are required by Laravel
alter table `auth_accounts` add `RememberToken` varchar(100)  collate utf8_swedish_ci null;

-- Adding neologisms to the sentence table
alter table `sentence` add `Neologism` bit default 0;
alter table `sentence` add `Approved` bit  default 0;
alter table `sentence` add `AuthorID` int null;
alter table `sentence` add `LongDescription` longtext null;
alter table `sentence` add `DateCreated` datetime default now();
alter table `sentence` add `Name` varchar(128) null;

update `sentence` as s
  set 
    s.`Approved` = 1, 
    s.`Name` = replace(replace(replace((
      select group_concat(f.`Fragment` separator ' ')
      from `sentence_fragment` as f
      where f.`SentenceID` = s.`SentenceID`
      group by f.`SentenceID`
    ), ' ,', ','), ' !', '!'), ' .', '.');

alter table `sentence` modify `Name` varchar(128) not null;

rename table `grammar_type` to `speech`;
alter table `speech` drop `GrammarTypeID`;
alter table `speech` add `SpeechID` int not null primary key auto_increment;
replace into `speech` (`SpeechID`, `Name`, `Order`) values (99, 'Unknown', 99);
alter table `sentence_fragment` add `SpeechID` int null; -- Defaults to unset

alter table `inflection` drop `WordID`;
alter table `inflection` drop `TranslationID`;
alter table `inflection` drop `Comments`;
alter table `inflection` drop `Phonetic`;
alter table `inflection` drop `Source`;
alter table `inflection` drop `Mutation`;
alter table `inflection` drop `GrammarTypeID`;
alter table `inflection` add `Name` varchar(64) not null;
alter table `inflection` add `Group` varchar(64) not null;

truncate table `speech`;
insert into `speech` (`Name`, `Order`) values
  ('adjective', 0),
  ('adverb', 0),
  ('affix', 0),
  ('definite article', 0),
  ('cardinal', 0),
  ('collective name', 0),
  ('collective noun', 0),
  ('family name', 0),
  ('feminine name', 0),
  ('fraction', 0),
  ('infix', 0),
  ('interjection', 0),
  ('masculine name', 0),
  ('noun', 0),
  ('ordinal', 0),
  ('particle', 0),
  ('phoneme', 0),
  ('place name', 0),
  ('prefix', 0),
  ('preposition', 0),
  ('pronoun', 0),
  ('proper name', 0),
  ('radical', 0),
  ('root', 0),
  ('suffix', 0),
  ('verb', 0);

truncate table `inflection`;
insert into `inflection` (`Group`, `Name`) values
  ('Inflections for number', 'singular'),
  ('Inflections for number', 'dual'),
  ('Inflections for number', 'plural'),
  ('Inflections for number', 'partitive plural'),
  ('Inflections for number', 'class plural'),
  ('Inflections for number', 'draft dual'),
  ('Inflections for number', 'draft plural'),

  ('Basic verb tenses', 'infinitive'),
  ('Basic verb tenses', 'aorist'),
  ('Basic verb tenses', 'present'),
  ('Basic verb tenses', 'past'),
  ('Basic verb tenses', 'strong past'),
  ('Basic verb tenses', 'perfect'),
  ('Basic verb tenses', 'strong perfect'),
  ('Basic verb tenses', 'future'),
  ('Basic verb tenses', 'gerund'),

  -- Complex (derived from Eldamo.org)
  ('Complex verb tenses', 'particular infinitive'),
  ('Complex verb tenses', 'consuetudinal past'),
  ('Complex verb tenses', 'present imperfect'),
  ('Complex verb tenses', 'present perfect'),
  ('Complex verb tenses', 'past continuous'),
  ('Complex verb tenses', 'past imperfect'),
  ('Complex verb tenses', 'past perfect'),
  ('Complex verb tenses', 'past future'),
  ('Complex verb tenses', 'past future perfect'),
  ('Complex verb tenses', 'long perfect'),
  ('Complex verb tenses', 'pluperfect'),
  ('Complex verb tenses', 'future imperfect'),
  ('Complex verb tenses', 'future perfect'),
  ('Complex verb tenses', 'continuative present'),
  ('Complex verb tenses', 'continuative past'),
  ('Complex verb tenses', 'draft perfect'),

  -- obscure verb tenses (derived from Eldamo.org)
  ('Obscure verb tenses', 'stative'),
  ('Obscure verb tenses', 'stative past'),
  ('Obscure verb tenses', 'stative future'),

  -- Verbal moods
  ('Verbal moods', 'imperative'),
  ('Verbal moods', 'suffixed imperative'),
  ('Verbal moods', 'subjunctive'),
  ('Verbal moods', 'impersonal'),
  ('Verbal moods', 'passive'),
  ('Verbal moods', 'reflexive'),

  ('Verbal participles', 'active participle'),
  ('Verbal participles', 'passive participle'),
  ('Verbal participles', 'imperfect participle'),
  ('Verbal participles', 'imperfect passive participle'),
  ('Verbal participles', 'perfect participle'),
  ('Verbal participles', 'perfect passive participle'),
  ('Verbal participles', 'perfective participle'),
  ('Verbal participles', 'future participle'),
  ('Verbal participles', 'future passive participle'),
  ('Verbal participles', 'reflexive participle'),

  ('Object inflections', 'with singular object'),
  ('Object inflections', 'with dual object'),
  ('Object inflections', 'with plural object'),
  ('Object inflections', 'with remote singular object'),
  ('Object inflections', 'with remote plural object'),
  ('Object inflections', 'with 1st singular object'),
  ('Object inflections', 'with 2nd plural object'),
  ('Object inflections', 'with 1st singular dative'),

  ('Subject inflections', '1st singular'),
  ('Subject inflections', '1st dual exclusive'),
  ('Subject inflections', '1st dual inclusive'),
  ('Subject inflections', '1st plural'),
  ('Subject inflections', '1st plural exclusive'),
  ('Subject inflections', '1st plural inclusive'),
  ('Subject inflections', '2nd singular'),
  ('Subject inflections', '2nd singular familiar'),
  ('Subject inflections', '2nd singular polite'),
  ('Subject inflections', '2nd singular honorific'),
  ('Subject inflections', '2nd dual'),
  ('Subject inflections', '2nd dual polite'),
  ('Subject inflections', '2nd dual honorific'),
  ('Subject inflections', '2nd plural'),
  ('Subject inflections', '2nd plural polite'),
  ('Subject inflections', '2nd plural honorific'),
  ('Subject inflections', '3rd singular'),
  ('Subject inflections', '3rd singular feminine'),
  ('Subject inflections', '3rd singular masculine'),
  ('Subject inflections', '3rd singular neuter'),
  ('Subject inflections', '3rd singular reflexive'),
  ('Subject inflections', '3rd dual'),
  ('Subject inflections', '3rd plural'),
  ('Subject inflections', '3rd plural feminine'),
  ('Subject inflections', '3rd plural masculine'),
  ('Subject inflections', '3rd plural neuter'),
  ('Subject inflections', '3rd plural reflexive'),

  ('Prepositional inflections', '1st singular preposition'),
  ('Prepositional inflections', '1st dual preposition'),
  ('Prepositional inflections', '1st plural exclusive preposition'),
  ('Prepositional inflections', '1st plural inclusive preposition'),
  ('Prepositional inflections', '2nd singular preposition'),
  ('Prepositional inflections', '2nd singular familiar preposition'),
  ('Prepositional inflections', '2nd singular polite preposition'),
  ('Prepositional inflections', '2nd plural preposition'),
  ('Prepositional inflections', '3rd singular preposition'),
  ('Prepositional inflections', '3rd singular inanimate preposition'),
  ('Prepositional inflections', '3rd singular honorific preposition'),
  ('Prepositional inflections', '3rd plural preposition'),
  ('Prepositional inflections', '3rd plural honorific preposition'),
  ('Prepositional inflections', 'definite preposition'),
  ('Prepositional inflections', 'definite plural preposition'),

  ('Possessive inflections', '1st singular possessive'),
  ('Possessive inflections', '1st plural exclusive possessive'),
  ('Possessive inflections', '1st plural inclusive possessive'),
  ('Possessive inflections', '2nd singular polite possessive'),
  ('Possessive inflections', '2nd dual possessive'),
  ('Possessive inflections', '2nd plural possessive'),
  ('Possessive inflections', '3rd singular possessive'),
  ('Possessive inflections', '3rd plural possessive'),

  ('Cases', 'accusative'),
  ('Cases', 'ablative'),
  ('Cases', 'allative'),
  ('Cases', 'dative'),
  ('Cases', 'genitive'),
  ('Cases', 'instrumental'),
  ('Cases', 'locative'),
  ('Cases', 'possessive'),

  ('Obscure cases', 's-case'),
  ('Obscure cases', 'old genitive'),
  ('Obscure cases', 'similative'),
  ('Obscure cases', 'partitive'),
  ('Obscure cases', 'objective'),
  ('Obscure cases', 'subjective'),
  ('Obscure cases', 'agental formation'),

  ('Comparative inflections', 'augmentative'),
  ('Comparative inflections', 'comparative'),
  ('Comparative inflections', 'diminutive'),
  ('Comparative inflections', 'intensive'),
  ('Comparative inflections', 'superlative'),
  ('Comparative inflections', 'diminutive superlative'),

  ('Mutations', 'soft mutation'),
  ('Mutations', 'nasal mutation'),
  ('Mutations', 'liquid mutation'),
  ('Mutations', 'stop mutation'),
  ('Mutations', 'mixed mutation'),
  ('Mutations', 'sibilant mutation'),

  ('Mutations', 'stem'),
  ('Mutations', 'assimilated'),
  ('Mutations', 'patronymic');

insert into `version` (`number`, `date`) values (1.8, NOW());
