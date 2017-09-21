<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ Sentence, AuditTrail };
use App\Adapters\SentenceAdapter;
use App\Repositories\TranslationReviewRepository;
use App\Repositories\Interfaces\IAuditTrailRepository;

class HomeController extends Controller
{
    protected $_auditTrail;
    protected $_sentenceAdapter;
    protected $_reviewRepository;

    public function __construct(IAuditTrailRepository $auditTrail, SentenceAdapter $sentenceAdapter, 
        TranslationReviewRepository $translationReviewRepository) 
    {
        $this->_auditTrail       = $auditTrail;
        $this->_sentenceAdapter  = $sentenceAdapter;
        $this->_reviewRepository = $translationReviewRepository;
    }

    public function index() 
    {
        $randomSentence = Sentence::approved()->inRandomOrder()->first();
        $data = $randomSentence 
            ? $this->_sentenceAdapter->adaptFragments($randomSentence->sentence_fragments, false) 
            : null;

        $auditTrails = $this->_auditTrail->get(10);
        $data = [
            'sentence'         => $randomSentence,
            'sentenceData'     => $data,
            'auditTrails'      => $auditTrails
        ];
        
        return view('home.index', $data);
    }
}
