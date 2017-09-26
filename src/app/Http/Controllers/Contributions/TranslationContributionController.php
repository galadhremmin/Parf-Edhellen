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

        $translationData = $translationData + [ 
            'word'     => $contribution->word,
            'sense'    => $contribution->sense
        ];
        $translation = new Translation($translationData);

        $translation->created_at   = $contribution->created_at;
        $translation->account_name = $contribution->account->nickname;
        $translation->type         = $translation->speech->name;

        $translationData = $this->_bookAdapter->adaptTranslations([$translation]);

        return view('contribution.'.$contribution->type.'.show', $translationData + [
            'review'      => $contribution,
            'keywords'    => $keywords
        ]);
    }

    /**
     * HTTP GET. Opens a view for editing a translation contribution.
     *
     * @param Request $request
     * @param Contribution $contribution
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
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
        $payloadData = json_decode($contribution->payload, true) + [ 
            'id' => $contribution->id,
            'word'  => $word,
            'sense' => $sense,
            '_keywords' => $keywords,
            'notes' => $contribution->notes
        ];

         return view('contribution.'.$contribution->type.'.edit', [
            'review' => $contribution, 
            'payload' => json_encode($payloadData)
        ]);
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
        $entity = new Translation;
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
        $keywords = json_decode($contribution->keywords, true);

        $this->_translationRepository->saveTranslation($contribution->word, $contribution->sense, 
            $translation, $keywords);

        $contribution->translation_id = $translation->id;
    }
}