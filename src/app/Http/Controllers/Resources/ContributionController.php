<?php

namespace App\Http\Controllers\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{
    Auth,
    View
};

use App\Http\Controllers\Controller;
use App\Models\Initialization\Morphs;
use App\Repositories\TranslationRepository;
use App\Adapters\{
    BookAdapter,
    SentenceAdapter
};
use App\Models\{
    Contribution,
    Sentence,
    SentenceFragment,
    Sense,
    Translation,
    Word
};
use App\Http\Controllers\Traits\{
    CanValidateTranslation, 
    CanMapTranslation,
    CanValidateSentence,
    CanMapSentence
};

class ContributionController extends Controller
{
    use CanMapTranslation,
        CanValidateTranslation,
        CanMapSentence,
        CanValidateSentence {
        validateSentenceInRequest as private validateSentenceInRequestImpl;
        validateFragmentsInRequest as private validateFragmentsInRequestImpl;
    }
    
    protected $_sentenceAdapter;
    protected $_bookAdapter;
    protected $_translationRepository;

    public function __construct(BookAdapter $bookAdapter, SentenceAdapter $sentenceAdapter, 
        TranslationRepository $translationRepository) 
    {
        $this->_bookAdapter = $bookAdapter;
        $this->_sentenceAdapter = $sentenceAdapter;
        $this->_translationRepository = $translationRepository;
    }

    /**
     * HTTP GET. Landing page for the current user.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $reviews = Contribution::forAccount($request->user()->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('contribution.index', [
            'reviews' => $reviews
        ]);
    }
    
    /**
     * HTTP GET. Page for administrators where all reviews are listed.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $pendingReviews  = Contribution::whereNull('is_approved')
            ->orderBy('id', 'asc')->get();
        $rejectedReviews = Contribution::where('is_approved', 0)
            ->orderBy('id', 'asc')->get();
        $approvedReviews = Contribution::where('is_approved', 1)
            ->orderBy('id', 'asc')->get();

        return view('contribution.list', [
            'approvedReviews' => $approvedReviews,
            'rejectedReviews' => $rejectedReviews,
            'pendingReviews'  => $pendingReviews
        ]);
    }

    /**
     * HTTP GET. Presents a the specified contribution.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Request $request, int $id) 
    {
        $review = Contribution::findOrFail($id);
        $this->requestPermission($request, $review);

        return $this->unfoldMorph($review->type, [
            Translation::class => function () use($review) {
                return $this->showTranslation($review);
            },
            Sentence::class => function() use($review) {
                return $this->showSentence($review);
            }
        ]);
    }

    private function showTranslation(Contribution $review)
    {
        $keywords = json_decode($review->keywords);

        $translationData = json_decode($review->payload, true);
        if (! is_array($translationData)) {
            abort(400, 'Unrecognised payload: '.$review->payload);
        }

        $translationData = $translationData + [ 
            'word'     => $review->word,
            'sense'    => $review->sense
        ];
        $translation = new Translation($translationData);

        $translation->created_at   = $review->created_at;
        $translation->account_name = $review->account->nickname;
        $translation->type         = $translation->speech->name;

        $translationData = $this->_bookAdapter->adaptTranslations([$translation]);

        return view('contribution.'.$review->type.'.show', $translationData + [
            'review'      => $review,
            'keywords'    => $keywords
        ]);
    }

    private function showSentence(Contribution $review)
    {
        $payload = json_decode($review->payload);
        $sentence = $payload->sentence;
        $fragmentData = $this->createFragmentDataFromPayload($payload);

        return view('contribution.'.$review->type.'.show', [
            'fragmentData' => json_encode($fragmentData),
            'sentence' => $sentence,
            'review'  => $review
        ]);
    }

    /**
     * HTTP GET. Presents a form for creating a contribution.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request, string $morph = null)
    {
        $viewName = 'contribution.'.$morph.'.create';
        if (! View::exists($viewName)) {
            $this->unrecognisedMorph($morph);
        }
        
        return view($viewName);
    }

    /**
     * HTTP GET. Presents a form for re-submitting a rejected contribution, or editing one pending review.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Request $request, int $id) 
    {
        // retrieve the review
        $review = Contribution::findOrFail($id);
        $this->requestPermission($request, $review);

        if ($review->is_approved) {
            abort(400, $review->word.' is already approved.');
        }

        return $this->unfoldMorph($review->type, [
            Translation::class => function() use($request, $review) {
                return $this->editTranslation($request, $review);
            },
            Sentence::class => function () use($request, $review) {
                return $this->editSentence($request, $review);
            }
        ]);
    }

    private function editTranslation(Request $request, Contribution $review)
    {
        // retrieve word and sense based on the information specified in the review object. If the word does not exist in 
        // the database, create a new instance of the model for the word.
        $word = Word::forString($review->word)->firstOrNew(['word' => $review->word]);
        $senseWord = Word::forString($review->sense)->firstOrNew([]);
        $sense = Sense::where('id', $senseWord->id)->with('word')->firstOrNew([]);
        if (! $sense->id) {
            // _word_ is actually a navigation property.
            $sense->word = Word::forString($review->sense)->firstOrNew(['word' => $review->sense]);
        }

        // Convert keyword strings to Word objects
        $keywords = array_map(function ($k) {
            return new Word([ 'word' => $k ]);
        }, json_decode($review->keywords));

        // extend the payload with information necessary for the form.
        $payloadData = json_decode($review->payload, true) + [ 
            'id' => $review->id,
            'word'  => $word,
            'sense' => $sense,
            '_keywords' => $keywords,
            'notes' => $review->notes
        ];

         return view('contribution.'.$review->type.'.edit', [
            'review' => $review, 
            'payload' => json_encode($payloadData)
        ]);
    }

    private function editSentence(Request $request, Contribution $review)
    {
        $payload = json_decode($review->payload);
        $sentence = $payload->sentence;
        $sentence->notes = $review->notes ?: '';
        $fragmentData = $this->createFragmentDataFromPayload($payload);

        return view('contribution.'.$review->type.'.edit', [
            'review' => $review,
            'sentence' => json_encode($sentence),
            'fragmentData' => json_encode($fragmentData)
        ]);
    }

    /**
     * HTTP GET. Confirm dialogue for deleting a specified submission. 
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function confirmDestroy(Request $request, int $id)
    {
        $review = Contribution::findOrFail($id);
        $this->requestPermission($request, $review);

        return view('contribution.confirm-destroy', [
            'review' => $review
        ]);
    }

    /**
     * HTTP GET. Confirm dialogue for rejecting a specified submission.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function confirmReject(Request $request, int $id)
    {
        $review = Contribution::findOrFail($id);
        $this->requestPermission($request, $review);

        return view('contribution.confirm-reject', [
            'review' => $review
        ]);
    }

    /**
     * HTTP POST. Creates a contribution.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response 
     */
    public function store(Request $request)
    {
        $this->validateRequest($request, 0);

        $review = new Contribution;
        $review->account_id = $request->user()->id;
        $review->is_approved = null;

        $this->saveContribution($review, $request);

        return response([
            'id'  => $review->id,
            'url' => route('contribution.show', ['id' => $review->id])
        ], 201);
    }

