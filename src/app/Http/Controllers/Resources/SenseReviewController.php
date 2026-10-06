<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Services\Senses\SenseReviewQueue;
use Illuminate\Http\Request;

/**
 * The page where an editor works through the senses no rule and no judge could place.
 */
class SenseReviewController extends Controller
{
    public function __construct(
        protected readonly SenseReviewQueue $_queue,
    ) {}

    public function index(Request $request)
    {
        $byReason = $this->_queue->summary();

        return view('admin.sense-review.index', [
            'waiting' => $byReason->sum(),
            'byReason' => $byReason,
            'reasons' => collect(ConceptReviewReason::cases())
                ->mapWithKeys(fn (ConceptReviewReason $reason) => [$reason->value => $this->describe($reason)]),
        ]);
    }

    /**
     * Why a sense is waiting, in a sentence an editor can act on.
     */
    private function describe(ConceptReviewReason $reason): string
    {
        return match ($reason) {
            ConceptReviewReason::NO_MEANING => 'The taxonomy knows no meaning for the headword, and no better word was offered.',
            ConceptReviewReason::UNSURE => 'A meaning was proposed, but not confidently enough to assign it.',
            ConceptReviewReason::UNKNOWN_WORD => 'The word proposed for the meaning is itself unknown to the taxonomy.',
            ConceptReviewReason::NOT_JUDGED => 'Nobody has judged it yet.',
            ConceptReviewReason::INVALID_ANSWER => 'The answer named a meaning that was never on offer.',
        };
    }
}
