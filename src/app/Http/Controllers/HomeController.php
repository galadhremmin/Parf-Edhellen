<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ Sentence, AuditTrail };
use App\Adapters\SentenceAdapter;
use App\Repositories\ContributionRepository;
use App\Repositories\Interfaces\IAuditTrailRepository;

class HomeController extends Controller
{
    protected $_auditTrail;
    protected $_sentenceAdapter;
    protected $_reviewRepository;

    public function __construct(IAuditTrailRepository $auditTrail, SentenceAdapter $sentenceAdapter, 
        ContributionRepository $ContributionRepository) 
    {
        $this->_auditTrail       = $auditTrail;
        $this->_sentenceAdapter  = $sentenceAdapter;
        $this->_reviewRepository = $ContributionRepository;
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