    /**
     * HTTP PUT. Updates a specified contribution, or creates a new review in the event that
     * the specified review was previously rejected.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id);

        $review = Contribution::findOrFail($id);
        $this->requestPermission($request, $review);
        
        if ($review->is_approved === 1) {
            abort(400);
        }

        if ($review->is_approved === 0) {
            $review = $review->replicate();
            $review->is_approved = null;
            $review->date_reviewed = null;
            $review->reviewed_by_account_id = null;
            $review->justification = null;
        }

        $this->saveContribution($review, $request);

        return response([
            'id'  => $review->id,
            'url' => route('contribution.show', ['id' => $review->id])
        ], 200);
    } 

    /**
     * HTTP PUT. Rejects a specified contribution.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function updateReject(Request $request, int $id)
    {
        $review = Contribution::findOrFail($id);
        $this->requestPermission($request, $review);

        $review->is_approved = 0;
        $review->date_reviewed = Carbon::now();
        $review->reviewed_by_account_id = $request->user()->id;
        $review->justification = $request->has('justification')
            ? $request->input('justification')
            : null;
        $review->save();

        return redirect()->route('contribution.show', ['id' => $review->id]);
    } 

    /**
     * HTTP PUT. Approves a specified contribution.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function updateApprove(Request $request, int $id)
    {
        $review = Contribution::findOrFail($id);
        $this->requestPermission($request, $review);

        $translationData = json_decode($review->payload, true) + [
            'account_id' => $review->account_id
        ];
        $translation = new Translation($translationData);
        $keywords = json_decode($review->keywords, true);
        $this->_translationRepository->saveTranslation($review->word, $review->sense, $translation, $keywords);

        $review->is_approved = 1;
        $review->date_reviewed = Carbon::now();
        $review->reviewed_by_account_id = $request->user()->id;
        $review->translation_id = $translation->id;
        $review->save();

        return redirect()->route('contribution.show', ['id' => $review->id]);
    }

    /**
     * HTTP DELETE. Deletes a specified contribution.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function destroy(Request $request, int $id) 
    {
        $review = Contribution::findOrFail($id);
        $this->requestPermission($request, $review);

        $review->delete();
        
        return $request->user()->isAdministrator()
            ? redirect()->route('contribution.list')
            : redirect()->route('contribution.index');
    }

    /**
     * HTTP POST. Validate sentence data in the request.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function validateSentenceInRequest(Request $request, int $id = 0)
    {
        $this->validateSentenceInRequestImpl($request, $id, true);
        return response(null, 204);
    }

    /**
     * HTTP POST. Validate sentence fragments in the request.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function validateFragmentsInRequest(Request $request)
    {
        $this->validateFragmentsInRequestImpl($request);
        return response(null, 204);
    }

    protected function validateRequest(Request $request, int $id)
    {
        $this->validate($request, [
            'morph' => 'required|string'
        ]);

        $this->unfoldMorph($request, [
            Translation::class => function() use($request, $id) {
                $this->validateTranslationInRequest($request, $id, true);

                return true;
            },
            Sentence::class => function () use($request, $id) {
                $this->validateSentenceInRequest($request, $id);
                $this->validateFragmentsInRequest($request);

                return true;
            }
        ]);
    }

    /**
     * Requests permission for the specified contribution based on the information associated with
     * the specified request.
     *
     * @param Request $request
     * @param Contribution $model
     * @return void
     */
    protected function requestPermission(Request $request, Contribution $model)
    {
        $user = $request->user();

        if ($user->isAdministrator()) {
            return;
        }

        if ($user->id === $model->account_id) {
            return;
        }

        abort(401);
    }

