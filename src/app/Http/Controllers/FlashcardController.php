<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Helpers\MarkdownParser;
use App\Events\FlashcardFlipped;
use App\Models\{
    Flashcard, 
    FlashcardResult, 
    Language, 
    Translation,
    Speech
};

class FlashcardController extends Controller
{
    public function index(Request $request)
    {
        $flashcards = Flashcard::all()
            ->sortBy('language.name');

        // Retrieve flashcard history and compile a 'statistics' by language associative array. 
        $accountId = $request->user()->id;

        // Traverse flashcard results and count failures versus successes.
        $statistics = FlashcardResult::where('account_id', $accountId)
            ->join('flashcards', 'flashcard_results.flashcard_id', 'flashcards.id')
            ->join('languages', 'flashcards.language_id', 'languages.id')
            ->groupBy([ 'languages.name', 'correct' ])
            ->select('languages.name', 'correct', DB::raw('count(*) as numberOfResults'))
            ->get();

        // Group the results by language, creating an associative array with the keys _correct_ and _wrong_,
        // as well as a general element called _total, which is the total number of cards the user has examined.
        $statisticsByLanguage = [];
        $numberOfWrongCards = 0;
        $numberOfCorrectCards = 0;
        foreach ($statistics as $statistic) {
            // Create the key if it does not exist
            if (! isset($statisticsByLanguage[$statistic->name])) {
                $statisticsByLanguage[$statistic->name] = [
                    'total' => 0
                ];
            }

            $statisticsByLanguage[$statistic->name][$statistic->correct ? 'correct' : 'wrong'] = $statistic->numberOfResults;
            $statisticsByLanguage[$statistic->name]['total'] += $statistic->numberOfResults;

            if ($statistic->correct) {
                $numberOfCorrectCards += $statistic->numberOfResults;
            } else {
                $numberOfWrongCards += $statistic->numberOfResults;
            }
        }

        $statisticsByLanguage['_total_correct'] = $numberOfCorrectCards;
        $statisticsByLanguage['_total_wrong'] = $numberOfWrongCards;
        $statisticsByLanguage['_total'] = $numberOfCorrectCards + $numberOfWrongCards;

        return view('flashcard.index', ['flashcards' => $flashcards, 'statistics' => $statisticsByLanguage]);
    }

    public function cards(Request $request, int $id)
    {
        $flashcard = Flashcard::findOrFail($id);
        return view('flashcard.cards', ['flashcard' => $flashcard]);
    }

    public function list(Request $request, int $id)
    {
        $flashcard = Flashcard::findOrFail($id);
        $userId = $request->user()->id;
        $results = FlashcardResult::forAccount($userId)
            ->where('flashcard_id', $id)
            ->with('translation', 'translation.word')
            ->orderBy('id', 'desc')
            ->get();

        return view('flashcard.list', [
            'results'  => $results,
            'flashcard' => $flashcard
        ]);
    }

    public function card(Request $request, int $n = 0)
    {
        $this->validate($request, [
            'id'    => 'numeric|exists:flashcards,id',
            'not'   => 'sometimes|array',
            'not.*' => 'sometimes|numeric'
        ]);

        $id = intval( $request->input('id') );
        
        $not = [];
        if ($request->has('not')) {
            $not = $request->input('not');
        }

        // retrieve the flashcard for its language and translation group 
        // which will be used to filter amongst the translations.
        $flashcard = Flashcard::find($id);

        // select a random translation
        $q = Translation::active()
            ->where([
                ['language_id', $flashcard->language_id],
                ['translation_group_id', $flashcard->translation_group_id]
            ])
            ->inRandomOrder();

        // the _not_ list contains reference to previous flash cards, to avoid
        // repetition.
        if (! empty($not)) {
            $q = $q->whereNotIn('id', $not);
        }
        
        // retrieve the random translation or fail (if none exists!)
        $translation = $q->firstOrFail();

        // ignore untranslated words
        if (mb_strtolower($translation->translation) === mb_strtolower($translation->word->word)) {
            // maximum 10 levels of recursion
            if ($n > 10) {
                abort(404);
            }

            return $this->card($request, $n + 1);
        }

        // Compile a list of options
        $options = [$translation->translation];

        // group verbs w/ one another as they tend to be in the infinitive
        // in English.
        $filters = [
            ['id', '<>', $translation->id],
            ['translation', '<>', $translation->translation]
        ];
        $verbSpeech = Speech::where('name', 'verb')->first();
        if ($verbSpeech && $translation->speech_id === $verbSpeech->id) {
            $filters[] = ['speech_id', $verbSpeech->id];
        }

        $fakeOptions = $q->where($filters)
            ->select('translation')
            ->take(4)
            ->get();

        foreach ($fakeOptions as $option) {
            $options[] = $option->translation;
        }

        shuffle($options);

        return [ 
            'word'           => $translation->word->word,
            'options'        => $options,
            'translation_id' => $translation->id 
         ];
    }

    public function test(Request $request)
    {
        $this->validate($request, [
            'flashcard_id'   => 'numeric|exists:flashcards,id',
            'translation_id' => 'numeric|exists:translations,id',
            'translation'    => 'string'
        ]);

        $translationId = intval( $request->input('translation_id') );
        $translation = Translation::where('id', $translationId)
            ->select('translation', 'source', 'comments')
            ->firstOrFail();

        $offeredGloss = $request->input('translation');
        $ok = strcmp($translation->translation, $offeredGloss) === 0;

        $account = $request->user();

        $result = new FlashcardResult;

        $result->flashcard_id   = intval( $request->input('flashcard_id') );
        $result->account_id     = $account->id;
        $result->translation_id = $translationId;
        $result->expected       = $translation->translation;
        $result->actual         = $offeredGloss;
        $result->correct        = $ok;

        $result->save();

        // parse comments, as it's saved as markdown
        if (! empty($translation->comments)) {
            $parser = new MarkdownParser();
            $translation->comments = $parser->parse($translation->comments);
        }

        // Record the progress
        $numberOfCards = FlashcardResult::where('account_id', $result->account_id)->count();
        event(new FlashcardFlipped($result, $numberOfCards));

        return [
            'correct'     => $ok,
            'translation' => $translation
        ];
    }
}
