<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Inflection;
use App\Repositories\SystemErrorRepository;
use App\Http\Controllers\Api\v3\Inflections\UngweInflectionProvider;
use App\Http\Controllers\Api\v3\Inflections\RinorSindarinInflectionProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use DateInterval;   

class InflectionApiController extends Controller
{
    private array $_inflectionProviders = [];

    public function __construct(
        private SystemErrorRepository $_systemErrorRepository,
        UngweInflectionProvider $ungweInflectionProvider,
        RinorSindarinInflectionProvider $rinorSindarinInflectionProvider)
    {
        $this->_inflectionProviders['quenya'] = $ungweInflectionProvider;
        $this->_inflectionProviders['sindarin'] = $rinorSindarinInflectionProvider;
    }

    public function index(Request $request, int $id = 0)
    {
        if ($id !== 0) {
            $inflection = Inflection::find($id);
            if ($inflection === null) {
                return response(null, 404);
            }

            return $inflection;
        }

        return Inflection::orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');
    }

    /**
     * HTTP GET. Fetches inflections from inflection providers for a given lexical entry.
     *
     * @param Request $request
     * @param int $lexicalEntryId The lexical entry ID to inflect
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAutoInflections(Request $request, int $lexicalEntryId)
    {
        /*
         * This is a highly specialized endpoint that fetches inflections from inflection providers for a given lexical entry. This is deliberately built not to be
         * generic and applicable to other languages, and the eligibility criteria is hardcoded for Quenya only. 
         * 
         * TODO: explore whether third party integrations like this can be generalized in a more elegant manner in the future.
         */
        $inflectData = Cache::remember('auto.inflections.'.$lexicalEntryId, DateInterval::createFromDateString('1 month'), function () use ($lexicalEntryId) {
            // Look up the lexical entry
            $lexicalEntry = \App\Models\LexicalEntry::with('word', 'language')->find($lexicalEntryId);
            
            if ($lexicalEntry === null) {
                return null;
            }

            $languageName = mb_strtolower($lexicalEntry->language->name);
            if (! isset($this->_inflectionProviders[$languageName])) {
                return null;
            }

            $inflections = $this->_inflectionProviders[$languageName]->getInflections($lexicalEntry);
            $inflections['tengwar_mode'] = $lexicalEntry->language->tengwar_mode;
            return $inflections;
        });

        if ($inflectData === null) {
            return response()->json([
                'error' => 'An error occurred while fetching inflections',
            ], 500);
        }

        return response()->json($inflectData);
    }
}
