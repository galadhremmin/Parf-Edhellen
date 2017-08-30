<?php

namespace App\Http\Controllers\Resources;

use App\Adapters\BookAdapter;
use App\Models\{ Translation, TranslationReview, Keyword, Word, Language };

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranslationReviewController extends TranslationControllerBase
{
    protected $_bookAdapter;

    public function __construct(BookAdapter $adapter) 
    {
        $this->_bookAdapter = $adapter;
    }

    public function index(Request $request)
    {
        $reviews = TranslationReview::forAccount($request->user()->id)
            ->get();

        return view('translation-review.index', [
            'reviews' => $reviews
        ]);
    }
    
    public function list(Request $request)
    {
        $pendingReviews  = TranslationReview::whereNull('is_approved')->get();
        $rejectedReviews = TranslationReview::where('is_approved', 0)->get();
        $approvedReviews = TranslationReview::where('is_approved', 1)->get();

        return view('translation-review.list', [
            'approvedReviews' => $approvedReviews,
            'rejectedReviews' => $rejectedReviews,
            'pendingReviews'  => $pendingReviews
        ]);
    }

    public function show(Request $request, $id) 
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

    public function create(Request $request)
    {
        return view('translation-review.create');
    }

    public function edit(Request $request, int $id) 
    {
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

         return view('translation-review.edit', [
            'review' => $review
        ]);
    }

    public function confirmDestroy(Request $request, int $id)
    {
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

        return view('translation-review.confirm-destroy', [
            'review' => $review
        ]);
    }

    public function confirmReject(Request $request, int $id)
    {
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

        return view('translation-review.confirm-reject', [
            'review' => $review
        ]);
    }

    public function confirmApprove(Request $request, int $id)
    {
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

        return view('translation-review.confirm-approve', [
            'review' => $review
        ]);
    }

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

    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id, true);

        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);
        
        $this->saveReview($review, $request);

        return response([
            'id'  => $review->id,
            'url' => route('translation-review.show', ['id' => $review->id])
        ], 200);
    } 

    public function destroy(Request $request, int $id) 
    {
        $review = TranslationReview::findOrFail($id);
        $this->requestPermission($request, $review);

        $review->delete();
        
        return $request->user()->isAdministrator()
            ? redirect()->route('translation-review.list')
            : redirect()->route('translation-review.index');
    }

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
