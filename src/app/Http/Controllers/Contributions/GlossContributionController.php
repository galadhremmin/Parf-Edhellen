<?php

namespace App\Http\Controllers\Contributions;

use App\Adapters\BookAdapter;
use App\Http\Controllers\Abstracts\Controller;
use App\Http\Controllers\Traits\CanMapGloss;
use App\Http\Controllers\Traits\CanValidateGloss;
use App\Models\Account;
use App\Models\Contribution;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryDetail;
use App\Models\Sense;
use App\Models\Gloss;
use App\Models\Word;
use App\Repositories\LexicalEntryInflectionRepository;
use App\Repositories\LexicalEntryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class GlossContributionController extends Controller implements IContributionController
{
    use CanMapGloss,
        CanValidateGloss;

    private BookAdapter $_bookAdapter;

    private LexicalEntryRepository $_lexicalEntryRepository;

    private LexicalEntryInflectionRepository $_lexicalEntryInflectionRepository;

    public function __construct(BookAdapter $bookAdapter, LexicalEntryRepository $lexicalEntryRepository,
        LexicalEntryInflectionRepository $lexicalEntryInflectionRepository)
    {
        $this->_bookAdapter = $bookAdapter;
        $this->_lexicalEntryRepository = $lexicalEntryRepository;
        $this->_lexicalEntryInflectionRepository = $lexicalEntryInflectionRepository;
    }

    public function getViewModel(Contribution $contribution): ViewModel
    {
        $keywords = json_decode($contribution->keywords);

        $glossData = json_decode($contribution->payload, true);
        if (! is_array($glossData)) {
            abort(400, 'Unrecognised payload: '.$contribution->payload);
        }

        $translations = $this->getTranslationsFromPayload($glossData);
        $details = $this->getDetailsFromPayload($glossData);
        $parentGloss = array_key_exists('id', $glossData)
            ? $glossData['id'] : 0;
        $glossData = $glossData + [
            'sense' => $contribution->sense,
        ];
        $gloss = new LexicalEntry($glossData);
        $glosses = [$gloss];

        $gloss->created_at = $contribution->created_at;
        $gloss->account_name = $contribution->account->nickname;
        $gloss->type = $gloss->speech->name;

        // Hack for assigning to the relation _glosses_ without saving them to the database.
        $gloss->setRelation('glosses', new Collection($translations));
        $gloss->setRelation('word', new Word(['word' => $contribution->word]));
        $gloss->setRelation('lexical_entry_details', new Collection($details));

        $glossData = $this->_bookAdapter->adaptLexicalEntries($glosses);

        $model = $glossData + [
            'keywords' => $keywords,
            'parentGloss' => $parentGloss,
        ];

        return new ViewModel($contribution, 'contribution.gloss._show', $model);
    }

    /**
     * Creates the view model for the gloss editing view.
     */
    public function getEditViewModel(Contribution $contribution)
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
            return new Word(['word' => $k]);
        }, json_decode($contribution->keywords));

        // extend the payload with information necessary for the form.
        $payload = json_decode($contribution->payload, true);
        $account = Account::find(isset($payload['account_id'])
            ? $payload['account_id']
            : $contribution->account_id
        );
        $translations = $this->getTranslationsFromPayload($payload);
        $details = $this->getDetailsFromPayload($payload);

        return $payload + [
            'contribution_id' => $contribution->id,
            'account' => $account,
            'word' => $word,
            'sense' => $sense,
            'keywords' => $keywords,
            'notes' => $contribution->notes,
            'translations' => $translations,
            'lexical_entry_details' => $details,
        ];
    }

    /**
     * HTTP GET. Opens a view for editing a gloss contribution.
     *
     * @return array|\Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit(Contribution $contribution, Request $request)
    {
        $payloadData = $this->getEditViewModel($contribution);
        $inflections = $this->_lexicalEntryInflectionRepository->getInflectionsForLexicalEntry(
            array_key_exists('id', $payloadData) ? $payloadData['id'] : 0
        );

        $viewModel = [
            'review' => $contribution,
            'payload' => $payloadData,
            'inflections' => $inflections,
            'form_restrictions' => ['gloss'],
        ];

        return $request->ajax() //
            ? $viewModel : view('contribution.gloss.edit', $viewModel);
    }

    /**
     * Shows a form for a new contribution.
     *
     * @return array|\Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'entity_id' => 'sometimes|numeric',
            'lexical_entry_version_id' => 'sometimes|numeric',
        ]);
        $entityId = isset($data['entity_id']) ? intval($data['entity_id']) : 0;
        $lexicalEntryVersionId = isset($data['lexical_entry_version_id']) ? intval($data['lexical_entry_version_id']) : 0;

        $gloss = null;
        if ($entityId !== 0) {
            $glosses = $this->_lexicalEntryRepository->getLexicalEntry($entityId);
            if (! $glosses->isEmpty()) {
                $gloss = $glosses->first();
            }
        }
        if ($lexicalEntryVersionId !== 0) {
            $gloss = $this->_lexicalEntryRepository->getLexicalEntryFromVersion($lexicalEntryVersionId);
        }

        $inflections = [];
        if ($gloss !== null) {
            $gloss->keywords = $this->_lexicalEntryRepository->getKeywords($gloss->sense_id, $gloss->id);
            $inflections = $this->_lexicalEntryInflectionRepository->getInflectionsForLexicalEntry($gloss->id);
        }

        return $request->ajax()
            ? $gloss
            // create a payload model if a gloss exists.
            : view('contribution.gloss.create', $gloss ? [
                'payload' => $gloss,
                'inflections' => $inflections,
            ] : []);
    }

    public function validateSubstep(Request $request, int $id = 0, int $substepId = 0): mixed
    {
        // noop
        return true;
    }

    public function validateBeforeSave(Request $request, int $id = 0): bool
    {
        $this->validateGlossInRequest($request, $id);

        return true;
    }

    public function populate(Contribution $contribution, Request $request)
    {
        // Modify an existing lexical entry, if the request body specifies the ID of such an entity. This is optional functionality.
        if ($request->has('id')) {
            $entity = LexicalEntry::findOrFail(intval($request->input('id')));
        } else {
            $entity = new LexicalEntry;
        }

        $map = $this->mapGloss($entity, $request);
        extract($map);

        if (! $request->user()->isAdministrator()) {
            $entity->account_id = $request->user()->id;
        }

        $entity->_translations = $translations;
        $entity->_details = $details;

        $contribution->word = $word;
        $contribution->sense = $sense;
        $contribution->keywords = json_encode($keywords);
        $contribution->language_id = $entity->language_id;

        if ($entity->exists) {
            $contribution->lexical_entry_id = $entity->id;
        }

        return $entity;
    }

    /**
     * Disable change detection within the parent controller as the payload is always
     * populated by the `populate` method.
     */
    public function disableChangeDetection(): bool
    {
        return false;
    }

    public function approve(Contribution $contribution, Request $request): int
    {
        $glossData = json_decode($contribution->payload, true) + [
            'account_id' => $contribution->account_id,
        ];

        $translations = $this->getTranslationsFromPayload($glossData);
        $details = $this->getDetailsFromPayload($glossData);

        $gloss = new LexicalEntry($glossData);
        // is the contribution a proposed change to an existing lexical entry?
        if (array_key_exists('id', $glossData)) {
            $id = intval($glossData['id']);
            $originalGloss = $this->_lexicalEntryRepository->getLexicalEntry($id);
            if ($originalGloss->count() > 0) {
                $gloss = $originalGloss->first();
                $gloss->fill($glossData);
            }
        }

        $keywords = json_decode($contribution->keywords, true);

        $gloss = $this->_lexicalEntryRepository->saveLexicalEntry(
            $contribution->word, $contribution->sense, $gloss, $translations, $keywords, $details);

        return $gloss->id;
    }

    /**
     * Gets translations (if available) from the specified array.
     *
     * @param  $glossData  array payload from persistence layer
     * @return array
     */
    private function getTranslationsFromPayload(array &$glossData)
    {
        // Retrieve translations, which should either be a an array stored
        // upon the data carrier with the key "_translations" (API v.2) or
        // a string with the key "translation", also on the data carrier
        // (API v.1).
        $apiVersion = isset($glossData['_translations'])
            ? 2
            : (isset($glossData['translation']) ? 1 : 0);

        switch ($apiVersion) {
            case 2:
                $translations = array_map(function ($data) {
                    return new Gloss($data);
                }, $glossData['_translations']);

                unset($glossData['_translations']);
                break;

            case 1:
                $translations = [
                    new Gloss([
                        'translation' => $glossData['translation'],
                    ]),
                ];
                break;
            default:
                abort(400, 'Unrecognised payload.');
        }

        return $translations;
    }

    /**
     * Gets gloss details from the specified array.
     *
     * @param  $glossData  array payload from persistence layer
     * @return array
     */
    private function getDetailsFromPayload(array &$glossData)
    {
        if (! isset($glossData['_details'])) {
            return [];
        }

        $details = array_map(function ($data) {
            return new LexicalEntryDetail($data);
        }, $glossData['_details']);
        unset($glossData['_details']);

        return $details;
    }
}
