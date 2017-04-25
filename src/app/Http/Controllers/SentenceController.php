<?php

namespace App\Http\Controllers;
use App\Adapters\SentenceAdapter;
use App\Models\Language;
use App\Models\Sentence;
use App\Repositories\SentenceRepository;
use Illuminate\Http\Request;

class SentenceController extends Controller
{
    private $_sentenceRepository;
    private $_adapter;

    public function __construct(SentenceRepository $sentenceRepository, SentenceAdapter $adapter)
    {
        $this->_sentenceRepository = $sentenceRepository;
        $this->_adapter = $adapter;
    }

    public function index() 
    {
        $numberOfSentences  = Sentence::approved()->count();
        $numberOfNeologisms = Sentence::approved()->neologisms()->count();
        $randomSentence     = Sentence::approved()->inRandomOrder()->first();
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
        $sentences = $this->_sentenceRepository->getByLanguage($langId);
        $language = Language::find($langId);
        
        return view('sentence.public.sentences', [
            'sentences'    => $sentences,
            'language'     => $language
        ]);
    }

    public function bySentence(Request $request, int $langId, string $languageName,
                               int $sentId, string $sentName)
    {
        $sentence  = Sentence::find($sentId);
        $language  = Language::find($langId);
        $fragments = $this->_adapter->adaptFragments($sentence->fragments);

        return view('sentence.public.sentence', [
            'sentence'  => $sentence,
            'language'  => $language,
            'fragments' => $fragments
        ]);
    }
}