    /**
     * Updates the specified contribution based on the infromation provided by the request.
     *
     * @param Contribution $review
     * @param Request $request
     * @return void
     */
    protected function saveContribution(Contribution $review, Request $request)
    {
        $entity = $this->unfoldMorph($request, [
            Translation::class => function() use($review, $request) {
                return $this->saveTranslationContribution($review, $request);
            },
            Sentence::class => function () use($review, $request) {
                return $this->saveSentenceContribution($review, $request);
            }
        ]);

        $review->type        = Morphs::getAlias($entity);
        $review->language_id = $entity->language_id;
        $review->notes       = $request->has('notes') 
            ? $request->input('notes') 
            : null;

        // payloads might already be configured at this point, either by the save methods
        // or earlier in the call stack.
        if (empty($review->payload)) {
            $review->payload = $entity instanceof Jsonable
                ? $entity->toJson()
                : json_encode($entity);
        }

        $review->save();
    }

    protected function saveTranslationContribution(Contribution $review, Request $request)
    {
        $entity = new Translation;
        $map = $this->mapTranslation($entity, $request);
        extract($map);

        $entity->account_id = $review->account_id;

        $review->word       = $word;
        $review->sense      = $sense;
        $review->keywords   = json_encode($keywords);

        return $entity;
    }

    protected function saveSentenceContribution(Contribution $review, Request $request)
    {
        $entity = new Sentence;
        $map = $this->mapSentence($entity, $request);

        $entity->account_id = $review->account_id;
    
        $review->payload = json_encode($map);
        $review->word    = $entity->name;
        $review->sense   = 'text';
        
        return $entity;
    }

    protected function unrecognisedMorph(string $morph)
    {
        abort(400, 'Unrecognised morph "'.$morph.'".');
    }

    protected function unfoldMorph($morphOrRequest, array $cases)
    {
        $modelName = Morphs::getMorphedModel(($morphOrRequest instanceof Request) 
            ? $morphOrRequest->input('morph') : $morphOrRequest
        );

        if (! array_key_exists($modelName, $cases)) {
            return;
        }

        return $cases[$modelName]();
    }

    private function createFragmentDataFromPayload(\stdClass $payload)
    {
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

        $result = $this->_sentenceAdapter->adaptFragments($fragments);
        return $result;
    }
}
