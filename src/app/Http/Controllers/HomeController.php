<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ Sentence, AuditTrail };
use App\Repositories\TranslationReviewRepository;

class HomeController extends Controller
{
    private $_reviewRepository;

    public function __construct(TranslationReviewRepository $translationReviewRepository) 
    {
        $this->_reviewRepository = $translationReviewRepository;
    }

    public function index() 
    {
        $sentence    = Sentence::approved()->inRandomOrder()->first();
        $auditTrails = AuditTrail::orderBy('id', 'desc')
            ->with([
                'account' => function ($query) {
                    $query->select('id', 'nickname');
                }
            ])
            ->take(10)
            ->get();

        $data = [
            'sentence'    => $sentence,
            'auditTrails' => $auditTrails
        ];
        
        return view('home.index', $data);
    }
}
