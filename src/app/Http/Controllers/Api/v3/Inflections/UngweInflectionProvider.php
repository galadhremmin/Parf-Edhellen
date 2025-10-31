<?php

namespace App\Http\Controllers\Api\v3\Inflections;

use Illuminate\Support\Facades\Http;
use App\Models\LexicalEntry;
use App\Repositories\SystemErrorRepository;
use App\Helpers\StringHelper;

class UngweInflectionProvider implements IInflectionProvider
{
    const UNGWE_API_URL = 'https://ungwe.net/api/v1';
    const UNGWE_API_TAGS = 'verb-bare,verb-aor1,verb-gerd,verb-futu,verb-past,verb-perf,verb-impe,verb-fimp,verb-fper';
    const UNGWE_API_NO_VERB_FORM_RESPONSE = ['words' => []];

    public function __construct(private SystemErrorRepository $_systemErrorRepository)
    {}

    public function getInflections(LexicalEntry $lexicalEntry): ?array
    {
        try {
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
                return self::UNGWE_API_NO_VERB_FORM_RESPONSE;
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
                        $form['forms'][$i] = StringHelper::reverseNormalization($form['forms'][$i], longAccents: false);
                    }
                }
            }
            unset($word, $form); // Break the references to avoid unexpected behavior

            $inflectData['url'] = !empty($inflectData['words']) //
                ? 'https://ungwe.net/parne/word?verb='.$inflectData['words'][0]['lemma'].':'.$inflectData['words'][0]['homonym']
                : null;

            return $inflectData;
        } catch (\Exception $e) {
            $this->_systemErrorRepository->saveException($e, 'ungwe');
            return null; 
        }
    }
}
