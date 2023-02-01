<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Repositories\ValueObjects\{
    ExternalEntitySearchValue,
    SearchIndexSearchValue,
    SpecificEntitiesSearchValue
};
use App\Models\Gloss;
use App\Repositories\{
    DiscussRepository,
    GlossInflectionRepository,
    GlossRepository
};
use App\Adapters\BookAdapter;
use App\Helpers\StringHelper;
use App\Models\Initialization\Morphs;
use App\Models\{
    SearchKeyword,
    Sense
};
use DB;

class GlossSearchIndexResolver implements ISearchIndexResolver
{
    private $_glossRepository;
    private $_glossInflectionRepository;
    private $_discussRepository;
    private $_bookAdapter;

    private $_glossMorph;
    private $_senseMorph;

    public function __construct(GlossRepository $glossRepository, GlossInflectionRepository $glossInflectionRepository,
        DiscussRepository $discussRepository, BookAdapter $bookAdapter)
    {
        $this->_glossRepository           = $glossRepository;
        $this->_glossInflectionRepository = $glossInflectionRepository;
        $this->_discussRepository         = $discussRepository;
        $this->_bookAdapter               = $bookAdapter;

        $this->_glossMorph = Morphs::getAlias(Gloss::class);
        $this->_senseMorph = Morphs::getAlias(Sense::class);
    }

    public function resolve(SearchIndexSearchValue $value): array
    {
        if ($value instanceof SpecificEntitiesSearchValue) {
            $glosses = $this->_glossRepository->getGlosses($value->getIds());
        } else if ($value instanceof ExternalEntitySearchValue) {
            $glosses = $this->_glossRepository->getGlossesByExternalId(
                $value->getExternalId(), $value->getGlossGroupId()
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
            // whatever gloss it might be associated with. This ensures that all relevant glosses are found.
            if ($entities->has($this->_senseMorph)) {
                // we've got the sense, now obtain glosses
                $entityIds = Gloss::whereIn('sense_id', $entities[$this->_senseMorph]->pluck('entity_id')) //
                    ->select('id')
                    ->get()
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
            if (! empty($value->getGlossGroupIds())) {
                $filters['gloss_group_id'] = $value->getGlossGroupIds();
            }
            if (! empty($value->getSpeechIds())) {
                $filters['speech_id'] = $value->getSpeechIds();
            }

            $glosses = $this->_glossRepository->getGlossesByExpandingViaSense(
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
            ? $this->_glossInflectionRepository->getInflectionsForGlosses($glossIds) //
            : collect([]);
        $comments = $this->_discussRepository->getNumberOfPostsForEntities(Gloss::class, $glossIds);
        return $this->_bookAdapter->adaptGlosses($glosses, $inflections, $comments, $value->getWord());
    }
}
