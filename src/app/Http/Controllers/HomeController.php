<?php

namespace App\Http\Controllers;

use App\Adapters\AuditTrailAdapter;
use App\Adapters\BookAdapter;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\AuditTrail;
use App\Models\GameWordFinderLanguage;
use App\Models\LexicalEntry;
use App\Models\Sentence;
use App\Repositories\ContributionRepository;
use App\Repositories\CrosswordRepository;
use App\Repositories\Interfaces\IAuditTrailRepository;
use App\Repositories\SentenceRepository;
use App\Repositories\StatisticsRepository;
use App\Repositories\TrendingRepository;
use DateInterval;
use NumberFormatter;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    protected IAuditTrailRepository $_auditTrail;

    protected AuditTrailAdapter $_auditTrailAdapter;

    protected SentenceRepository $_sentenceRepository;

    protected BookAdapter $_bookAdapter;

    protected ContributionRepository $_reviewRepository;

    protected StatisticsRepository $_statisticsRepository;

    protected TrendingRepository $_trendingRepository;

    protected CrosswordRepository $_crosswordRepository;

    protected LinkHelper $_linkHelper;

    public function __construct(IAuditTrailRepository $auditTrail, AuditTrailAdapter $auditTrailAdapter, StatisticsRepository $statisticsRepository,
        BookAdapter $bookAdapter, SentenceRepository $sentenceRepository, ContributionRepository $contributionRepository,
        TrendingRepository $trendingRepository, CrosswordRepository $crosswordRepository, LinkHelper $linkHelper)
    {
        $this->_auditTrail = $auditTrail;
        $this->_auditTrailAdapter = $auditTrailAdapter;
        $this->_bookAdapter = $bookAdapter;
        $this->_sentenceRepository = $sentenceRepository;
        $this->_reviewRepository = $contributionRepository;
        $this->_statisticsRepository = $statisticsRepository;
        $this->_trendingRepository = $trendingRepository;
        $this->_crosswordRepository = $crosswordRepository;
        $this->_linkHelper = $linkHelper;
    }

    public function index()
    {
        // Retrieve a random sentence to be featured.
        $randomSentence = Cache::remember('ed.home.sentence', DateInterval::createFromDateString('1 day'), function () {
            $sentence = Sentence::approved()->inRandomOrder()
                ->select('id')
                ->first();

            return [
                'sentence' => $sentence === null //
                    ? null //
                    : $this->_sentenceRepository->getSentence($sentence->id),
            ];
        });

        // Retrieve a random lexical entry to feature
        $randomGloss = Cache::remember('ed.home.gloss', DateInterval::createFromDateString('1 day'), function () {
            $lexicalEntry = LexicalEntry::active()
                ->inRandomOrder()
                ->notUncertain()
                ->first();

            return [
                'lexicalEntry' => $lexicalEntry === null //
                    ? null //
                    : $this->_bookAdapter->adaptLexicalEntry($lexicalEntry),
            ];
        });

        $statistics = Cache::remember('ed.home.statistics', DateInterval::createFromDateString('1 hour'), function () {
            return $this->_statisticsRepository->getGlobalStatistics();
        });

        // Retrieve the 15 latest audit trail
        $auditTrails = Cache::remember(IAuditTrailRepository::HOME_CACHE_KEY, DateInterval::createFromDateString('5 minutes'), function () {
            return $this->_auditTrailAdapter->adaptAndMerge(
                $this->_auditTrail->get(15, 0, [
                    AuditTrail::ACTION_COMMENT_ADD,
                    AuditTrail::ACTION_COMMENT_LIKE,
                    AuditTrail::ACTION_GLOSS_ADD,
                    AuditTrail::ACTION_GLOSS_EDIT,
                    AuditTrail::ACTION_SENTENCE_ADD,
                    AuditTrail::ACTION_SENTENCE_EDIT,
                ], true /* = publicOnly */),
                0,
                null,
                true /* = publicOnly */
            );
        });

        $trendingSearches = Cache::remember('ed.home.trending', DateInterval::createFromDateString('1 hour'), function () {
            $items = $this->_trendingRepository->getMostSearchedTerms(7, 10);

            return array_map(fn (array $item) => $item + [
                'url' => $this->_linkHelper->dictionaryWord(
                    $item['search_term'],
                    $item['language_id'],
                    $item['speech_ids']
                ),
            ], $items);
        });

        // The weekly crossword is the page's reason to come back, but the
        // generator can fall behind, so this is allowed to be empty and the view
        // hides the section rather than linking to a puzzle that isn't there.
        $crosswords = Cache::remember('ed.home.crosswords', DateInterval::createFromDateString('1 hour'), function () {
            return $this->_crosswordRepository->getCurrentPuzzles();
        });

        $wordFinderLanguages = Cache::remember('ed.home.word-finder', DateInterval::createFromDateString('1 day'), function () {
            return GameWordFinderLanguage::orderBy('title')->get();
        });

        // A trailing average, not a count for today, which would read as near zero every morning.
        $searchesPerDay = Cache::remember('ed.home.searches-per-day', DateInterval::createFromDateString('1 hour'), function () {
            return $this->_trendingRepository->getAverageSearchesPerDay(7);
        });

        $data = $randomSentence + $randomGloss + $statistics + [
            'auditTrails' => $auditTrails,
            'trendingSearches' => $trendingSearches,
            'crosswords' => $crosswords,
            'wordFinderLanguages' => $wordFinderLanguages,
            'searchesPerDay' => $searchesPerDay,
            'periodSinceInception' => ucfirst((new NumberFormatter('en', NumberFormatter::SPELLOUT))->format(date('Y') - 2011))
        ];

        return view('home.index', $data);
    }
}
