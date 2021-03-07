<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;

class GamesController extends Controller
{
    public function index() 
    {
        $games = [
            (object) [
                'route'       => route('flashcard'),
                'title'       => __('flashcard.title'),
                'description' => __('flashcard.description')
            ],
            (object) [
                'route'       => route('word-finder.index'),
                'title'       => __('word-finder.title'),
                'description' => __('word-finder.description')
            ]
        ];
        return view('games.index', [ 'games' => $games ]);
    }
}
