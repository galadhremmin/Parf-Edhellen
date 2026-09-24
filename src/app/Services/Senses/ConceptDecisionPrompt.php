<?php

namespace App\Services\Senses;

use App\Models\Gloss;
use App\Models\LexicalEntry;
use App\Models\Sense;
use Illuminate\Support\Collection;

/**
 * Asks a model which WordNet meaning a sense has. Gemini (for contributions) and the one-off backfill use this same
 * request, prompt and schema, so a sense is judged the same way whoever judges it.
 */
class ConceptDecisionPrompt
{
    // enough entries to show how a sense is used without drowning the candidates
    private const ENTRIES_PER_SENSE = 4;

    // how many of an entry's remaining glosses to show
    private const GLOSSES_PER_ENTRY = 3;

    public function __construct(
        protected readonly ConceptCandidateFinder $_finder,
        protected readonly SenseClassifier $_classifier,
    ) {}

    /**
     * The request for senses that share a headword. Their candidates are merged: entries' parts of speech can let
     * one sense consider meanings another can't.
     *
     * @param  Collection<int, Sense>  $senses  with `word`, `terms` and `lexical_entries` loaded
     */
    public function request(Collection $senses): ConceptDecisionRequest
    {
        $lookups = $senses->map(fn (Sense $sense) => $this->_finder->lookUp($sense));
        $first = $lookups->first();

        return new ConceptDecisionRequest(
            $first->lookedUp,
            $first->viaPhraseHead,
            $this->_finder->describe($lookups->flatMap(fn (CandidateLookup $lookup) => $lookup->synsetIds)->unique()->values()),
            $this->evidence($senses),
        );
    }

    /**
     * The prompt for a batch of requests.
     *
     * @param  Collection<int, ConceptDecisionRequest>  $requests
     */
    public function prompt(Collection $requests): string
    {
        $groups = $requests->map(fn (ConceptDecisionRequest $request) => $this->group($request))->implode("\n\n");

        return $this->instructions()."\n\n".$groups;
    }

    /**
     * What is being asked and how to answer it, without any groups: the opening of a prompt, or of a file of them.
     */
    public function instructions(): string
    {
        return <<<'PROMPT'
        You are sorting the English senses of a dictionary of J.R.R. Tolkien's invented languages into WordNet meanings.
        A sense is the English a word of Quenya, Sindarin or another of Tolkien's languages is glossed with, often a
        list of near-synonyms ("gate, door"). The senses below come in groups that share a headword; each group lists
        the WordNet meanings that headword can have, with the part of speech of each.

        For every sense:
        - Give the meaning it has as synset_ids, judging from the sense's own words and from the entries that use it:
          their language, part of speech and glosses. "light, not heavy" and "light, radiance" share a headword but
          not a meaning, and a candidate whose part of speech the sense cannot have is never the answer.
        - A sense that really covers two meanings ("(day)light; candle") takes both, the main one first. Otherwise
          give one.
        - When a group's candidates belong to the head of a phrase, choose the meaning the phrase is a kind of:
          "mouth of a river" is a kind of mouth (the opening), not a synonym of it.
        - When no candidate fits, leave synset_ids empty and give better_word: one plain English word for the
          meaning, with relation saying how it stands to the sense:
          - "synonym" when the word means the same thing, as "bright" does for "light, bright, sunny".
          - "kind_of" when the sense is a kind of that word, as "mallorn" is a kind of "tree" and "athelas" of "herb".
        - Leave synset_ids empty and better_word null when the sense is not a meaning at all: a name, an Elvish word
          left untranslated, a grammatical label.
        - Answer only from what each sense and its entries say here. Where they give no English meaning, answer null
          rather than drawing on what you know of Tolkien's languages.
        - confidence is 0-100: how sure you are that another careful reader would answer the same.

        Answer with one decision per sense, using the sense IDs given.
        PROMPT;
    }

