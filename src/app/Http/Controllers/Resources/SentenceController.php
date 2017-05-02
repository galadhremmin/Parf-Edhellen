<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Models\{Language, Sentence};
use App\Repositories\SentenceRepository;
use App\Adapters\SentenceAdapter;
use App\Http\Controllers\Controller;

class SentenceController extends Controller
{
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
        $fragments = $this->_sentenceAdapter->adaptFragments($sentence->fragments, false);

        return view('sentence.edit', ['sentence' => $sentence, 'fragments' => $fragments]);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);
        
        // 

        return redirect()->route('sentence.index');
    }

    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id);

        $sentence = Sentence::findOrFail($id);
        $sentence->name  = $request->input('name');
        $sentence->save();

        return redirect()->route('sentence.index');
    }

    public function destroy(Request $request, int $id) 
    {
        $sentence = Sentence::findOrFail($id);
        
        foreach ($sentence->fragments as $fragment) {
            $fragment->delete();
        }

        $sentence->delete();

        return redirect()->route('sentence.index');
    }

    public function validatePayload(Request $request)
    {
        $this->validateRequest($request, $request->input('id') ?? 0);
        return response(null, 200);
    }

    public function validateFragments(Request $request) 
    {
        $this->validateFragmentsInRequest($request);
        return response(null, 200);
    }

    protected function validateRequest(Request $request, int $id = 0)
    {
        $rules = [
            'name'        => 'required|min:1|max:128|unique:sentences,name'.($id === 0 ? '' : ','.$id.',id'),
            'description' => 'required|max:255',
            'language_id' => 'required|exists:languages,id',
            'source'      => 'required|min:3|max:64'
        ];

        $this->validate($request, $rules);
    } 

    protected function validateFragmentsInRequest(Request $request)
    {
        $rules = [
            'fragments' => 'required|array'
        ];
        $this->validate($request, $rules);

        $rules = [];
        $fragments = $request->input('fragments');
        $numberOfFragments = count($fragments);

        for ($i = 0; $i < $numberOfFragments; $i += 1) {
            $prefix = 'fragments.'.$i.'.';
            
            $rules[$prefix.'translation_id'] = 'required|exists:translations,id';
            $rules[$prefix.'speech_id'] = 'required|exists:speeches,id';
            $rules[$prefix.'tengwar'] = 'required|max:128';
            $rules[$prefix.'fragment'] = 'required|max:48';

            $inflections = $fragments[$i]['inflections'];
            $numberOfInflections = count($inflections);

            for ($j = 0; $j < $numberOfInflections; $j += 1) {
                $rules[$prefix.'inflections.'.$j.'.id'] = 'required|exists:inflections,id';
            }
        }
        $this->validate($request, $rules);
    }
}
