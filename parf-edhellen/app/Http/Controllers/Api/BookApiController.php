<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Keyword;

class BookApiController extends Controller 
{

    public function find(Request $request) {
        $word = $request->input('word');
        $reversed = $request->input('reversed') === true;

        if (strpos($word, '*') !== false) {
            $word = str_replace('*', '%', $word);
        } else {
            $word .= '%';
        }

        $keywords = Keyword::findByWord($word, $reversed)
            ->select('Keyword as k', 'NormalizedKeyword as nk')
            ->get();

        return $keywords;
    }

}