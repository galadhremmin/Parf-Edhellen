<?php

namespace App\Http\Controllers;

use App\Adapters\BookAdapter;
use App\Events\FlashcardFlipped;
use App\Helpers\StringHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Flashcard;
use App\Models\FlashcardResult;
use App\Models\Gloss;
use App\Models\Speech;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FlashcardController extends Controller
{
    private BookAdapter $_bookAdapter;

    public function __construct(BookAdapter $bookAdapter)
    {
        $this->_bookAdapter = $bookAdapter;
    }

    public function index(Request $request)
    {
        $flashcards = Flashcard::all()
            ->sortBy('language.name');

        $user = $request->user();

        return view('flashcard.index', [
            'flashcards' => $flashcards,
            'statistics' => $this->getStatisticsByUser($user),
        ]);
    }

    private function getStatisticsByUser($user)
    {
        // Retrieve flashcard history and compile a 'statistics' by language associative array.
        if (! $user) {
            return null;
        }

        $accountId = $user->id;

        // Traverse flashcard results and count failures versus successes.
        $statistics = FlashcardResult::where('account_id', $accountId)
            ->join('flashcards', 'flashcard_results.flashcard_id', 'flashcards.id')
            ->join('languages', 'flashcards.language_id', 'languages.id')
            ->groupBy(['languages.name', 'correct'])
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
                    'total' => 0,
                    'correct' => 0,
                    'wrong' => 0,
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

        return $statisticsByLanguage;
    }

    public function cards(Request $request, int $id)
    {
        $flashcard = Flashcard::findOrFail($id);

        return view('flashcard.cards', [
            'flashcard' => $flashcard,
            'user' => $request->user(),
        ]);
    }

    public function list(Request $request, int $id)
    {
        $flashcard = Flashcard::findOrFail($id);
        $userId = $request->user()->id;
        $results = FlashcardResult::forAccount($userId)
            ->where('flashcard_id', $id)
            ->with('gloss', 'gloss.word')
            ->orderBy('id', 'desc')
            ->get();

        return view('flashcard.list', [
            'results' => $results,
            'flashcard' => $flashcard,
        ]);
    }

    public function card(Request $request, int $n = 0)
    {
        $this->validate($request, [
            'id' => 'numeric|exists:flashcards,id',
            'not' => 'sometimes|array',
            'not.*' => 'sometimes|numeric',
        ]);

        $id = intval($request->input('id'));

        $not = [];
        if ($request->has('not')) {
            $not = $request->input('not');
        }

        // retrieve the flashcard for its language and gloss group
        // which will be used to filter amongst the glosses.
        $flashcard = Flashcard::findOrFail($id);

        // select a random gloss
        $q = Gloss::active()
            ->with('translations')
            ->where([
                ['language_id', $flashcard->language_id],
                ['gloss_group_id', $flashcard->gloss_group_id],
            ])
            ->inRandomOrder();

        // the _not_ list contains reference to previous flash cards, to avoid
        // repetition.
        if (! empty($not)) {
            $q = $q->whereNotIn('id', $not);
        }

        // retrieve the random gloss or fail (if none exists!)
        $gloss = $q->firstOrFail();

        // ignore untranslated words
        $lowercaseWord = StringHelper::normalize($gloss->word->word, false);
        $translations = $gloss->translations->filter(function ($t) use ($lowercaseWord) {
            return StringHelper::normalize($t->translation, false) !== $lowercaseWord;
        });
        if ($translations->count() < 1) {
            // maximum 10 levels of recursion
            if ($n > 10) {
                abort(404);
            }

            return $this->card($request, $n + 1);
        }

        // Compile a list of options
        $translation = $translations->random();
        $options = [$translation->translation];

        // group verbs w/ one another as they tend to be in the infinitive
        // in English.
        $verbSpeechId = Cache::remember('ed.speech.v', 60 * 60 /* seconds */, function () {
            $speech = Speech::where('name', 'verb')->first();

            return $speech ? $speech->id : -1;
        });
        if ($gloss->speech_id !== $verbSpeechId) {
            $verbSpeechId = -1;
        }

        // Random selection optimization:
        // https://stackoverflow.com/questions/1823306/mysql-alternatives-to-order-by-rand
        //
        // This is a terrible hack, but a performant one (at that!)
        $fakeOptions = DB::select('SELECT 
                t.translation
            FROM
                translations as t
            JOIN (SELECT 
                    ti.id
                FROM
                    translations as ti
                JOIN glosses as g on g.id = ti.gloss_id 
                WHERE
                    ti.translation <> :translation AND
                    ti.translation NOT IN(\'?\', \'\', \'[unglossed]\') AND
                    ( :speech0 = -1 OR ( :speech1 > -1 AND :speech2 = g.speech_id) ) AND
                    RAND() < (SELECT 
                            (16 / COUNT(*)) * 10
                        FROM
                            translations
                    )
                ORDER BY RAND()
                LIMIT 16    
            ) AS t0 ON t0.id = t.id
            LIMIT 4', [
            'translation' => $translation->translation,
            'speech0' => $verbSpeechId,
            'speech1' => $verbSpeechId,
            'speech2' => $verbSpeechId,
        ]);

        foreach ($fakeOptions as $option) {
            $options[] = $option->translation;
        }

        shuffle($options);

        return [
            'word' => $gloss->word->word,
            'options' => $options,
            'translation_id' => $translation->id,
        ];
    }

    public function test(Request $request)
    {
        $this->validate($request, [
            'flashcard_id' => 'numeric|exists:flashcards,id',
            'translation_id' => 'numeric|exists:translations,id',
            'translation' => 'string|required',
            'gloss' => 'string',
        ]);

        $translationId = intval($request->input('translation_id'));
        $translation = Translation::findOrFail($translationId);
        $gloss = $translation->gloss;

        $offeredGloss = $request->input('translation');
        $ok = strcmp($translation->translation, $offeredGloss) === 0;

        $account = $request->user();

        if ($account) {
            $result = new FlashcardResult;

            $result->flashcard_id = intval($request->input('flashcard_id'));
            $result->account_id = $account->id;
            $result->gloss_id = $gloss->id;
            $result->expected = $translation->translation;
            $result->actual = $offeredGloss;
            $result->correct = $ok;

            $result->save();

            // parse comments, as it's saved as markdown
            $gloss = $translation->gloss;
            $gloss->load('translations');

            // Record the progress
            $numberOfCards = FlashcardResult::where('account_id', $result->account_id)->count();
            event(new FlashcardFlipped($result, $numberOfCards));
        }

        return [
            'correct' => $ok,
            'gloss' => $this->_bookAdapter->adaptGloss($gloss),
        ];
    }
}
