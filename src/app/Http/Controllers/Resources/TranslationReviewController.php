<?php

namespace App\Http\Controllers\Resources;

use App\Models\{ Translation, TranslationReview, Word, Sense };
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranslationReviewController extends TranslationControllerBase
{
    /**
     * HTTP GET. Landing page for the current user.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $reviews = TranslationReview::forAccount($request->user()->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('translation-review.index', [
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
        $pendingReviews  = TranslationReview::whereNull('is_approved')
            ->orderBy('id', 'asc')->get();
        $rejectedReviews = TranslationReview::where('is_approved', 0)
            ->orderBy('id', 'asc')->get();
        $approvedReviews = TranslationReview::where('is_approved', 1)
            ->orderBy('id', 'asc')->get();

        return view('translation-review.list', [
            'approvedReviews' => $approvedReviews,
            'rejectedReviews' => $rejectedReviews,
            'pendingReviews'  => $pendingReviews
        ]);
    }

    /**
     * HTTP GET. Presents a the specified translation review.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Request $request, int $id) 
    {
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

        $keywords = json_decode($review->keywords);

        $translationData = json_decode($review->payload, true) + [ 
            'word'     => $review->word,
            'sense'    => $review->sense
        ];
        $translation = new Translation($translationData);

        $translation->created_at   = $review->created_at;
        $translation->account_name = $review->account->nickname;
        $translation->type         = $translation->speech->name;

        $translationData = $this->_bookAdapter->adaptTranslations([$translation]);

        return view('translation-review.show', $translationData + [
            'review'      => $review,
            'keywords'    => $keywords
        ]);
    }

    /**
     * HTTP GET. Presents a form for creating a translation review.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        return view('translation-review.create');
    }

    /**
     * HTTP GET. Presents a form for re-submitting a rejected translation review, or editing one pending review.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Request $request, int $id) 
    {
        // retrieve the review
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

        // retrieve word and sense based on the information specified in the review object. If the word does not exist in 
        // the database, create a new instance of the model for the word.
        $word = Word::forString($review->word)->firstOrNew(['word' => $review->word]);
        $sense = Sense::forString($review->word)->firstOrNew([]);
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
            'id' => $id,
            'word'  => $word,
            'sense' => $sense,
            '_keywords' => $keywords,
            'notes' => $review->notes
        ];

         return view('translation-review.edit', [
            'review' => $review, 
            'payload' => json_encode($payloadData)
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
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

        return view('translation-review.confirm-destroy', [
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
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

        return view('translation-review.confirm-reject', [
            'review' => $review
        ]);
    }

    /**
     * HTTP POST. Creates a translation review.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response 
     */
    public function store(Request $request)
    {
        $this->validateRequest($request, 0, true);

        $review = new TranslationReview;
        $review->account_id = $request->user()->id;
        $review->is_approved = null;

        $this->saveReview($review, $request);

        return response([
            'id'  => $review->id,
            'url' => route('translation-review.show', ['id' => $review->id])
        ], 201);
    }

    /**
     * HTTP PUT. Updates a specified translation review, or creates a new review in the event that
     * the specified review was previously rejected.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id, true);

        $review = TranslationReview::findOrFail($id);
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

        $this->saveReview($review, $request);

        return response([
            'id'  => $review->id,
            'url' => route('translation-review.show', ['id' => $review->id])
        ], 200);
    } 

    /**
     * HTTP PUT. Rejects a specified translation review.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function updateReject(Request $request, int $id)
    {
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

        $review->is_approved = 0;
        $review->date_reviewed = Carbon::now();
        $review->reviewed_by_account_id = $request->user()->id;
        $review->justification = $request->has('justification')
            ? $request->input('justification')
            : null;
        $review->save();

        return redirect()->route('translation-review.show', ['id' => $review->id]);
    } 

    /**
     * HTTP PUT. Approves a specified translation review.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function updateApprove(Request $request, int $id)
    {
        $review = TranslationReview::findOrFail($id);
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

        return redirect()->route('translation-review.show', ['id' => $review->id]);
    }

    /**
     * HTTP DELETE. Deletes a specified translation review.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function destroy(Request $request, int $id) 
    {
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

        $review->delete();
        
        return $request->user()->isAdministrator()
            ? redirect()->route('translation-review.list')
            : redirect()->route('translation-review.index');
    }

    /**
     * Requests persmission for the specified translation review based on the information associated with
     * the specified request.
     *
     * @param Request $request
     * @param TranslationReview $model
     * @return void
     */
    protected function requestPermission(Request $request, TranslationReview $model)
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
     * Updates the specified translation review based on the infromation provided by the request.
     *
     * @param TranslationReview $review
     * @param Request $request
     * @return void
     */
    protected function saveReview(TranslationReview $review, Request $request)
    {
        $translation = new Translation;
        $map = $this->mapTranslation($translation, $request);
        
        $translation->account_id = $review->account_id;
        extract($map);

        $review->language_id = $translation->language_id;
        $review->word        = $word;
        $review->sense       = $sense;
        $review->keywords    = json_encode($keywords);
        $review->payload     = $translation->toJson();
        $review->notes       = $request->has('notes') 
            ? $request->input('notes') 
            : null;

        $review->save();
    }
}