    /**
     * One headword group as the prompt shows it.
     */
    public function renderGroup(ConceptDecisionRequest $request): string
    {
        return $this->group($request);
    }

    /**
     * The JSON schema answers must follow.
     *
     * @return array<string, mixed>
     */
    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'decisions' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'sense_id' => ['type' => 'integer'],
                            // the meanings it has, the main one first; empty when no candidate fits
                            'synset_ids' => ['type' => 'array', 'items' => ['type' => 'string']],
                            // one English word for the meaning, when no candidate fits
                            'better_word' => ['type' => 'string', 'nullable' => true],
                            'relation' => ['type' => 'string', 'enum' => ['synonym', 'kind_of'], 'nullable' => true],
                            'confidence' => ['type' => 'integer'],
                        ],
                        'required' => ['sense_id', 'synset_ids', 'better_word', 'relation', 'confidence'],
                    ],
                ],
            ],
            'required' => ['decisions'],
        ];
    }

    /**
     * One group as the prompt shows it: the headword, its candidates, then its senses.
     */
    private function group(ConceptDecisionRequest $request): string
    {
        $heading = $request->viaPhraseHead
            ? "## Headword: phrases about \"{$request->headword}\" (candidates are meanings of \"{$request->headword}\")"
            : "## Headword: \"{$request->headword}\"";

        $candidates = $request->candidates->isEmpty()
            ? '(no WordNet candidates)'
            : $request->candidates->map(fn (ConceptCandidate $candidate) => sprintf('- %s %s (%s) [%s]%s: %s%s',
                $candidate->synsetId,
                $candidate->label,
                $candidate->pos->label(),
                $candidate->lexname,
                $candidate->synonyms ? ' (also '.implode(', ', $candidate->synonyms).')' : '',
                $candidate->definition,
                $candidate->lineage ? '; a kind of '.implode(' < ', $candidate->lineage) : '',
            ))->implode("\n");

        $senses = $request->senses->map(fn (SenseEvidence $sense) => sprintf("- sense %d%s: \"%s\"\n%s",
            $sense->senseId,
            $sense->speeches ? ' ('.implode(', ', $sense->speeches).')' : '',
            $sense->sense,
            collect($sense->entries)->map(fn (string $entry) => '  - '.$entry)->implode("\n"),
        ))->implode("\n");

        return "{$heading}\nCandidates:\n{$candidates}\nSenses:\n{$senses}";
    }

    /**
     * Each sense with the first few entries that use it: language, word, part of speech and glosses.
     *
     * @param  Collection<int, Sense>  $senses
     * @return Collection<int, SenseEvidence>
     */
    private function evidence(Collection $senses): Collection
    {
        $entries = LexicalEntry::active()
            ->whereIn('sense_id', $senses->pluck('id'))
            ->with(['word:id,word', 'language:id,name', 'speech:id,name', 'glosses:id,lexical_entry_id,translation'])
            ->get()
            ->groupBy('sense_id');

        return $senses->map(fn (Sense $sense) => new SenseEvidence(
            $sense->id,
            $sense->word->word,
            $this->_classifier->speechesOf($sense)->unique()->values()->all(),
            $entries->get($sense->id, collect())
                ->take(self::ENTRIES_PER_SENSE)
                ->map(fn (LexicalEntry $entry) => rtrim(sprintf('%s %s%s: %s',
                    $entry->language?->name ?? 'unknown language',
                    $entry->word->word,
                    $entry->speech ? ' ('.$entry->speech->name.')' : '',
                    // drops the glosses that only restate the sense, leaving the ones that add to it
                    $entry->glosses
                        ->map(fn (Gloss $gloss) => trim($gloss->translation))
                        ->reject(fn (string $gloss) => mb_strtolower($gloss) === mb_strtolower(trim($sense->word->word)))
                        ->unique()
                        ->take(self::GLOSSES_PER_ENTRY)
                        ->implode('; '),
                ), ': '))
                ->values()
                ->all(),
        ))->values();
    }
}
