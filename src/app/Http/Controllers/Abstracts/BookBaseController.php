<?php

namespace App\Http\Controllers\Abstracts;

use Illuminate\Http\Request;
use Cache;

use App\Helpers\StringHelper;
use App\Repositories\{
    DiscussRepository,
    GlossRepository,
    SearchIndexRepository,
    SentenceRepository
};
use App\Adapters\BookAdapter;
use App\Models\{
    Keyword,
    Gloss, 
    GlossGroup, 
    Language,
    Word
};

abstract class BookBaseController extends Controller 
{
    protected $_discussRepository;
    protected $_glossRepository;
    protected $_searchIndexRepository;
    protected $_sentenceRepository;
    protected $_bookAdapter;

    public function __construct(DiscussRepository $discussRepository, GlossRepository $glossRepository,
        SearchIndexRepository $searchIndexRepository, SentenceRepository $sentenceRepository, BookAdapter $bookAdapter)
    {
        $this->_discussRepository     = $discussRepository;
        $this->_glossRepository       = $glossRepository;
        $this->_searchIndexRepository = $searchIndexRepository;
        $this->_sentenceRepository    = $sentenceRepository;
        $this->_bookAdapter           = $bookAdapter;
    }

    /**
     * Gets the gloss with the specified ID. The gloss is also adapted for the immediate
     * use as a view model.
     *
     * @param int $glossId
     * @param bool $coerceLatest
     * @return void
     */
    protected function getGloss(int $glossId, bool $coerceLatest = false)
    {
        $glosses = $this->getGlossUnadapted($glossId, $coerceLatest);
        if ($glosses === null) {
            return null;
        }

        $gloss = $glosses->first();
        $comments = $this->_discussRepository->getNumberOfPostsForEntities(Gloss::class, [$glossId]);
        return $this->_bookAdapter->adaptGlosses($glosses->toArray(), [/* no inflections */], $comments, $gloss->word);
    }

    /**
     * Gets the gloss with the specified ID. As there might be multiple translations
     * associated with the specified gloss, this method might return multiple glosses. 
     *
     * @param int $glossId
     * @param bool $coerceLatest
     * @return void
     */
    protected function getGlossUnadapted(int $glossId, bool $coerceLatest = false)
    {
        $glosses = $this->_glossRepository->getGlossVersion($glossId);
        if ($glosses->count() < 1) {
            return null;
        }

        $gloss = $glosses->first();
        if (! $gloss->is_latest && $coerceLatest) {
            $glossId = $this->_glossRepository->getLatestGloss($gloss->origin_gloss_id ?: $gloss->id);
            return $this->getGlossUnadapted($glossId, false);
        }

        return $glosses;
    }

    protected function validateGetGlossConfiguration(Request $request)
    {
        return $this->validate($request, [
            'gloss_group_ids'   => 'sometimes|array',
            'gloss_group_ids.*' => 'sometimes|numeric',
            'include_old'       => 'sometimes|boolean',
            'language_id'       => 'sometimes|numeric',
            'speech_ids'        => 'sometimes|array',
            'speech_ids.*'      => 'sometimes|numeric'
        ]);
    }

    protected function findGlosses(string $word, int $languageId = 0, bool $includeInflections = true, bool $includeOld = true,
        array $speechIds = null, array $glossGroupIds = null)
    {
        $glosses = $this->_glossRepository->getWordGlosses($word, $languageId, $includeOld, $speechIds, $glossGroupIds);
        $glossIds = array_map(function ($v) {
            return $v->id;
        }, $glosses);

        $inflections = $includeInflections
            ? $this->_sentenceRepository->getInflectionsForGlosses($glossIds)
            : [];
        $comments = $this->_discussRepository->getNumberOfPostsForEntities(Gloss::class, $glossIds);
        
        return $this->_bookAdapter->adaptGlosses($glosses, $inflections, $comments, $word);
    }
}
