<?php

namespace App\Http\Controllers\Api\v3;

use App\Helpers\StringHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Inflection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Repositories\SystemErrorRepository;
use DateInterval;   

class InflectionApiController extends Controller
{
    const UNGWE_API_URL = 'https://ungwe.net/api/v1';
    const UNGWE_API_TAGS = 'verb-bare,verb-aor1,verb-gerd,verb-futu,verb-past,verb-perf,verb-impe,verb-curt';

    public function __construct(private SystemErrorRepository $_systemErrorRepository)
    {}

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
     * HTTP GET. Fetches inflections from Ungwe API for a given lexical entry.
     *
     * @param Request $request
     * @param int $lexicalEntryId The lexical entry ID to inflect
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUngweInflections(Request $request, int $lexicalEntryId)
    {
        /*
         * This is a highly specialized endpoint that fetches inflections from Ungwe API for a given lexical entry. This is deliberately built not to be
         * generic and applicable to other languages, and the eligibility criteria is hardcoded for Quenya only. 
         * 
         * TODO: explore whether third party integrations like this can be generalized in a more elegant manner in the future.
         */
        $inflectData = Cache::remember('ungwe.inflections.'.$lexicalEntryId, DateInterval::createFromDateString('1 month'), function () use ($lexicalEntryId) {
            try {
                // Look up the lexical entry
                $lexicalEntry = \App\Models\LexicalEntry::with('word')->find($lexicalEntryId);
                
                if ($lexicalEntry === null) {
                    return null;
                }
    
                $word = $lexicalEntry->word->word;
                $word = trim(trim($word), '-');
    
                // Step 1: Get word reference to find qwid for verb
                $wordRefResponse = Http::get(self::UNGWE_API_URL . '/wordrefs', [
                    'word' => $word,
                ]);
    
                if (!$wordRefResponse->successful()) {
                    return null;
                }
    
                $wordRefData = $wordRefResponse->json();
    
                // Find the verb entry
                $qwid = null;
                foreach ($wordRefData['found'] ?? [] as $found) {
                    foreach ($found['words'] ?? [] as $wordData) {
                        if ($wordData['category'] === 'v') {
                            $qwid = $wordData['qwid'];
                            break 2;
                        }
                    }
                }
    
                if ($qwid === null) {
                    $this->_systemErrorRepository->saveException(new \Exception('No verb form found for word: '.$word), 'ungwe');
                    return null;
                }
    
                // Step 2: Get inflections using the qwid
                $inflectResponse = Http::get(self::UNGWE_API_URL . '/inflect', [
                    'qwid' => $qwid,
                    'tags' => self::UNGWE_API_TAGS,
                ]); 
    
                if (!$inflectResponse->successful()) {    
                    $this->_systemErrorRepository->saveException(new \Exception('Ungwe inflect API request failed for qwid: '.$qwid), 'ungwe');
                    return null;
                }
    
                $inflectData = $inflectResponse->json();
                foreach ($inflectData['words'] as &$word) {
                    foreach ($word['forms'] as &$form) {
                        $form['tag'] = __('ungwe.'.$form['tag']);
                        for ($i = 0; $i < count($form['forms']); $i++) {
                            $form['forms'][$i] = StringHelper::reverseNormalization($form['forms'][$i]);
                        }
                    }
                }
                unset($word, $form); // Break the references to avoid unexpected behavior
    
                return $inflectData;
            } catch (\Exception $e) {
                $this->_systemErrorRepository->saveException($e, 'ungwe');
                return null; 
            }
        });

        if ($inflectData === null) {
            return response()->json([
                'error' => 'An error occurred while fetching inflections',
            ], 500);
        }

        return response()->json($inflectData);
    }
}
