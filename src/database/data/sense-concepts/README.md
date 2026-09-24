# Bringing sense concepts to another copy of the dictionary

Concepts say what a sense means: *oak* is a kind of *tree*, *mallorn* sits beside it, and a search for "trees"
can find them all. Most of that is worked out by rules and can simply be run again. What cannot is the judgement
about senses the rules can't place — those decisions travel as data, in one file.

Everything below is safe to re-run: each command skips what is already done.

## 1. The schema

```bash
sudo -u www-data php artisan migrate --force
```

## 2. The reference data and the rules

```bash
# downloads Princeton WordNet 3.1 into storage/app/wordnet and loads it (~420k rows)
sudo -u www-data php artisan ed-import:wordnet

# splits every sense in use into its terms (headword first)
sudo -u www-data php artisan ed-senses:normalize

# gives a concept to every sense the rules can place, and queues the rest
sudo -u www-data php artisan ed-senses:assign-by-rule
```

Each takes minutes and prints timestamped progress. `ed-senses:assign-by-rule` asks no model, so it costs nothing
but time.

## 3. The judged decisions

`sense-concept-decisions.jsonl` holds every decision a model or an editor made: what was on offer, what was
answered, how sure it was, and what came of it. Put it in `storage/app/` and replay it:

```bash
sudo -u www-data php artisan ed-senses:replay-decisions --dry-run
sudo -u www-data php artisan ed-senses:replay-decisions
```

Replaying assigns the same concepts, leaves the same senses waiting for an editor, and writes the same audit
trail, without asking any model to judge anything again. The confidence thresholds that applied when each
decision was made are honoured rather than applied afresh.

A newer copy of the file can be replayed later; senses that already have a concept are left alone.

## 4. Afterwards

```bash
sudo -u www-data php artisan queue:restart   # the workers pick up the new jobs
sudo -u www-data php artisan cache:clear     # search results and keyword lists are cached for a day
```

## Keeping the file current

On the copy where the judging happens:

```bash
php artisan ed-senses:export-decisions        # writes storage/app/sense-concept-decisions.jsonl
```

## What is decided where

| Decided by | Reproduced how |
| --- | --- |
| Rules: names, a headword's only meaning, a headword's settled meaning, untranslated senses, grammar, function words | Run `ed-senses:assign-by-rule` again |
| A model or an editor | Replay `sense-concept-decisions.jsonl` |
| Senses contributed since the file was exported | `ed-senses:resolve`, scheduled daily, which asks Gemini only where the rules cannot tell (`ED_SENSE_CONCEPT_JUDGE`) |

## Reading the audit trail

```bash
php artisan ed-senses:decisions --limit=20
php artisan ed-senses:decisions --sense=45826      # everything ever decided about one sense
php artisan ed-senses:decisions --source=backfill  # only what the one-off judging decided
```
