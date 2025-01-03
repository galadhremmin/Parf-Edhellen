<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Language;
use App\Models\Sentence;
use App\Repositories\SentenceRepository;
use Illuminate\Http\Request;

class SentenceController extends Controller
{
    private SentenceRepository $_sentenceRepository;

    public function __construct(SentenceRepository $sentenceRepository)
    {
        $this->_sentenceRepository = $sentenceRepository;
    }

    public function index()
    {
        $numberOfSentences = Sentence::approved()->count();
        $numberOfNeologisms = Sentence::approved()->neologisms()->count();
        $randomSentence = Sentence::approved()->inRandomOrder()->select('id')->first();

        if ($randomSentence !== null) {
            $randomSentence = $this->_sentenceRepository->getSentence($randomSentence->id);
        }
        $languages = $this->_sentenceRepository->getLanguages();

        return view('sentence.index', [
            'numberOfSentences' => $numberOfSentences,
            'numberOfNeologisms' => $numberOfNeologisms,
            'languages' => $languages,
            'randomSentence' => $randomSentence,
        ]);
    }

    public function byLanguage(Request $request, int $langId)
    {
        $sentences = $this->_sentenceRepository->getByLanguage($langId)
            ->groupBy('is_neologism')
            ->toArray();
        $language = Language::find($langId);

        return view('sentence.sentences', [
            'sentences' => array_key_exists(0, $sentences) ? $sentences[0] : [],
            'neologisms' => array_key_exists(1, $sentences) ? $sentences[1] : [],
            'language' => $language,
        ]);
    }

    public function bySentence(Request $request, int $langId, string $languageName,
        int $sentId, string $sentName)
    {
        $sentence = $this->_sentenceRepository->getSentence($sentId);
        if ($sentence === null) {
            return response(null, 404);
        }

        $language = Language::findOrFail($langId);

        return view('sentence.sentence', [
            'sentence' => $sentence,
            'language' => $language,
        ]);
    }
}
