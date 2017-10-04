<?php

namespace App\Http\Controllers\Contributions;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Repositories\TranslationRepository;
use App\Adapters\BookAdapter;
use App\Models\{
    Contribution,
    Sense,
    Translation,
    Word
};
use App\Http\Controllers\Traits\{
    CanValidateTranslation, 
    CanMapTranslation
};

class TranslationContributionController extends Controller implements IContributionController
{
    use CanMapTranslation,
        CanValidateTranslation;

    private $_bookAdapter;
    private $_translationRepository;

    public function __construct(BookAdapter $bookAdapter, TranslationRepository $translationRepository)
    {
        $this->_bookAdapter = $bookAdapter;
        $this->_translationRepository = $translationRepository;
    }

    /**
     * HTTP GET. Shows a translation contribution.
     *
     * @param Contribution $contribution
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function show(Contribution $contribution)
    {
        $keywords = json_decode($contribution->keywords);

        $translationData = json_decode($contribution->payload, true);
        if (! is_array($translationData)) {
            abort(400, 'Unrecognised payload: '.$contribution->payload);
        }

        $parentTranslation = array_key_exists('id', $translationData)
            ? $translationData['id'] : 0;
        $translationData = $translationData + [ 
            'word'     => $contribution->word,
            'sense'    => $contribution->sense
        ];
        $translation = new Translation($translationData);
        $translations = [$translation];

        $translation->created_at   = $contribution->created_at;
        $translation->account_name = $contribution->account->nickname;
        $translation->type         = $translation->speech->name;

        $translationData = $this->_bookAdapter->adaptTranslations($translations);
        
        return view('contribution.translation.show', $translationData + [
            'review'            => $contribution,
            'keywords'          => $keywords,
            'parentTranslation' => $parentTranslation
        ]);
    }

    /**
     * HTTP GET. Opens a view for editing a translation contribution.
     *
     * @param Request $request
     * @param Contribution $contribution
     * @return array|\Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit(Contribution $contribution, Request $request)
    {
        // retrieve word and sense based on the information specified in the review object. If the word does not exist in 
        // the database, create a new instance of the model for the word.
        $word = Word::forString($contribution->word)->firstOrNew(['word' => $contribution->word]);
        $senseWord = Word::forString($contribution->sense)->firstOrNew([]);
        $sense = Sense::where('id', $senseWord->id)->with('word')->firstOrNew([]);
        if (! $sense->id) {
            // _word_ is actually a navigation property.
            $sense->word = Word::forString($contribution->sense)->firstOrNew(['word' => $contribution->sense]);
        }

        // Convert keyword strings to Word objects
        $keywords = array_map(function ($k) {
            return new Word([ 'word' => $k ]);
        }, json_decode($contribution->keywords));

        // extend the payload with information necessary for the form.
        $payload = json_decode($contribution->payload, true);
        $payloadData = $payload + [ 
            'contribution_id' => $contribution->id,
            'word'  => $word,
            'sense' => $sense,
            '_keywords' => $keywords,
            'notes' => $contribution->notes
        ];

        return $request->ajax()
            ? [
                'review' => $contribution, 
                'payload' => $payloadData
            ] : view('contribution.translation.edit', [
                'review' => $contribution, 
                'payload' => json_encode($payloadData)
            ]);
    }
    
    /**
     * Shows a form for a new contribution.
     *
     * @param Request $request
     * @return array|\Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create(Request $request, int $entityId = 0)
    {
        $translation = null;

        if ($entityId) {
            $translation = Translation::where('id', $entityId)
                ->with('sense', 'sense.word', 'translation_group', 'word')
                ->firstOrFail();

            $translation->_keywords = $this->_translationRepository->getKeywords($translation->sense_id, $translation->id);
        }

        return $request->ajax()
            ? $translation
            // create a payload model if a translation exists.
            : view('contribution.translation.create', $translation ? [
                'payload' => json_encode($translation)
            ] : []);
    }

    public function validateSubstep(Request $request, int $id = 0, int $substepId = 0)
    {
        // noop
    }

    public function validateBeforeSave(Request $request, int $id = 0)
    {
        $this->validateTranslationInRequest($request, $id, true);
    }

    public function populate(Contribution $contribution, Request $request)
    {
        // Modify an existing translation, if the request body specifies the ID of such an entity. This is optional functionality.
        $entity = $request->has('id') 
            ? Translation::findOrFail(intval($request->input('id'))) 
            : new Translation;

        $map = $this->mapTranslation($entity, $request);
        extract($map);

        $entity->account_id = $contribution->account_id;

        $contribution->word       = $word;
        $contribution->sense      = $sense;
        $contribution->keywords   = json_encode($keywords);

        return $entity;
    }

    public function approve(Contribution $contribution, Request $request)
    {
        $translationData = json_decode($contribution->payload, true) + [
            'account_id' => $contribution->account_id
        ];
        $translation = new Translation($translationData);
        // is the contribution a proposed change to an existing translation?
        if (array_key_exists('id', $translationData)) {
            $translation->id = intval($translationData['id']);
        }

        $keywords = json_decode($contribution->keywords, true);

        $translation = $this->_translationRepository->saveTranslation(
            $contribution->word, $contribution->sense, $translation, $keywords);
        $contribution->translation_id = $translation->id;
    }
}