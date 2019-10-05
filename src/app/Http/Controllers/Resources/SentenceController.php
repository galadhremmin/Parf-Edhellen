<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Http\Controllers\Controller;
use App\Repositories\SentenceRepository;
use App\Events\{
    SentenceDestroyed
};
use App\Models\{
    Language, 
    Sentence, 
    SentenceFragment, 
    SentenceFragmentInflectionRel
};
use App\Http\Controllers\Traits\{
    CanMapSentence, 
    CanValidateSentence
};
use App\Helpers\{
    LinkHelper,
    SentenceHelper
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
