<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;

class PracticeController extends Controller
{
    public function index()
    {
        $activities = [
            (object) [
                'route' => route('flashcard'),
                'title' => __('flashcard.title'),
                'description' => __('flashcard.description'),
            ],
            (object) [
                'route' => route('word-finder.index'),
                'title' => __('word-finder.title'),
                'description' => __('word-finder.description'),
            ],
            (object) [
                'route' => route('crossword.index'),
                'title' => __('crossword.title'),
                'description' => __('crossword.description'),
            ],
        ];

        return view('practice.index', ['activities' => $activities]);
    }
}
