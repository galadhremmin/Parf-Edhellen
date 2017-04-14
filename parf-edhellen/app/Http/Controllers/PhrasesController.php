<?php

namespace App\Http\Controllers;
use App\Models\Sentence;
use App\Repositories\SentenceRepository;
use Illuminate\Http\Request;

class PhrasesController extends Controller
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
        $languages          = $this->_sentenceRepository->getLanguages();

        return view('phrases.index', [
            'numberOfSentences'  => $numberOfSentences,
            'numberOfNeologisms' => $numberOfNeologisms,
            'languages'          => $languages
        ]);
    }

    public function byLanguage(Request $request, int $langId) {
        return $langId;
    }
}
