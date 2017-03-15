<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Translation;

class BookController extends Controller
{
    public function pageForWord($word)
    {
        return view('book.page');
    }

    public function pageForTranslationId($id)
    {
        $id = intval($id);
        $translation = Translation::find($id);

        return view('book.page', [ 
            'sections' => [ 
                [ 'language' => $translation->language, 'glosses' => [ $translation ] ] 
            ] 
        ]);
    }
}
