<?php

namespace App\Http\Controllers;
use App\Adapters\SentenceAdapter;
use App\Models\Language;
use App\Models\Sentence;
use App\Helpers\MarkdownParser;
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
        $randomSentenceData = $this->_adapter->adaptFragments($randomSentence->sentence_fragments);
        $languages          = $this->_sentenceRepository->getLanguages();

        return view('sentence.public.index', [
            'numberOfSentences'  => $numberOfSentences,
            'numberOfNeologisms' => $numberOfNeologisms,
            'languages'          => $languages,
            'randomSentence'     => $randomSentence,
            'randomSentenceData' => $randomSentenceData
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
        $sentence  = Sentence::findOrFail($sentId);
        $language  = Language::findOrFail($langId);
        
        $data = $this->_adapter->adaptFragments($sentence->sentence_fragments);

        $parser = new MarkdownParser();
        $sentence->long_description = $parser->parse($sentence->long_description);

        return view('sentence.public.sentence', [
            'sentence'     => $sentence,
            'sentenceData' => $data,
            'language'     => $language
        ]);
    }
}
