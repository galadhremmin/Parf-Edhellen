<?php

namespace App\Http\Controllers\Api\v2;

use App\Helpers\StringHelper;
use App\Http\Controllers\Abstracts\BookBaseController;
use App\Models\GlossGroup;
use App\Models\Language;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookApiController extends BookBaseController
{
    /**
     * HTTP GET. Gets the word which corresponds to the specified ID.
     *
     * @return void
     */
    public function getWord(Request $request, int $id)
    {
        $word = Word::find($id);
        if (! $word) {
            return response(null, 404);
        }

        return $word;
    }

    public function getLanguages()
    {
        $languages = Cache::remember('ed.languages', 60 * 60 /* = 1 hour */, function () {
            return Language::all()
                ->sortBy('order')
                ->sortBy('name')
                ->groupBy('category')
                ->toArray();
        });

        return $languages;
    }

    /**
     * HTTP GET. Gets all available gloss groups.
     *
     * @return void
     */
    public function getGroups(Request $request)
    {
        return GlossGroup::orderBy('name')->get();
    }

    /**
     * HTTP POST. Performs a forward search among words for the specified word parameter.
     *
     * @return void
     */
    public function findWord(Request $request)
    {
        $this->validate($request, [
            'word' => 'required|string|max:64',
            'max' => 'sometimes|numeric|min:1',
        ]);

        $normalizedWord = StringHelper::normalize($request->input('word'));
        $max = intval($request->input('max'));

        $query = Word::where('normalized_word', 'like', $normalizedWord.'%');

        if ($max > 0) {
            $query = $query->take($max);
        }

        return $query->select('id', 'word')->get();
    }

    /**
     * HTTP POST. Finds keywords for the specified word.
     *
     * @return void
     */
    public function find(Request $request)
    {
        $v = $this->validateFindRequest($request);
        $keywords = $this->_searchIndexRepository->findKeywords($v);

        // Create a key-value pair that maps group ID (integers) to a human readable, internationalized format.
        $locale = $request->getLocale();
        $searchGroups = Cache::remember('ed.search-groups.'.$locale, 60 * 60 /* = 1 hour */, function () {
            $config = config('ed.book_entities');
            $entities = array_values($config);

            return array_reduce($entities, function ($carry, $entity) {
                $carry[intval($entity['group_id'])] = __('entities.'.$entity['intl_name']);

                return $carry;
            }, []);
        });

        return [
            'keywords' => $keywords,
            'search_groups' => $searchGroups,
        ];
    }

    /**
     * HTTP POST. Finds entitites corresponding to a specified keyword.
     */
    public function entities(Request $request, int $groupId, $entityId = 0): array
    {
        $entities = [];
        if ($entityId !== 0) {
            $entities = $this->getEntity($groupId, $entityId);
        } else {
            $entities = $this->findMatchingEntities($request, $groupId);
        }

        return $entities;
    }

    /**
     * HTTP GET. Gets the gloss corresponding to the specified ID.
     *
     * @return void
     */
    public function get(Request $request, int $glossId)
    {
        $gloss = $this->getGloss($glossId);
        if (! $gloss) {
            return response(null, 404);
        }

        return $gloss;
    }

    public function getFromVersion(Request $request, int $id)
    {
        $gloss = $this->_glossRepository->getSpecificGlossVersion($id);

        return $this->_bookAdapter->adaptGlosses([$gloss], null, [], $gloss->word->word);
    }

    /**
     * Finds the matching entities with the search query parameters in the request.
     */
    private function findMatchingEntities(Request $request, int $groupId)
    {
        $v = $this->validateFindRequest($request);

        $cacheKey = 'ed.entities.'.$groupId.'.'.md5(json_encode($v));
        $entities = Cache::get($cacheKey);
        if ($entities === null) {
            $entities = $this->_searchIndexRepository->resolveIndexToEntities($groupId, $v);
            if (is_array($entities) && ! empty($entities['entities'])) {
                Cache::put($cacheKey, $entities, 60 * 60 /* 1 hour */);
            }
        }

        return $entities;
    }

    /**
     * Retrieves the entity that matches the specific entity ID. The output is compatible
     * with `findMatchesEntities`.
     */
    private function getEntity(int $groupId, int $entityId)
    {
        return $this->_searchIndexRepository->resolveEntity($groupId, $entityId);
    }
}
