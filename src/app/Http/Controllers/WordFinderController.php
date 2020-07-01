<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use DB;

use App\Models\{ 
    Gloss,
    GlossGroup,
    Language
};

class WordFinderController extends Controller
{
    public function show(Request $request, int $languageId)
    {
        $language = Language::findOrFail($languageId);
        return view('word-finder.show', [
            'language'   => $language,
            'languageId' => $languageId
        ]);
    }
}
