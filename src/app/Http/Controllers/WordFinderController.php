<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use DB;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\{ 
    GameWordFinderLanguage
};

class WordFinderController extends Controller
{
    public function index(Request $request)
    {
        $games = GameWordFinderLanguage::orderBy('title')
            ->get();
        return view('word-finder.index', [ 'games' => $games ]);
    }

    public function show(Request $request, int $gameId)
    {
        $game = GameWordFinderLanguage::findOrFail($gameId);
        return view('word-finder.show', [
            'game' => $game
        ]);
    }
}
