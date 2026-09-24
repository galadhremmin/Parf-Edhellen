<?php

namespace App\Services\Senses;

use App\Models\Sense;
use App\Models\SenseTerm;
use App\Models\WordNetSynset;
use App\Repositories\WordNetRepository;
use App\Services\Enumerations\WordNetPos;
use Illuminate\Support\Collection;

/**
 * Finds the WordNet meanings a sense could have. Both rules and the models that judge the ambiguous senses start
 * from here, so they choose between the same candidates.
 */
class ConceptCandidateFinder
{
    // what a candidate is a kind of, a few levels up: enough to tell a tree from timber
    private const LINEAGE_DEPTH = 4;

    private const SYNONYMS = 4;

    // a phrase turns on the first of these: "clearing in forest" is a clearing, not a forest
    private const PREPOSITIONS = ['of', 'in', 'on', 'at', 'by', 'for', 'from', 'with', 'to', 'into', 'upon', 'over', 'under', 'about', 'against', 'among', 'between', 'through', 'without'];

    // a common word has many meanings; enough that the right one is nearly always among them
    private const CANDIDATES = 12;

    public function __construct(
        protected readonly WordNetRepository $_wordNet,
        protected readonly SenseClassifier $_classifier,
    ) {}

    /**
     * Looks up the headword in WordNet; a phrase WordNet doesn't know as a whole is looked up by its head.
     *
     * @param  Sense  $sense  with `terms` and `lexical_entries` loaded
     */
    public function lookUp(Sense $sense): CandidateLookup
    {
        $head = $sense->terms->first();
        $pos = $this->pos($sense, $head);

        $synsetIds = $this->_wordNet->synsetIdsFor($this->spellings($head->lemma), $pos);
        if ($synsetIds->isNotEmpty() || ! $this->isPhrase($head->lemma)) {
            return new CandidateLookup($synsetIds, $head->lemma, false);
        }

        $phraseHead = $this->phraseHead($head->lemma, $head->is_verb);

        return new CandidateLookup($this->_wordNet->synsetIdsFor([$phraseHead], $pos), $phraseHead, true);
    }

    /**
     * The first few candidates, described for choosing between them.
     *
     * @param  Collection<int, string>  $synsetIds
     * @return Collection<int, ConceptCandidate>
     */
    public function describe(Collection $synsetIds, int $limit = self::CANDIDATES): Collection
    {
        return $synsetIds->take($limit)->map(function (string $synsetId) {
            $lineage = $this->_wordNet->lineage($synsetId);
            /** @var WordNetSynset $synset */
            $synset = $lineage->first();

            return new ConceptCandidate(
                $synsetId,
                $synset->label,
                WordNetPos::from($synset->pos),
                $synset->lexname,
                $synset->definition,
                $synset->senses()->orderByDesc('tag_count')->limit(self::SYNONYMS + 1)->pluck('lemma')
                    ->reject(fn (string $lemma) => $lemma === mb_strtolower($synset->label))
                    ->take(self::SYNONYMS)->values()->all(),
                $lineage->skip(1)->take(self::LINEAGE_DEPTH)->pluck('label')->values()->all(),
            );
        })->values();
    }

    /**
     * The meaning a sense has without any judgement: the headword's only candidate, when that candidate is named
     * after the headword and is a kind of thing rather than one particular thing. WordNet's only "fair-haired" means
     * favourite and its only "wi" is Wisconsin; those go to judgement instead.
     */
    public function selfEvident(CandidateLookup $lookup): ?string
    {
        if ($lookup->viaPhraseHead || $lookup->synsetIds->count() !== 1) {
            return null;
        }

        $synsetId = $lookup->synsetIds->first();
        $label = WordNetSynset::findOrFail($synsetId)->label;
        if ($this->compact($label) !== $this->compact($lookup->lookedUp) || $this->_wordNet->isInstance($synsetId)) {
            return null;
        }

        return $synsetId;
    }

    /**
     * Verbs are looked up as verbs; anything else by what its entries' parts of speech allow, or as anything when
     * they don't say.
     *
     * @return WordNetPos[]
     */
    private function pos(Sense $sense, SenseTerm $head): array
    {
        if ($head->is_verb) {
            return [WordNetPos::VERB];
        }

        return $this->_classifier->wordNetPos($sense) ?? WordNetPos::cases();
    }

    /**
     * Lower case, without spaces or hyphens: "Oak-tree" and "oak tree" compare equal.
     */
    private function compact(string $word): string
    {
        // drop spaces and hyphens
        return preg_replace('/[\s\-]+/u', '', mb_strtolower($word));
    }

    /**
     * WordNet may write a compound with a space, a hyphen or neither: oak tree, oak-tree, oaktree.
     *
     * @return string[]
     */
    private function spellings(string $lemma): array
    {
        return array_values(array_unique([
            $lemma,
            str_replace('-', ' ', $lemma),
            $this->compact($lemma),
        ]));
    }

    /**
     * Whether the lemma has more than one word, counting the halves of a compound: "star-queen".
     */
    private function isPhrase(string $lemma): bool
    {
        return count($this->words($lemma)) > 1;
    }

    /**
     * The word a phrase is about: a verb phrase's first word ("gobble up"), the noun a preposition hangs off ("mouth
     * of a river", "clearing in forest"), otherwise the last word ("great towering building", "star-queen").
     */
    private function phraseHead(string $phrase, bool $isVerb): string
    {
        if ($isVerb) {
            return $this->words($phrase)[0];
        }

        // everything up to the first preposition: what follows it describes the head, it isn't the head
        $beforePreposition = preg_split('/\s(?:'.implode('|', self::PREPOSITIONS).')\s/u', $phrase, 2)[0];
        $words = $this->words($beforePreposition);

        return end($words);
    }

    /**
     * The words of a phrase, taking a hyphenated compound apart: "star-queen" is "star" and "queen".
     *
     * @return string[]
     */
    private function words(string $phrase): array
    {
        // split on spaces and hyphens
        return preg_split('/[\s\-]+/u', $phrase, -1, PREG_SPLIT_NO_EMPTY);
    }
}
