<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Adapters\BookAdapter;
use App\Helpers\StringHelper;
use App\Models\LexicalEntry;
use App\Models\Initialization\Morphs;
use App\Models\SearchKeyword;
use App\Models\Sense;
use App\Repositories\DiscussRepository;
use App\Repositories\LexicalEntryInflectionRepository;
use App\Repositories\LexicalEntryRepository;
use App\Repositories\ValueObjects\ExternalEntitySearchValue;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use App\Repositories\ValueObjects\SpecificEntitiesSearchValue;

class GlossSearchIndexResolver implements ISearchIndexResolver
{
    private LexicalEntryRepository $_lexicalEntryRepository;

    private LexicalEntryInflectionRepository $_lexicalEntryInflectionRepository;

    private DiscussRepository $_discussRepository;

    private BookAdapter $_bookAdapter;

    private ?string $_lexicalEntryMorph;

    private ?string $_senseMorph;

    public function __construct(LexicalEntryRepository $lexicalEntryRepository, LexicalEntryInflectionRepository $lexicalEntryInflectionRepository,
        DiscussRepository $discussRepository, BookAdapter $bookAdapter)
    {
        $this->_lexicalEntryRepository = $lexicalEntryRepository;
        $this->_lexicalEntryInflectionRepository = $lexicalEntryInflectionRepository;
        $this->_discussRepository = $discussRepository;
        $this->_bookAdapter = $bookAdapter;

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
            $searchColumn = $value->getReversed() ? 'normalized_keyword_reversed' : 'normalized_keyword';
            $query = SearchKeyword::whereIn('entity_name', [$this->_lexicalEntryMorph, $this->_senseMorph])
                ->limit(500); // limit the number of results to 500 to prevent performance issues

            if ($value->getNaturalLanguage()) {
                $normalizedWord = StringHelper::normalize($value->getWord(), /* accentsMatter = */ true, /* retainWildcard = */ false);
                $query->whereRaw('MATCH(' . $searchColumn . ') AGAINST(? IN NATURAL LANGUAGE MODE)', [$normalizedWord]);
            } else {
                // Use FULLTEXT BOOLEAN MODE for prefix matching - much faster than LIKE queries
                $normalizedWord = StringHelper::normalize($value->getWord(), /* accentsMatter = */ true, /* retainWildcard = */ false);
                $fulltextTerm = StringHelper::prepareFulltextBooleanTerm($normalizedWord);
                $query->whereRaw('MATCH(' . $searchColumn . ') AGAINST(? IN BOOLEAN MODE)', [$fulltextTerm]);
            }

            // Check for empty search terms
            if (empty($normalizedWord) || $normalizedWord === '*') {
                $lexicalEntries = [];

            } else {
                
                $entities = $query->select('entity_name', 'entity_id') //
                    ->get() //
                    ->groupBy('entity_name');
                
                if ($value->getNaturalLanguage()) {
                    $query->where('entity_name', $this->_lexicalEntryMorph);
                }

                $entityIds = [];

                // Senses are *not* supported by the search index, so with this shim, the 'sense' is resolved to
                // whatever lexical entry it might be associated with. This ensures that all relevant lexical entries are found.
                if ($entities->has($this->_senseMorph)) {
                    // we've got the sense, now obtain lexical entries
                    $entityIds = LexicalEntry::whereIn('sense_id', $entities[$this->_senseMorph]->pluck('entity_id')) //
                        ->pluck('id')
                        ->all();
                }

                if ($entities->has($this->_lexicalEntryMorph)) {
                    $entityIds = array_merge(
                        $entityIds,
                        $entities[$this->_lexicalEntryMorph]->pluck('entity_id')->all()
                    );
                }

                $filters = [];
                if (! empty($value->getLexicalEntryGroupIds())) {
                    $filters['lexical_entry_group_id'] = $value->getLexicalEntryGroupIds();
                }
                if (! empty($value->getSpeechIds())) {
                    $filters['speech_id'] = $value->getSpeechIds();
                }

                $lexicalEntries = $this->_lexicalEntryRepository->getLexicalEntriesByExpandingViaSense(
                    $entityIds,
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
