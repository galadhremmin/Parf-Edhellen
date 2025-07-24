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

    private LexicalEntryInflectionRepository $_glossInflectionRepository;

    private DiscussRepository $_discussRepository;

    private BookAdapter $_bookAdapter;

    private ?string $_glossMorph;

    private ?string $_senseMorph;

    public function __construct(LexicalEntryRepository $glossRepository, LexicalEntryInflectionRepository $glossInflectionRepository,
        DiscussRepository $discussRepository, BookAdapter $bookAdapter)
    {
        $this->_lexicalEntryRepository = $glossRepository;
        $this->_glossInflectionRepository = $glossInflectionRepository;
        $this->_discussRepository = $discussRepository;
        $this->_bookAdapter = $bookAdapter;

        $this->_glossMorph = Morphs::getAlias(LexicalEntry::class);
        $this->_senseMorph = Morphs::getAlias(Sense::class);
    }

    public function resolve(SearchIndexSearchValue $value): array
    {
        if ($value instanceof SpecificEntitiesSearchValue) {
            $glosses = $this->_lexicalEntryRepository->getLexicalEntries($value->getIds());
        } elseif ($value instanceof ExternalEntitySearchValue) {
            $glosses = $this->_lexicalEntryRepository->getLexicalEntriesByExternalId(
                $value->getExternalId(), $value->getLexicalEntryGroupId()
            );
        } else {
            $normalizedWord = StringHelper::normalize($value->getWord(), /* accentsMatter = */ true, /* retainWildcard = */ false);

            // Sense morph is technically not supported by the search engine but there's plenty of them in the
            // database grandfathered in by the previous data model. It simply wasn't possible back in the day,
            // when the migration was implemented, to associate disassociated senses with the right gloss, resulting
            // in what can be best described as 'dangling' senses. These senses aren't directly tied to a word (for
            // an example, 'gold-full one' maps to 'gold') but they're still useful to retain in the index. This is why
            // the sense morph is included in the query. If you're rebuilding the database from scratch, this will not
            // do anything as it's currently not possible to create senses within the search keyword table (it'll result
            // in an exception.)
            $entities = SearchKeyword::whereIn('entity_name', [$this->_glossMorph, $this->_senseMorph]) //
                ->where($value->getReversed() ? 'normalized_keyword_reversed' : 'normalized_keyword', $normalizedWord) //
                ->select('entity_name', 'entity_id') //
                ->get() //
                ->groupBy('entity_name');

            $entityIds = [];

            // Senses are *not* supported by the search index, so with this shim, the 'sense' is resolved to
            // whatever lexical entry it might be associated with. This ensures that all relevant lexical entries are found.
            if ($entities->has($this->_senseMorph)) {
                // we've got the sense, now obtain lexical entries
                $entityIds = LexicalEntry::whereIn('sense_id', $entities[$this->_senseMorph]->pluck('entity_id')) //
                    ->pluck('id')
                    ->all();
            }

            if ($entities->has($this->_glossMorph)) {
                $entityIds = array_merge(
                    $entityIds,
                    $entities[$this->_glossMorph]->pluck('entity_id')->all()
                );
            }

            $filters = [];
            if (! empty($value->getLexicalEntryGroupIds())) {
                $filters['lexical_entry_group_id'] = $value->getLexicalEntryGroupIds();
            }
            if (! empty($value->getSpeechIds())) {
                $filters['speech_id'] = $value->getSpeechIds();
            }

            $glosses = $this->_lexicalEntryRepository->getLexicalEntriesByExpandingViaSense(
                $entityIds,
                $value->getLanguageId(),
                $value->getIncludesOld(),
                $filters
            );
        }

        $glossIds = array_map(function ($v) {
            return $v->id;
        }, $glosses);

        $inflections = $value->getIncludesInflections() //
            ? $this->_glossInflectionRepository->getInflectionsForLexicalEntries($glossIds) //
            : collect([]);
        $comments = $this->_discussRepository->getNumberOfPostsForEntities(LexicalEntry::class, $glossIds);

        return $this->_bookAdapter->adaptLexicalEntries($glosses, $inflections, $comments, $value->getWord());
    }

    public function resolveId(int $entityId): array
    {
        $glosses = $this->_lexicalEntryRepository->getLexicalEntry($entityId)->all();
        $inflections = $this->_glossInflectionRepository->getInflectionsForLexicalEntries([$entityId]);
        $comments = $this->_discussRepository->getNumberOfPostsForEntities(LexicalEntry::class, [$entityId]);

        return $this->_bookAdapter->adaptLexicalEntries($glosses, $inflections, $comments, count($glosses) > 0 ? $glosses[0]->word->word : null);
    }
}
