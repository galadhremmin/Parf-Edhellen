<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Models\{Language, Sentence, SentenceFragment, SentenceFragmentInflectionRel};
use App\Repositories\SentenceRepository;
use App\Adapters\SentenceAdapter;
use App\Helpers\LinkHelper;
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
        $fragments = $this->_sentenceAdapter->adaptFragments($sentence->sentence_fragments, false);

        return view('sentence.edit', ['sentence' => $sentence, 'fragments' => $fragments]);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);
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
        $this->validateRequest($request, $id);
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
        
        $this->destroyFragments($sentence);
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

    protected function saveSentence(Sentence $sentence, Request $request) 
    {
        $sentence->name             = $request->input('name');
        $sentence->source           = $request->input('source');
        $sentence->description      = $request->input('description');
        $sentence->long_description = $request->input('long_description') ?? null;
        $sentence->language_id      = intval($request->input('language_id'));
        $sentence->account_id       = intval($request->input('account_id'));
        $sentence->is_neologism     = intval($request->input('is_neologism'));
        $sentence->is_approved      = 1; // always approved by administrators

        $sentence->save();

        $this->destroyFragments($sentence);
        $order = 0;
        foreach ($request->input('fragments') as $fragmentData) {
            $fragment = new SentenceFragment;

            $fragment->is_linebreak = boolval($fragmentData['is_linebreak']);

            if (! $fragment->is_linebreak) {
                $fragment->fragment = $fragmentData['fragment'];
                $fragment->tengwar  = $fragmentData['tengwar'];
                $fragment->comments = $fragmentData['comments'] ?? ''; // cannot be NULL

                if (! $fragment->isPunctuationOrWhitespace()) {
                    $fragment->speech_id      = intval($fragmentData['speech_id']);
                    $fragment->translation_id = intval($fragmentData['translation_id']);
                    $fragment->is_linebreak   = false;
                } 
            } else {
                $fragment->fragment = '\\n';
                $fragment->comments = '';
            }

            $fragment->order       = $order;
            $fragment->sentence_id = $sentence->id;

            $fragment->save();

            if (! $fragment->isPunctuationOrWhitespace() && isset($fragmentData['inflections'])) {
                foreach ($fragmentData['inflections'] as $inflection) {
                    $inflectionRel = new SentenceFragmentInflectionRel;

                    $inflectionRel->inflection_id        = $inflection['id'];
                    $inflectionRel->sentence_fragment_id = $fragment->id;

                    $inflectionRel->save(); 
                }
            }

            $order += 10;
        }
    }

    protected function destroyFragments(Sentence $sentence) 
    {
        foreach ($sentence->sentence_fragments as $fragment) {
            foreach ($fragment->inflection_associations as $inflectionRel) {
                $inflectionRel->delete();
            }
            
            $fragment->delete();
        }
    }

    protected function validateRequest(Request $request, int $id = 0)
    {
        $rules = [
            'name'         => 'required|string|min:1|max:128|unique:sentences,name'.($id === 0 ? '' : ','.$id.',id'),
            'description'  => 'required|string|max:255',
            'language_id'  => 'required|numeric|exists:languages,id',
            'source'       => 'required|min:3|max:64',
            'id'           => 'sometimes|required|numeric|exists:sentences,id',
            'is_neologism' => 'required|boolean',
            'account_id'   => 'required|numeric|exists:accounts,id'
        ];

        $this->validate($request, $rules);
    } 

    protected function validateFragmentsInRequest(Request $request)
    {
        // This is unfortunately a multi-tiered validation process, as its validation
        // rules are heavily dependant on the request data payload.
        //
        // step 1: Ensure that there is a parameter called _fragments_.
        $rules = [
            'fragments'                => 'required|array',
            'fragments.*.is_linebreak' => 'required|boolean'
        ];
        $this->validate($request, $rules);

        // Step 2: construct a new set of rules dependant on the payload
        $rules = [];
        $fragments = $request->input('fragments');
        $numberOfFragments = count($fragments);
    
        for ($i = 0; $i < $numberOfFragments; $i += 1) {
            $prefix = 'fragments.'.$i.'.';

            // Line breaks are treated in a very restricted manner and therefore requires minimum
            // validation. 
            if ($fragments[$i]['is_linebreak']) {
                continue;
            }

            $rules[$prefix.'fragment'] = 'required|max:48';

            // Apply additional validation to non-interpunctuation fragments. Create
            // an instance of the SentenceFragment class to access the _isPunctuationOrWhitespace_
            // method
            $fragment = new SentenceFragment;
            $fragment->fragment = $fragments[$i]['fragment'];

            if (! $fragment->isPunctuationOrWhitespace()) {
                $rules[$prefix.'tengwar']          = 'required|max:128';
                $rules[$prefix.'translation_id']   = 'required|exists:translations,id';
                $rules[$prefix.'speech_id']        = 'required|exists:speeches,id';

                // inflections are optional, but when present, have to be declared as an array
                $rules[$prefix.'inflections']      = 'sometimes|array';
                $rules[$prefix.'inflections.*.id'] = 'sometimes|exists:inflections,id';
            }
        }

        $this->validate($request, $rules);
    }
}
