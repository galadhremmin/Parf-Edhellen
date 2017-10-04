<?php

namespace App\Http\Controllers\Contributions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;

use App\Repositories\SentenceRepository;
use App\Adapters\SentenceAdapter;
use App\Models\{
    Contribution,
    Sentence,
    SentenceFragment,
    SentenceFragmentInflectionRel
};
use App\Http\Controllers\Traits\{
    CanValidateSentence, 
    CanMapSentence
};

class SentenceContributionController extends Controller implements IContributionController
{
    use CanValidateSentence, 
        CanMapSentence;

    private $_sentenceAdapter;
    private $_sentenceRepository;

    public function __construct(SentenceAdapter $sentenceAdapter, SentenceRepository $sentenceRepository)
    {
        $this->_sentenceAdapter = $sentenceAdapter;
        $this->_sentenceRepository = $sentenceRepository;
    }

    /**
     * HTTP GET. Shows a sentence contribution.
     *
     * @param Contribution $contribution
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function show(Contribution $contribution)
    {
        $payload = json_decode($contribution->payload);
        $originalSentence = property_exists($payload, 'id')
            ? Sentence::findOrFail($payload->id) : null;

        $sentence = new Sentence((array) $payload->sentence);
        $fragmentData = $this->createFragmentDataFromPayload($payload);

        return view('contribution.sentence.show', [
            'fragmentData'     => json_encode($fragmentData),
            'sentence'         => $sentence,
            'originalSentence' => $originalSentence,
            'review'           => $contribution
        ]);
    }

    /**
     * HTTP GET. Opens a view for editing a sentence contribution.
     *
     * @param Request $request
     * @param Contribution $contribution
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit(Contribution $contribution, Request $request)
    {
        $payload = json_decode($contribution->payload);
        
        $sentence = $payload->sentence;
        $sentence->contribution_id = $contribution->id;
        $sentence->notes = $contribution->notes ?: '';

        $fragmentData = $this->createFragmentDataFromPayload($payload);

        return view('contribution.sentence.edit', [
            'review' => $contribution,
            'sentence' => json_encode($sentence),
            'fragmentData' => json_encode($fragmentData)
        ]);
    }
    
    /**
     * Shows a form for a new contribution.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create(Request $request, int $entityId = 0)
    {
        $model = [];

        if ($entityId) {
            $sentence = Sentence::findOrFail($entityId);
            $fragmentData = $this->createFragmentDataFromPayload($sentence);

            $model = [
                'sentence'     => json_encode($sentence),
                'fragmentData' => json_encode($fragmentData)
            ];
        }

        return view('contribution.sentence.create', $model);
    }

    /**
     * HTTP POST. Performs partial validation of the values passed through the request.
     * There are two substeps for sentences:
     * - 0 (default): sentence form
     * - 1:           fragments
     *
     * @param Request $request
     * @param int $id
     * @param int $substepId
     * @return void
     */
    public function validateSubstep(Request $request, int $id = 0, int $substepId = 0)
    {
        switch ($substepId) {
            case 0:
                $this->validateSentenceInRequest($request, $id, true);
                break;
            case 1:
                $this->validateFragmentsInRequest($request);
                break;
        }
    }

    /**
     * HTTP POST. Performs a full validation of the request's input parameters.
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function validateBeforeSave(Request $request, int $id = 0)
    {
        $this->validateSentenceInRequest($request, $id, true);
        $this->validateFragmentsInRequest($request);
    }

    /**
     * Populates the specified contribution with information pertaining to the
     * sentence payload located within the request's input parameters. Returns
     * the resulting entity.
     *
     * @param Contribution $contribution
     * @param Request $request
     * @return void
     */
    public function populate(Contribution $contribution, Request $request)
    {
        $entity = $request->has('id') 
            ? Sentence::findOrFail( intval($request->input('id')) ) 
            : new Sentence;
        
        $map = $this->mapSentence($entity, $request);

        $entity->account_id = $contribution->account_id;
    
        $contribution->payload = json_encode($map);
        $contribution->word    = $entity->name;
        $contribution->sense   = 'text';
        
        return $entity;
    }

    /**
     * HTTP POST.
     * Approves the specified contributions by transforming it into a sentence entity.
     * The contribution's _sentence_id_ property is assigned the resulting entity's ID.
     *
     * @param Contribution $contribution
     * @param Request $request
     * @return void
     */
    public function approve(Contribution $contribution, Request $request)
    {
        $map = json_decode($contribution->payload, true);

        $sentence = new Sentence($map['sentence']);

        // Is the proposed contribution a modification of an existing sentence entity?
        if (array_key_exists('id', $map['sentence'])) {
            $sentence->id = intval($map['sentence']['id']);
            // Inform the model that it in fact exists, even though it was created as a new entity.
            $sentence->exists = true;
        }
        
        // Transform fragments into SentenceFragment entities.
        $fragments = array_map(function ($fragment) {
            return new SentenceFragment($fragment);
        }, $map['fragments']);

        // Transform inflections into SentenceFragmentInflectionRel entities.
        $inflections = array_map(function ($inflections) {
            return array_map(function ($inflection) {
                return new SentenceFragmentInflectionRel($inflection);
            }, $inflections);
        }, $map['inflections']);

        // Save the sentence and assign the resulting ID to the contribution entity.
        $this->_sentenceRepository->saveSentence($sentence, $fragments, $inflections);
        $contribution->sentence_id = $sentence->id;
    }

    /**
     * Transforms the specified payload into a view object, ready to be JSON-serialized.
     *
     * @param \stdClass|Sentence $payload
     * @return array
     */
    private function createFragmentDataFromPayload($payload)
    {
        if ($payload instanceof Sentence) {
            $fragments = $payload->sentence_fragments;

        } else {
            $fragments = new Collection();
            
            $i = 0;
            foreach ($payload->fragments as $fragmentData) {
                $fragment = new SentenceFragment((array) $fragmentData);

                // Generate a fake ID (descending order, starting at -10).
                $fragment->id = ($i + 1) * -10;

                // Create an array of IDs for inflections associated with this fragment.
                $fragment->_inflections = array_map(function ($rel) {
                    return $rel->inflection_id;
                }, $payload->inflections[$i]);

                $fragments->push($fragment);
                
                $i += 1;
            }
        }

        $result = $this->_sentenceAdapter->adaptFragments($fragments);
        return $result;
    }
}