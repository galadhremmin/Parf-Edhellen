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
use App\Security\RoleConstants;
use App\Repositories\LexicalEntryInflectionRepository;
use App\Repositories\LexicalEntryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class LexicalEntryContributionController extends Controller implements IContributionController
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

        $glosses = $this->getGlossesFromPayload($glossData);
        $details = $this->getDetailsFromPayload($glossData);
        $parentLexicalEntry = array_key_exists('id', $glossData)
            ? $glossData['id'] : 0;
        $glossData = $glossData + [
            'sense' => $contribution->sense,
        ];
        $entry = new LexicalEntry($glossData);

        $entry->created_at = $contribution->created_at;
        $entry->account_name = $contribution->account->nickname;
        $entry->type = $entry->speech->name;

        // Hack for assigning to the relation _glosses_ without saving them to the database.
        $entry->setRelation('glosses', new Collection($glosses));
        $entry->setRelation('word', new Word(['word' => $contribution->word]));
        $entry->setRelation('lexical_entry_details', new Collection($details));

        $entryData = $this->_bookAdapter->adaptLexicalEntries([$entry]);

        $model = $entryData + [
            'keywords' => $keywords,
            'parentLexicalEntry' => $parentLexicalEntry,
        ];

        return new ViewModel($contribution, 'contribution.lexical_entry._show', $model);
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
        $glosses = $this->getGlossesFromPayload($payload);
        $details = $this->getDetailsFromPayload($payload);

        return $payload + [
            'contribution_id' => $contribution->id,
            'account' => $account,
            'word' => $word,
            'sense' => $sense,
            'keywords' => $keywords,
            'notes' => $contribution->notes,
            'glosses' => $glosses,
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
            'form_restrictions' => ['lexical_entry'],
        ];

        return $request->ajax() //
            ? $viewModel : view('contribution.lexical_entry.edit', $viewModel);
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

        $lexicalEntry = null;
        if ($entityId !== 0) {
            $lexicalEntries = $this->_lexicalEntryRepository->getLexicalEntry($entityId);
            if (! $lexicalEntries->isEmpty()) {
                $lexicalEntry = $lexicalEntries->first();
            }
        }
        if ($lexicalEntryVersionId !== 0) {
            $lexicalEntry = $this->_lexicalEntryRepository->getLexicalEntryFromVersion($lexicalEntryVersionId);
        }

        $inflections = [];
        if ($lexicalEntry !== null) {
            $lexicalEntry->keywords = $this->_lexicalEntryRepository->getKeywords($lexicalEntry->sense_id, $lexicalEntry->id);
            $inflections = $this->_lexicalEntryInflectionRepository->getInflectionsForLexicalEntry($lexicalEntry->id);
        }

        return $request->ajax()
            ? $lexicalEntry
            // create a payload model if a gloss exists.
            : view('contribution.lexical_entry.create', $lexicalEntry ? [
                'payload' => $lexicalEntry,
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

        $map = $this->mapLexicalEntry($entity, $request);
        extract($map);

        if (! $request->user()->isAdministrator() && //
            ! $request->user()->memberOf(RoleConstants::Reviewers)) {
            $entity->account_id = $request->user()->id;
        }

        $entity->_glosses = $glosses;
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
        $payload = json_decode($contribution->payload, true) + [
            'account_id' => $contribution->account_id,
        ];

        $glosses = $this->getGlossesFromPayload($payload);
        $details = $this->getDetailsFromPayload($payload);

        $entry = new LexicalEntry($payload);

        // is the contribution a proposed change to an existing lexical entry?
        if (array_key_exists('id', $payload)) {
            $id = intval($payload['id']);
            $originalEntry = $this->_lexicalEntryRepository->getLexicalEntry($id);
            if ($originalEntry->count() > 0) {
                $entry = $originalEntry->first();
                $entry->fill($payload);
            }
        }

        $keywords = json_decode($contribution->keywords, true);

        $entry = $this->_lexicalEntryRepository->saveLexicalEntry($contribution->word, 
            $contribution->sense, $entry, $glosses, $keywords, $details);

        return $entry->id;
    }

    /**
     * Gets translations (if available) from the specified array.
     *
     * @param  $payload  array payload from persistence layer
     * @return array
     */
    protected function getGlossesFromPayload(array &$payload)
    {
        $glosses = array_map(function ($data) {
            return new Gloss($data);
        }, $payload['_glosses']);

        unset($payload['_glosses']);

        return $glosses;
    }

    /**
     * Gets gloss details from the specified array.
     *
     * @param  $glossData  array payload from persistence layer
     * @return array
     */
    protected function getDetailsFromPayload(array &$payload)
    {
        if (! isset($payload['_details'])) {
            return [];
        }

        $details = array_map(function ($data) {
            return new LexicalEntryDetail($data);
        }, $payload['_details']);
        unset($payload['_details']);

        return $details;
    }
}
