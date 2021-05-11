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
use App\Repositories\ValueObjects\SearchIndexSearchValue;
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
    protected $_bookAdapter;

    public function __construct(DiscussRepository $discussRepository, GlossRepository $glossRepository,
        SearchIndexRepository $searchIndexRepository, BookAdapter $bookAdapter)
    {
        $this->_discussRepository     = $discussRepository;
        $this->_glossRepository       = $glossRepository;
        $this->_searchIndexRepository = $searchIndexRepository;
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

    /**
     * Ensures that the specified request to ensure that it contains the information we need to find entities
     * matching a specified criteria. The overrides is an optional parameter that allows you to override values
     * of your choice, i.e. values that may be passed through the URL path instead of the query string and request
     * payload.
     * 
     * @param Request $request 
     * @param array $overrides An associate array with key-value pairs representing overrides from the default rule set
     */
    protected function validateFindRequest(Request $request, array $overrides = []): SearchIndexSearchValue
    {
        $rules = [
            'gloss_group_ids'   => 'sometimes|array',
            'gloss_group_ids.*' => 'sometimes|numeric',
            'include_old'       => 'sometimes|boolean',
            'inflections'       => 'sometimes|boolean',
            'language'          => 'sometimes|string',
            'language_id'       => 'sometimes|numeric',
            'reversed'          => 'sometimes|boolean',
            'speech_ids'        => 'sometimes|array',
            'speech_ids.*'      => 'sometimes|numeric',
            'word'              => 'required|string'
        ];

        foreach (array_keys($overrides) as $rule) {
            unset($rules[$rule]);
        }
        $v = $request->validate($rules);
        foreach ($overrides as $rule => $value) {
            $v[$rule] = $value;
        }

        $includeOld    = isset($v['include_old']) ? boolval($v['include_old']) : true;
        $inflections   = isset($v['inflections']) ? boolval($v['inflections']) : false;
        $languageId    = isset($v['language_id']) ? intval($v['language_id']) : 0;
        $reversed      = isset($v['reversed']) ? boolval($v['reversed']) : false;
        $word          = $v['word'];

        $glossGroupIds = isset($v['gloss_group_ids']) ? array_map(function ($v) {
            return intval($v);
        }, $v['gloss_group_ids']) : null;
        $speechIds     = isset($v['speech_ids']) ? array_map(function ($v) {
            return intval($v);
        }, $v['speech_ids']) : null;

        $value = new SearchIndexSearchValue([
            'gloss_group_ids' => $glossGroupIds,
            'include_old'     => $includeOld,
            'inflections'     => $inflections,
            'language_id'     => $languageId,
            'reversed'        => $reversed,
            'speech_ids'      => $speechIds,
            'word'            => $word
        ]);

        return $value;
    }
}
