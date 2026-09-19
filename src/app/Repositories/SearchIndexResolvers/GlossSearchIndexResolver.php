<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Adapters\BookAdapter;
use App\Helpers\StringHelper;
use App\Models\Initialization\Morphs;
use App\Models\LexicalEntry;
use App\Models\SearchKeyword;
use App\Models\Sense;
use App\Repositories\DiscussRepository;
use App\Repositories\LexicalEntryInflectionRepository;
use App\Repositories\LexicalEntryRepository;
use App\Repositories\SenseTermRepository;
use App\Repositories\ValueObjects\ExternalEntitySearchValue;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Repositories\ValueObjects\SpecificEntitiesSearchValue;
use Illuminate\Database\QueryException;

class GlossSearchIndexResolver implements ISearchIndexResolver
{
    private LexicalEntryRepository $_lexicalEntryRepository;

    private LexicalEntryInflectionRepository $_lexicalEntryInflectionRepository;

    private DiscussRepository $_discussRepository;

    private BookAdapter $_bookAdapter;

    private SenseTermRepository $_senseTermRepository;

    private ?string $_lexicalEntryMorph;

    private ?string $_senseMorph;

    public function __construct(LexicalEntryRepository $lexicalEntryRepository, LexicalEntryInflectionRepository $lexicalEntryInflectionRepository,
        DiscussRepository $discussRepository, BookAdapter $bookAdapter, SenseTermRepository $senseTermRepository)
    {
        $this->_lexicalEntryRepository = $lexicalEntryRepository;
        $this->_lexicalEntryInflectionRepository = $lexicalEntryInflectionRepository;
        $this->_discussRepository = $discussRepository;
        $this->_bookAdapter = $bookAdapter;
        $this->_senseTermRepository = $senseTermRepository;

        $this->_lexicalEntryMorph = Morphs::getAlias(LexicalEntry::class);
        $this->_senseMorph = Morphs::getAlias(Sense::class);
    }

    public function resolve(SearchIndexSearchValue $value): array
    {
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
                    $entities = $query->select('entity_name', 'entity_id') //
                        ->get() //
                        ->groupBy('entity_name');
                } catch (QueryException $_) {
                    $entities = collect([]);
                }

                $senseIds = collect();

                // The legacy sense rows described above index a sense directly: their entity ID is already a sense ID.
                if ($entities->has($this->_senseMorph)) {
                    $senseIds = $entities[$this->_senseMorph]->pluck('entity_id');
                }

                if ($entities->has($this->_lexicalEntryMorph)) {
                    $senseIds = $senseIds->merge(
                        LexicalEntry::whereIn('id', $entities[$this->_lexicalEntryMorph]->pluck('entity_id'))->pluck('sense_id')
                    );
                }

                // fulltext has no notion of plurals or compounds: "trees" finds senses glossed "tree" by headword
                if (config('ed.sense_term_search')) {
                    $senseIds = $senseIds->merge($this->_senseTermRepository->senseIdsMatching($value->getWord()));
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
                    $filters
                );
            }
        }

        $lexicalEntryIds = array_map(function ($v) {
            return $v->id;
        }, $lexicalEntries);

        $inflections = $value->getIncludesInflections() //
            ? $this->_lexicalEntryInflectionRepository->getInflectionsForLexicalEntries($lexicalEntryIds) //
            : collect([]);
        $comments = $this->_discussRepository->getNumberOfPostsForEntities(LexicalEntry::class, $lexicalEntryIds);

        return $this->_bookAdapter->adaptLexicalEntries($lexicalEntries, $inflections, $comments, $value->getWord());
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
