<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Repositories\SentenceRepository;
use App\Events\{
    SentenceDestroyed
};
use App\Models\{
    Sentence
};
use App\Http\Controllers\Traits\{
    CanMapSentence, 
    CanValidateSentence
};

class SentenceController extends Controller
{
    use CanMapSentence,
        CanValidateSentence;

    protected $_sentenceRepository;

    public function __construct(SentenceRepository $sentenceRepository)
    {
        $this->_sentenceRepository = $sentenceRepository;
    }

    public function index(Request $request)
    {
        $sentences = $this->_sentenceRepository->getAllGroupedByLanguage();
        return view('sentence.index', ['sentences' => $sentences]);
    }

    public function confirmDestroy(Request $request, int $id)
    {
        $sentence = Sentence::findOrFail($id);
        return view('sentence.confirm-destroy', [
            'sentence' => $sentence
        ]);
    }

    public function destroy(Request $request, int $id) 
    {
        $sentence = Sentence::findOrFail($id);
        
        $this->_sentenceRepository->destroyFragments($sentence);
        $sentence->delete();

        event(new SentenceDestroyed($sentence));

        return redirect()->route('sentence.index');
    }
}
