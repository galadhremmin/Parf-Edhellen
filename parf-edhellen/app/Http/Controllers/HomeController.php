<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sentence;
use App\Repositories\TranslationReviewRepository;

class HomeController extends Controller
{
    private $_reviewRepository;

    public function __construct(TranslationReviewRepository $translationReviewRepository)
    {
        $this->_reviewRepository = $translationReviewRepository;
    }

    public function index() {
        $sentence = Sentence::inRandomOrder()->first();
        $reviews  = $this->_reviewRepository->getRecentlyApproved(); 
        $data = [
            'sentence' => $sentence,
            'reviews'  => $reviews
        ];
        
        return view('home.index', $data);
    }
}
