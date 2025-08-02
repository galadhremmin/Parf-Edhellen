<?php

namespace App\Http\Controllers\Contributions;

use App\Helpers\SentenceHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Http\Controllers\Traits\CanMapSentence;
use App\Http\Controllers\Traits\CanValidateSentence;
use App\Models\Contribution;
use App\Models\LexicalEntryInflection;
use App\Models\Sentence;
use App\Models\SentenceFragment;
use App\Models\SentenceTranslation;
use App\Repositories\SentenceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SentenceContributionController extends Controller implements IContributionController
{
    use CanMapSentence,
        CanValidateSentence;

    private SentenceHelper $_sentenceHelper;

    private SentenceRepository $_sentenceRepository;

    public function __construct(SentenceHelper $sentenceHelper, SentenceRepository $sentenceRepository)
    {
        $this->_sentenceHelper = $sentenceHelper;
        $this->_sentenceRepository = $sentenceRepository;
    }

    public function getViewModel(Contribution $contribution): ViewModel
    {
        $payload = json_decode($contribution->payload, true);
        $this->makeMapCurrent($payload);

        $originalSentence = isset($payload['id'])
            ? Sentence::with('sentence_fragments')->findOrFail(intval($payload['id'])) : null;

        $sentence = new Sentence($payload['sentence']);

        // This is all the data required by the sentence component. It has to be 'rebuilt'
        // based on the JSON payload that is stored on the contribution. This is a bit clumsy, but
        // it is currently the only way to visualize the sentence in the contribution preview view.
        $fragmentData = $this->createFragmentDataFromPayload($payload);
        $speeches = $this->_sentenceRepository->getSpeechesForSentenceFragments($fragmentData['fragments']);
        $inflections = $this->_sentenceRepository->getInflectionsForSentenceFragments($fragmentData['fragments']);

        $fragmentData = [
            'inflections' => $inflections,
            'sentence' => $sentence,
            'sentence_fragments' => $fragmentData['fragments'],
            'sentence_translations' => $fragmentData['translations'],
            'sentence_transformations' => $fragmentData['transformations'],
            'speeches' => $speeches,
        ];

        $model = [
            'fragmentData' => $fragmentData,
            'sentence' => $sentence,
            'originalSentence' => $originalSentence,
            'review' => $contribution,
        ];

        return new ViewModel($contribution, 'contribution.sentence._show', $model);
    }

    /**
     * HTTP GET. Opens a view for editing a sentence contribution.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit(Contribution $contribution, Request $request)
    {
        $payload = json_decode($contribution->payload, true);
        $this->makeMapCurrent($payload);

        $sentenceData = $payload['sentence'];
        $sentence = new Sentence($sentenceData);
        $sentence->contribution_id = $contribution->id;
        $sentence->notes = $contribution->notes ?: '';
        $sentence->load('account');

        if (isset($sentenceData['id']) && $sentenceData['id'] !== 0) {
            $sentence->setAttribute('id', $sentenceData['id']);
        }

        $fragmentData = $this->createFragmentDataFromPayload($payload);
        $model = array_merge($fragmentData, [
            'review' => $contribution,
            'sentence' => $sentence,
        ]);

        return view('contribution.sentence.edit', $model);
    }

    /**
     * Shows a form for a new contribution.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create(Request $request, int $entityId = 0)
    {
        $model = [];

        if ($entityId) {
            $sentence = Sentence::findOrFail($entityId);
            $sentence->load('account');

            $fragmentData = $this->createFragmentDataFromPayload($sentence);
            $model = array_merge($fragmentData, [
                'sentence' => $sentence,
            ]);
        }

        return view('contribution.sentence.create', $model);
    }

    /**
     * HTTP POST. Performs partial validation of the values passed through the request.
     * There are two substeps for sentences:
     * - 0 (default): sentence form
     * - 1:           fragments
     *
     * @return void
     */
    public function validateSubstep(Request $request, int $id = 0, int $substepId = 0): mixed
    {
        switch ($substepId) {
            case 0:
                $this->validateSentenceInRequest($request, $id);
                break;
            case 1:
                $this->validateFragmentsInRequest($request);
                break;
            case 2:
                return $this->createTransformations($request);
        }

        return true;
    }

    /**
     * HTTP POST. Performs a full validation of the request's input parameters.
     *
     * @return void
     */
    public function validateBeforeSave(Request $request, int $id = 0): bool
    {
        $this->validateSentenceInRequest($request, $id);
        $this->validateFragmentsInRequest($request);

        return true;
    }

    /**
     * Populates the specified contribution with information pertaining to the
     * sentence payload located within the request's input parameters. Returns
     * the resulting entity.
     *
     * @return void
     */
    public function populate(Contribution $contribution, Request $request)
    {
        $entity = $request->has('id')
            ? Sentence::findOrFail(intval($request->input('id')))
            : new Sentence;

        $map = $this->mapSentence($entity, $request);

        if (! $request->user()->isAdministrator()) {
            $entity->account_id = $contribution->account_id;
        }

        $contribution->payload = json_encode($map);
        $contribution->word = $entity->name;
        $contribution->sense = 'text';
        $contribution->language_id = $entity->language_id;

        return $entity;
    }

    /**
     * Disable change detection within the parent controller as the payload is always
     * populated by the `populate` method.
     */
    public function disableChangeDetection(): bool
    {
        return true;
    }

    /**
     * HTTP POST.
     * Approves the specified contributions by transforming it into a sentence entity.
     * The contribution's _sentence_id_ property is assigned the resulting entity's ID.
     *
     * @return void
     */
    public function approve(Contribution $contribution, Request $request): int
    {
        $map = json_decode($contribution->payload, true);
        $this->makeMapCurrent($map);

        // Is the proposed contribution a modification of an existing sentence entity?
        $sentence = null;
        if (isset($map['sentence']['id'])) {
            $sentence = Sentence::find(intval($map['sentence']['id']));
            if ($sentence) {
                $sentence->fill($map['sentence']);
            }
        }

        if (! $sentence) {
            $sentence = new Sentence($map['sentence']);
        }

        // Transform fragments into SentenceFragment entities.
        $fragments = array_map(function ($fragment) {
            return new SentenceFragment($fragment);
        }, $map['fragments']);

        // Transform inflections into GlossInflection entities.
        $inflections = array_map(function ($inflections) {
            return array_map(function ($inflection) {
                return new LexicalEntryInflection($inflection);
            }, $inflections);
        }, $map['inflections']);

        $translations = [];
        if (isset($map['translations'])) {
            $translations = array_map(function ($translation) {
                return new SentenceTranslation($translation);
            }, $map['translations']);
        }

        // Save the sentence and assign the resulting ID to the contribution entity.
        $this->_sentenceRepository->saveSentence($sentence, $fragments, $inflections, $translations);

        return $sentence->id;
    }

    private function createTransformations(Request $request)
    {
        $this->validateFragmentsInRequest($request, false);
        $suggestionMap = $request->validate([
            'suggest_for_language_id' => 'sometimes|numeric|exists:languages,id',
        ]);

        $sentence = new Sentence;
        $fragmentsMap = $this->mapSentenceFragments($sentence, $request);
        $transformations = $this->_sentenceHelper->buildSentences($fragmentsMap['fragments']);
        $suggestions = [];

        if (isset($suggestionMap['suggest_for_language_id'])) {
            $languageId = intval($suggestionMap['suggest_for_language_id']);
            $suggestions = $this->_sentenceRepository->suggestFragmentGlosses($fragmentsMap['fragments'], $languageId);
        }

        return [
            'suggestions' => (object) $suggestions,
            'transformations' => $transformations,
        ];
    }

    /**
     * Transforms the specified payload into a view object, ready to be JSON-serialized.
     *
     * @param  \stdClass|Sentence  $payload
     * @return array
     */
    private function createFragmentDataFromPayload($payload)
    {
        if ($payload instanceof Sentence) {
            $fragments = $payload->sentence_fragments;
            $translations = $payload->sentence_translations;

            foreach ($fragments as $fragment) {
                $fragment->load('lexical_entry_inflections');
            }

        } else {
            $fragments = new Collection;
            $translations = new Collection;

            $i = 0;
            foreach ($payload['fragments'] as $fragmentData) {
                // To maintain backwards compatibility, make sure to transition fields native to v2 to v3's structure.
                if (isset($fragmentData['gloss_id'])) {
                    $fragmentData['lexical_entry_id'] = $fragmentData['gloss_id'];
                    unset($fragmentData['gloss_id']);
                }

                $fragment = new SentenceFragment($fragmentData);

                // Generate a fake ID (descending order, starting at -1).
                $fragment->setAttribute('id', ($i + 1) * -1);

                // Create an array of IDs for inflections associated with this fragment.
                $fragment->lexical_entry_inflections = collect(
                    array_map(function ($i) {  // 20220831: maintained for backwards compatibility.
                        return [
                            'inflection_id' => $i['inflection_id'],
                        ];
                    }, $payload['inflections'][$i])
                )->map(function ($i, $order) {
                    return new LexicalEntryInflection($i + ['order' => $order]);
                });

                $fragments->push($fragment);
                $i += 1;
            }

            if (isset($payload['translations'])) {
                foreach ($payload['translations'] as $translationData) {
                    $translation = new SentenceTranslation($translationData);
                    $translations->push($translation);
                }
            }
        }

        $transformations = $this->_sentenceHelper->buildSentences($fragments);

        return [
            'fragments' => $fragments,
            'translations' => $translations,
            'transformations' => $transformations,
        ];
    }

    /**
     * Transitions earlier payloads to a format compatible with the latest version of
     * the API. This transition was made necessary after transitioning to version 2, when
     * a breaking change was introduced: _translations_ was renamed _glosses_.
     *
     * @return void
     */
    private function makeMapCurrent(array &$map)
    {
        if (! isset($map['fragments'])) {
            abort(400, 'A strange payload, indeed. There are no fragments.');
        }

        $fragments = &$map['fragments'];
        $inflections = $map['inflections'];

        if (count($fragments) !== count($inflections)) {
            abort(400, 'The number of fragments does not match the number of available inflections.');
        }

        if (count($fragments) < 1) {
            return;
        }

        if (array_key_exists('lexical_entry_id', $fragments[0])) {
            return;
        }

        $i = 0;
        $numberOfFragments = count($fragments);
        while ($i < $numberOfFragments) {
            $fragment = &$fragments[$i];

            // transition from API version v1 to v2.
            if (isset($fragment['translation_id'])) {
                $fragment['lexical_entry_id'] = $fragment['translation_id'];
                unset($fragment['translation_id']);
            }

            $i += 1;
        }
    }
}
