<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Adapters\BookAdapter;
use App\Helpers\StringHelper;
use App\Models\Initialization\Morphs;
use App\Models\LexicalEntry;
use App\Models\SearchKeyword;
use App\Models\Sense;
use App\Models\Word;
use App\Repositories\ConceptRepository;
use App\Repositories\DiscussRepository;
use App\Repositories\LexicalEntryInflectionRepository;
use App\Repositories\LexicalEntryRepository;
use App\Repositories\SenseTermRepository;
use App\Repositories\ValueObjects\ExternalEntitySearchValue;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Repositories\ValueObjects\SpecificEntitiesSearchValue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class GlossSearchIndexResolver implements ISearchIndexResolver
{
    private LexicalEntryRepository $_lexicalEntryRepository;

    private LexicalEntryInflectionRepository $_lexicalEntryInflectionRepository;

    private DiscussRepository $_discussRepository;

    private BookAdapter $_bookAdapter;

    private SenseTermRepository $_senseTermRepository;

    private ConceptRepository $_conceptRepository;

    private ?string $_lexicalEntryMorph;

    private ?string $_senseMorph;

    public function __construct(LexicalEntryRepository $lexicalEntryRepository, LexicalEntryInflectionRepository $lexicalEntryInflectionRepository,
        DiscussRepository $discussRepository, BookAdapter $bookAdapter, SenseTermRepository $senseTermRepository,
        ConceptRepository $conceptRepository)
    {
        $this->_lexicalEntryRepository = $lexicalEntryRepository;
        $this->_lexicalEntryInflectionRepository = $lexicalEntryInflectionRepository;
        $this->_discussRepository = $discussRepository;
        $this->_bookAdapter = $bookAdapter;
        $this->_senseTermRepository = $senseTermRepository;
        $this->_conceptRepository = $conceptRepository;

        $this->_lexicalEntryMorph = Morphs::getAlias(LexicalEntry::class);
        $this->_senseMorph = Morphs::getAlias(Sense::class);
    }

    public function resolve(SearchIndexSearchValue $value): array
    {
        $narrower = collect();
        $broader = collect();

        if ($value instanceof SpecificEntitiesSearchValue) {
            $lexicalEntries = $this->_lexicalEntryRepository->getLexicalEntries($value->getIds());

        } elseif ($value instanceof ExternalEntitySearchValue) {
            $lexicalEntries = $this->_lexicalEntryRepository->getLexicalEntriesByExternalId(
                $value->getExternalId(), $value->getLexicalEntryGroupId()
            );

        } else {
            // Sense morph is technically not supported by the search engine but there's plenty of them in the
            // database grandfathered in by the previous data model. It simply wasn't possible back in the day,
            // when the migration was implemented, to associate disassociated senses with the right lexical entry, resulting
            // in what can be best described as 'dangling' senses. These senses aren't directly tied to a word (for
            // an example, 'gold-full one' maps to 'gold') but they're still useful to retain in the index. This is why
            // the sense morph is included in the query. If you're rebuilding the database from scratch, this will not
            // do anything as it's currently not possible to create senses within the search keyword table (it'll result
            // in an exception.)
            $query = SearchKeyword::whereIn('entity_name', [$this->_lexicalEntryMorph, $this->_senseMorph])
                ->limit(500); // limit the number of results to 500 to prevent performance issues

            $normalizedWord = StringHelper::transliterate($value->getWord(), /* transformAccentsIntoLetters = */ true);
            $fulltextTerm = StringHelper::prepareQuotedFulltextTerm($normalizedWord);

            if ($value->getNaturalLanguage()) {
                $query->whereRaw('MATCH(normalized_keyword) AGAINST(? IN NATURAL LANGUAGE MODE)', [$fulltextTerm]);
            } else {
                $fulltextTerm = StringHelper::escapeFulltextUniqueSymbols($fulltextTerm);
                $query->whereRaw('MATCH(normalized_keyword) AGAINST(? IN BOOLEAN MODE)', [$fulltextTerm]);
            }

            // Check for empty search terms
            if (empty($normalizedWord) || $normalizedWord === '*') {
                $lexicalEntries = [];

            } else {

                try {
                    $entities = $query->select('entity_name', 'entity_id', 'normalized_keyword', 'is_keyword_language_invented') //
                        ->get() //
                        ->groupBy('entity_name');
                } catch (QueryException $_) {
                    $entities = collect([]);
                }

                $matched = $entities->get($this->_lexicalEntryMorph, collect());
                $directIds = $matched->pluck('entity_id')->unique();
                $senseIds = $this->sensesWorthWidening($matched, $entities->get($this->_senseMorph, collect()));

                // fulltext has no notion of plurals or compounds: "trees" finds senses glossed "tree" by headword
                $headwordSenseIds = config('senses.term_search')
                    ? $this->_senseTermRepository->senseIdsMatching($value->getWord())
                    : collect();
                $senseIds = $senseIds->merge($headwordSenseIds);

                // WordNet's name for a meaning is often no word of the dictionary: nothing is glossed "bungalow",
                // yet a Quenya word means one. Such a search can only be answered through the concept itself.
                if ($senseIds->isEmpty() && $directIds->isEmpty()) {
                    $headwordSenseIds = $this->_conceptRepository->senseIdsUnder(
                        $this->_conceptRepository->conceptIdsForLabel($this->_senseTermRepository->keysFor($value->getWord())),
                        /* inclusive = */ true
                    );
                    $senseIds = $headwordSenseIds;
                }

                // and the taxonomy widens it further: a search for trees answers with the oaks and the alders too
                $subjects = collect();
                if ($headwordSenseIds->isNotEmpty()) {
                    $subjects = $this->_conceptRepository->subjectConceptIds($headwordSenseIds->all());

                    if (config('senses.widening')) {
                        $senseIds = $senseIds->merge($this->_conceptRepository->senseIdsUnder($subjects));
                    }
                }

                $filters = [];
                if (! empty($value->getLexicalEntryGroupIds())) {
                    $filters['lexical_entry_group_id'] = $value->getLexicalEntryGroupIds();
                }
                if (! empty($value->getSpeechIds())) {
                    $filters['speech_id'] = $value->getSpeechIds();
                }

                $lexicalEntries = $this->_lexicalEntryRepository->getLexicalEntriesBySenses(
                    $senseIds->unique()->values()->all(),
                    $value->getLanguageId(),
                    $value->getIncludesOld(),
                    $filters,
                    $directIds->all()
                );

                // and both ways through the taxonomy are offered: the kinds of it, and what it is a kind of
                if (config('senses.concept_search') && $subjects->isNotEmpty()) {
                    $narrower = $this->_conceptRepository->narrowerFor($headwordSenseIds->all(),
                        (int) config('senses.concept_search_limit'));
                    $broader = $this->_conceptRepository->broaderFor($headwordSenseIds->all(),
                        (int) config('senses.broader_limit'));
                }
            }
        }

        $lexicalEntryIds = array_map(function ($v) {
            return $v->id;
        }, $lexicalEntries);

        $inflections = $value->getIncludesInflections() //
            ? $this->_lexicalEntryInflectionRepository->getInflectionsForLexicalEntries($lexicalEntryIds) //
            : collect([]);
        $comments = $this->_discussRepository->getNumberOfPostsForEntities(LexicalEntry::class, $lexicalEntryIds);

        $entities = $this->_bookAdapter->adaptLexicalEntries($lexicalEntries, $inflections, $comments, $value->getWord());
        if ($narrower->isNotEmpty()) {
            $entities['narrower'] = $narrower->values()->all();
        }

        if ($broader->isNotEmpty()) {
            $entities['broader'] = $broader->values()->all();
        }

        return $entities;
    }

    /**
     * The senses of the matches that justify widening the search. A match on a word's own spelling, or on the very
     * sense a word is glossed with, says the reader is after that meaning, so every word sharing it belongs in the
     * answer. A match on some other word of a gloss says no such thing: "Day of the Two Trees" mentions trees, but
     * what it means is a day.
     *
     * @param  Collection<int, SearchKeyword>  $entryMatches  matches against lexical entries
     * @param  Collection<int, SearchKeyword>  $senseMatches  matches against the legacy sense rows
     * @return Collection<int, int> sense IDs
     */
    private function sensesWorthWidening(Collection $entryMatches, Collection $senseMatches): Collection
    {
        $entries = LexicalEntry::whereIn('id', $entryMatches->pluck('entity_id'))
            ->with('word:id,word')
            ->get(['id', 'sense_id', 'word_id'])
            ->keyBy('id');
        $senseText = Word::whereIn('id', $entries->pluck('sense_id')->merge($senseMatches->pluck('entity_id')))
            ->pluck('word', 'id');

        $spelled = fn (?string $word, SearchKeyword $keyword) => $word !== null
            && StringHelper::transliterate($word, false) === $keyword->normalized_keyword;

        // plain collections of IDs: merging models with integers is not the same operation
        $fromEntries = collect($entryMatches
            ->filter(fn (SearchKeyword $keyword) => $entries->has($keyword->entity_id)
                && ($spelled($entries[$keyword->entity_id]->word?->word, $keyword)
                    || $spelled($senseText->get($entries[$keyword->entity_id]->sense_id), $keyword)))
            ->map(fn (SearchKeyword $keyword) => $entries[$keyword->entity_id]->sense_id)
            ->all());

        $fromSenses = collect($senseMatches
            ->filter(fn (SearchKeyword $keyword) => $spelled($senseText->get($keyword->entity_id), $keyword))
            ->map(fn (SearchKeyword $keyword) => $keyword->entity_id)
            ->all());

        return $fromEntries->merge($fromSenses)->unique()->values();
    }

    public function resolveId(int $entityId): array
    {
        $lexicalEntries = $this->_lexicalEntryRepository->getLexicalEntry($entityId)->all();
        $inflections = $this->_lexicalEntryInflectionRepository->getInflectionsForLexicalEntries([$entityId]);
        $comments = $this->_discussRepository->getNumberOfPostsForEntities(LexicalEntry::class, [$entityId]);

        return $this->_bookAdapter->adaptLexicalEntries(
            $lexicalEntries,
            $inflections,
            $comments,
            count($lexicalEntries) > 0 ? $lexicalEntries[0]->word->word : null,
        );
    }

    public function emptyResponse(): array
    {
        return $this->_bookAdapter->adaptLexicalEntries([], null, [], null);
    }
}
