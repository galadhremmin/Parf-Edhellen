<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ Sentence, AuditTrail };
use App\Repositories\{ AuditTrailRepository, TranslationReviewRepository };

class HomeController extends Controller
{
    protected $_auditTrail;
    protected $_reviewRepository;

    public function __construct(AuditTrailRepository $auditTrail, TranslationReviewRepository $translationReviewRepository) 
    {
        $this->_auditTrail       = $auditTrail;
        $this->_reviewRepository = $translationReviewRepository;
    }

    public function index() 
    {
        $sentence    = Sentence::approved()->inRandomOrder()->first();
        $auditTrails = $this->_auditTrail->get(10);

        $data = [
            'sentence'    => $sentence,
            'auditTrails' => $auditTrails
        ];
        
        return view('home.index', $data);
    }
}
