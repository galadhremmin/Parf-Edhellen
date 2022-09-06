<?php

namespace App\Repositories\SearchIndexResolvers;

use App\Repositories\ValueObjects\{
    SearchIndexSearchValue,
    SpecificEntitiesSearchValue
};
use App\Models\Gloss;
use App\Repositories\{
    DiscussRepository,
    GlossRepository,
    SentenceRepository
};
use App\Adapters\BookAdapter;
use App\Helpers\StringHelper;
use App\Models\Initialization\Morphs;
use App\Models\SearchKeyword;

class GlossSearchIndexResolver implements ISearchIndexResolver
{
    private $_glossRepository;
    private $_sentenceRepository;
    private $_discussRepository;
    private $_bookAdapter;

    public function __construct(GlossRepository $glossRepository, SentenceRepository $sentenceRepository,
        DiscussRepository $discussRepository, BookAdapter $bookAdapter)
    {
        $this->_glossRepository       = $glossRepository;
        $this->_sentenceRepository    = $sentenceRepository;
        $this->_discussRepository     = $discussRepository;
        $this->_bookAdapter           = $bookAdapter;
    }

    public function resolve(SearchIndexSearchValue $value): array
    {
        if ($value instanceof SpecificEntitiesSearchValue) {
            $glosses = $this->_glossRepository->getGlosses($value->getIds());
        } else {
            $normalizedWord = StringHelper::normalize($value->getWord(), /* accentsMatter = */ true, /* retainWildcard = */ false);

            $query = SearchKeyword::where('entity_name', Morphs::getAlias(Gloss::class)) //
                ->where($value->getReversed() ? 'normalized_keyword_reversed' : 'normalized_keyword', $normalizedWord);

            if ($value->getLanguageId() !== 0) {
                $query = $query->where('language_id', $value->getLanguageId());
            }
            if (! $value->getIncludesOld()) {
                $query = $query->where('is_old', false);
            }

            $filters = [];
            if (! empty($value->getGlossGroupIds())) {
                $filters['gloss_group_id'] = $value->getGlossGroupIds();
            }
            if (! empty($value->getSpeechIds())) {
                $filters['speech_id'] = $value->getSpeechIds();
            }

            foreach ($filters as $column => $values) {
                $query = $query->whereIn($column, $values);
            }

            $entityIds = $query //
                ->select('entity_id') //
                ->get() //
                ->pluck('entity_id') //
                ->toArray();
            
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
            ? $this->_sentenceRepository->getInflectionsForGlosses($glossIds) //
            : [];
        $comments = $this->_discussRepository->getNumberOfPostsForEntities(Gloss::class, $glossIds);

        return $this->_bookAdapter->adaptGlosses($glosses, $inflections, $comments, $value->getWord());
    }
}
