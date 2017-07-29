<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ Sentence, AuditTrail };
use App\Adapters\SentenceAdapter;
use App\Repositories\{ AuditTrailRepository, TranslationReviewRepository };

class HomeController extends Controller
{
    protected $_auditTrail;
    protected $_sentenceAdapter;
    protected $_reviewRepository;

    public function __construct(AuditTrailRepository $auditTrail, SentenceAdapter $sentenceAdapter, 
        TranslationReviewRepository $translationReviewRepository) 
    {
        $this->_auditTrail       = $auditTrail;
        $this->_sentenceAdapter  = $sentenceAdapter;
        $this->_reviewRepository = $translationReviewRepository;
    }

    public function index() 
    {
        $randomSentence = Sentence::approved()->inRandomOrder()->first();
        $data = $this->_sentenceAdapter->adaptFragments($randomSentence->sentence_fragments, false);
        $auditTrails = $this->_auditTrail->get(10);

        $data = [
            'sentence'         => $randomSentence,
            'sentenceData'     => $data,
            'auditTrails'      => $auditTrails
        ];
        
        return view('home.index', $data);
    }
}
