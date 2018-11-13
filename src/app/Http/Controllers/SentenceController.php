<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Sentence;
use App\Helpers\MarkdownParser;
use App\Repositories\SentenceRepository;
use Illuminate\Http\Request;

class SentenceController extends Controller
{
    private $_sentenceRepository;

    public function __construct(SentenceRepository $sentenceRepository)
    {
        $this->_sentenceRepository = $sentenceRepository;
    }

    public function index() 
    {
        $numberOfSentences  = Sentence::approved()->count();
        $numberOfNeologisms = Sentence::approved()->neologisms()->count();
        $randomSentence     = Sentence::approved()->inRandomOrder()->select('id')->first();

        $randomSentence     = $this->_sentenceRepository->getSentence($randomSentence->id);
        $languages          = $this->_sentenceRepository->getLanguages();

        return view('sentence.public.index', [
            'numberOfSentences'  => $numberOfSentences,
            'numberOfNeologisms' => $numberOfNeologisms,
            'languages'          => $languages,
            'randomSentence'     => $randomSentence
        ]);
    }

    public function byLanguage(Request $request, int $langId)
    {
        $sentences = $this->_sentenceRepository->getByLanguage($langId)
            ->groupBy('is_neologism')
            ->toArray();
        $language = Language::find($langId);

        return view('sentence.public.sentences', [
            'sentences'    => array_key_exists(0, $sentences) ? $sentences[0] : [],
            'neologisms'   => array_key_exists(1, $sentences) ? $sentences[1] : [],
            'language'     => $language
        ]);
    }

    public function bySentence(Request $request, int $langId, string $languageName,
                               int $sentId, string $sentName)
    {
        $sentence = $this->_sentenceRepository->getSentence($sentId);
        $language = Language::findOrFail($langId);
        
        return view('sentence.public.sentence', [
            'sentence' => $sentence,
            'language' => $language
        ]);
    }
}
