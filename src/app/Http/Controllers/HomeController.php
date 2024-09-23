<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

use App\Http\Controllers\Abstracts\Controller;
use App\Repositories\{
    ContributionRepository,
    SentenceRepository,
    StatisticsRepository
};
use App\Repositories\Interfaces\IAuditTrailRepository;
use App\Models\{
    AuditTrail,
    Gloss,
    Sentence
};
use App\Adapters\{
    AuditTrailAdapter,
    BookAdapter
};

class HomeController extends Controller
{
    protected $_auditTrail;
    protected $_auditTrailAdapter;
    protected $_sentenceRepository;
    protected $_bookAdapter;
    protected $_reviewRepository;
    protected $_statisticsRepository;

    public function __construct(IAuditTrailRepository $auditTrail, AuditTrailAdapter $auditTrailAdapter, StatisticsRepository $statisticsRepository,
        BookAdapter $bookAdapter, SentenceRepository $sentenceRepository, ContributionRepository $contributionRepository) 
    {
        $this->_auditTrail           = $auditTrail;
        $this->_auditTrailAdapter    = $auditTrailAdapter;
        $this->_bookAdapter          = $bookAdapter;
        $this->_sentenceRepository   = $sentenceRepository;
        $this->_reviewRepository     = $contributionRepository;
        $this->_statisticsRepository = $statisticsRepository;
    }

    public function index() 
    {
        // Retrieve a random jumbotron background image from configuration. The background is 
        // positioned upon the jumbotron.
        $jumbotronFiles = config('ed.jumbotron_files');
        $noOfJumbotronFiles = count($jumbotronFiles);
        $background = $noOfJumbotronFiles > 0 //
            ? $jumbotronFiles[mt_rand(0, $noOfJumbotronFiles - 1)] //
            : null;

        // Retrieve a random sentence to be featured.
        $randomSentence = Cache::remember('ed.home.sentence', 60 * 24 /* seconds */, function () {
            $sentence = Sentence::approved()->inRandomOrder()
                ->select('id')
                ->first();
            return [
                'sentence' => $sentence === null // 
                    ? null //
                    : $this->_sentenceRepository->getSentence($sentence->id)
            ];
        });

        // Retrieve a random gloss to feature
        $randomGloss = Cache::remember('ed.home.gloss', 60 * 60 /* seconds */, function () {
            $gloss = Gloss::active()
                ->inRandomOrder()
                ->notUncertain()
                ->first();
            
            return [
                'gloss' => $gloss === null //
                    ? null //
                    : $this->_bookAdapter->adaptGloss($gloss)
            ];
        });

        $statistics = Cache::remember('ed.home.statistics', 60 * 60 /* seconds */, function () {
            return $this->_statisticsRepository->getGlobalStatistics();
        });

        // Retrieve the 10 latest audit trail
        $auditTrails = Cache::remember('ed.home.audit', 60 * 5 /* seconds */, function() {
            return $this->_auditTrailAdapter->adaptAndMerge(
                $this->_auditTrail->get(15, 0, [
                    AuditTrail::ACTION_COMMENT_ADD,
                    AuditTrail::ACTION_COMMENT_LIKE,
                    AuditTrail::ACTION_GLOSS_ADD,
                    AuditTrail::ACTION_GLOSS_EDIT,
                    AuditTrail::ACTION_SENTENCE_ADD,
                    AuditTrail::ACTION_SENTENCE_EDIT
                ])
            );
        });

        $data = $randomSentence + $randomGloss + $statistics + [
            'auditTrails'      => $auditTrails,
            'background'       => $background
        ];
        
        return view('home.index', $data);
    }
}
