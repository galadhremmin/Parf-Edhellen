# Deploying sense concepts to another copy of the dictionary

Concepts say what a sense means: *oak* is a kind of *tree*, *mallorn* sits beside it, and a search for "trees"
finds them all. Most of it is worked out by rules and can simply be run again. What cannot is the judgement
about senses the rules can't place — those decisions travel as data, in one file.

Every command below is safe to re-run: each skips what is already done.

## 0. Before you start

```bash
# the decisions file, exported from the copy where the judging happened (~6 MB, 20,821 rows)
ls -l storage/app/sense-concept-decisions.jsonl

# these three stay off until the data is in place (see step 7)
grep -E 'ED_SENSE_TERM_SEARCH|ED_SENSE_CONCEPT_WIDENING|ED_SENSE_CONCEPT_SEARCH' .env
```

`ED_SENSE_CONCEPT_JUDGE` decides whether newly contributed senses are sent to Gemini. Leave it off until the
backfill is in, or the model will be asked things the rules are about to answer for free.

## 1. The maintenance page

Run this **after** the new code is on disk and **before** migrations, so the page is rendered from the release
being deployed rather than the one being replaced.

```bash
ED_DOWN_COMING='Senses grouped by meaning: search "trees" and find mallorn|Trails through the dictionary — the kinds of a thing, and what it is a kind of' \
  sudo -u www-data php artisan down --render="errors::503" --retry=60 --with-secret
```

It prints a URL that lets you through the page while everyone else waits. Whatever runs the deploy should bring
the site back even when a step fails:

```bash
trap 'sudo -u www-data php artisan up || true' EXIT
```

`ED_DOWN_COMING` is pipe-separated and empty by default; nothing configured means no announcement, which is what
an unplanned outage should promise. See `config/ed-down.php`.

## 2. The schema

```bash
sudo -u www-data php artisan migrate --force
```

Afterwards, satisfy yourself that the schema and the ledger agree — a migration recorded as run whose column is
absent will fail at the first write, not at deploy time:

```bash
sudo -u www-data php artisan tinker --execute='var_dump(Schema::hasColumn("keywords", "identity_hash"));'
```

## 3. The reference data and the rules

```bash
# downloads Princeton WordNet 3.1 into storage/app/wordnet and loads it (~420k rows, 117,791 synsets)
sudo -u www-data php artisan ed-import:wordnet

# splits every sense in use into its terms, headword first (26,454 senses -> 39,624 terms)
sudo -u www-data php artisan ed-senses:normalize

# gives a concept to every sense the rules can place, and queues the rest for judgement
sudo -u www-data php artisan ed-senses:assign-by-rule
```

Each takes minutes and prints timestamped progress. None of them asks a model anything, so they cost nothing but
time.

## 4. The judged decisions

`sense-concept-decisions.jsonl` holds every decision a model or an editor made: what was on offer, what was
answered, how sure it was, and what came of it. Put it in `storage/app/` and replay it:

```bash
sudo -u www-data php artisan ed-senses:replay-decisions --dry-run
sudo -u www-data php artisan ed-senses:replay-decisions
```

Replaying assigns the same concepts, leaves the same senses waiting for an editor, and writes the same audit
trail, without asking any model to judge anything again. The confidence thresholds that applied when each
decision was made are honoured rather than applied afresh. A newer copy of the file can be replayed later;
senses that already have a concept are left alone.

## 5. Assets, caches and workers

```bash
sudo -u www-data npm ci && sudo -u www-data npm run production
sudo -u www-data php artisan optimize:clear   # config, routes and views, in case any were cached
sudo -u www-data php artisan queue:restart    # workers pick up the new jobs
```

`ED_VERSION` in `.env` decides the asset path, so it and the compiled assets must agree before the site comes
back.

## 6. Up

```bash
sudo -u www-data php artisan up
```

At this point the dictionary works exactly as it did before: the data is in place but nothing reads it yet.

## 7. Turn the search on, one flag at a time

Each is independent, and each is the only thing that can make search visibly worse. Set it in `.env`, run
`optimize:clear`, then try a handful of searches before going on.

| Flag | What it adds | What to search for |
| --- | --- | --- |
| `ED_SENSE_TERM_SEARCH` | plurals and compounds find their headword | `trees` finds what `tree` finds |
| `ED_SENSE_CONCEPT_WIDENING` | a search widens to the kinds of the thing | `birds` also finds lark, nightingale, woodpecker |
| `ED_SENSE_CONCEPT_SEARCH` | the chips above the glossary, both directions | `birch` offers tree, woody plant; `tree` offers its kinds |

To undo any of them, set it back to `false` and clear the cache. No data is touched either way, which is the
point of doing it in three steps.

## 8. Afterwards

```bash
sudo -u www-data php artisan cache:clear   # search results and keyword lists are cached for a day
```

`ed-senses:resolve` is scheduled daily and settles senses contributed since the export. It only asks Gemini
where the rules cannot tell, and only when `ED_SENSE_CONCEPT_JUDGE` is on; `--limit` caps what a single run can
spend.

Around 1,196 senses are left waiting for a human to choose between candidates. Nothing is broken while they
wait — they simply aren't in the taxonomy yet.

## What is decided where

| Decided by | Reproduced how |
| --- | --- |
| Rules: names, a headword's only meaning, a headword's settled meaning, untranslated senses, grammar, function words | Run `ed-senses:assign-by-rule` again |
| A model or an editor | Replay `sense-concept-decisions.jsonl` |
| Senses contributed since the file was exported | `ed-senses:resolve`, scheduled daily |

## Reading the audit trail

Every decision ever made about a sense's meaning is recorded, so an auditor can see who decided what, on what
evidence, and how sure they were.

```bash
php artisan ed-senses:decisions --limit=20
php artisan ed-senses:decisions --sense=45826      # everything ever decided about one sense
php artisan ed-senses:decisions --source=backfill  # only what the one-off judging decided
```

## Keeping the file current

On the copy where the judging happens:

```bash
php artisan ed-senses:export-decisions        # writes storage/app/sense-concept-decisions.jsonl
```
