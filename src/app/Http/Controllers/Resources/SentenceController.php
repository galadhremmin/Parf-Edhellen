<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Http\Controllers\Controller;
use App\Helpers\LinkHelper;
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
use App\Adapters\{
    SentenceBuilder, 
    LatinSentenceBuilder, 
    SentenceAdapter
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
    protected $_sentenceAdapter;

    public function __construct(SentenceRepository $sentenceRepository, SentenceAdapter $sentenceAdapter)
    {
        $this->_sentenceRepository = $sentenceRepository;
        $this->_sentenceAdapter = $sentenceAdapter;
    }

    public function index(Request $request)
    {
        $sentences = $this->_sentenceRepository->getAllGroupedByLanguage();
        return view('sentence.index', ['sentences' => $sentences]);
    }

    public function create(Request $request)
    {
        return view('sentence.create');
    }

    public function edit(Request $request, int $id) 
    {
        $sentence = Sentence::findOrFail($id);
        $data = $this->_sentenceAdapter->adaptFragments($sentence->sentence_fragments, false);

        return view('sentence.edit', [
            'sentence'     => $sentence, 
            'sentenceData' => $data
        ]);
    }

    public function confirmDestroy(Request $request, int $id)
    {
        $sentence = Sentence::findOrFail($id);
        return view('sentence.confirm-destroy', [
            'sentence' => $sentence
        ]);
    }

    public function store(Request $request)
    {
        $this->validateSentenceInRequest($request);
        $this->validateFragmentsInRequest($request);

        $sentence = new Sentence;
        $this->saveSentence($sentence, $request);

        $link = new LinkHelper();
        return [
            'sentence' => $sentence, 
            'url'      => $link->sentence(
                $sentence->language->id, 
                $sentence->language->name,
                $sentence->id,
                $sentence->name
            )
        ];
    }

    public function update(Request $request, int $id)
    {
        $this->validateSentenceInRequest($request, $id);
        $this->validateFragmentsInRequest($request);
        
        $sentence = Sentence::findOrFail($id);
        $this->saveSentence($sentence, $request);

        $link = new LinkHelper();
        return [
            'sentence' => $sentence, 
            'url'      => $link->sentence(
                $sentence->language->id, 
                $sentence->language->name,
                $sentence->id,
                $sentence->name
            )
        ];
    }

    public function destroy(Request $request, int $id) 
    {
        $sentence = Sentence::findOrFail($id);
        
        $this->_sentenceRepository->destroyFragments($sentence);
        $sentence->delete();

        event(new SentenceDestroyed($sentence));

        return redirect()->route('sentence.index');
    }

    public function validatePayload(Request $request)
    {
        $this->validateSentenceInRequest($request, $request->input('id') ?? 0);
        return response(null, 204);
    }

    public function validateFragments(Request $request) 
    {
        $this->validateFragmentsInRequest($request);
        return response(null, 204);
    }

    public function parseFragments(Request $request, string $name)
    {
        parent::validate($request, [
            'fragments' => 'required|array'
        ]);

        $fragments = $request->input('fragments');
        $sentences = $this->_sentenceAdapter->adaptFragmentsToSentences($fragments, $name);

        return $sentences[$name];
    }

    protected function saveSentence(Sentence $sentence, Request $request) 
    {
        $map = $this->mapSentence($sentence, $request);
        $this->_sentenceRepository->saveSentence($map['sentence'], $map['fragments'], $map['inflections']);
    }
}
