<?php

namespace App\Http\Controllers;
use App\Models\Language;
use App\Models\Sentence;
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
        $randomSentence     = Sentence::approved()->inRandomOrder()->first();
        $languages          = $this->_sentenceRepository->getLanguages();

        return view('sentences.index', [
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
        
        return view('sentences.sentences', [
            'sentences'    => $sentences,
            'language'     => $language
        ]);
    }

    public function bySentence(Request $request, int $langId, string $languageName,
                               int $sentId, string $sentName)
    {
        $sentence = Sentence::find($sentId);
        $language = Language::find($langId);

        return view('sentences.sentence', [
            'sentence' => $sentence,
            'language' => $language
        ]);
    }
}
