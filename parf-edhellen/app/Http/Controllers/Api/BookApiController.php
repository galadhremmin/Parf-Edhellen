<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Keyword;

class BookApiController extends Controller 
{

    public function find(Request $request) {
        $term = $request->input('term');

        if (strpos($term, '*') !== false) {
            $term = str_replace('*', '%', $term);
        } else {
            $term .= '%';
        }

        $words = Keyword::findByTerm($term)
            ->select('Keyword as k', 'NormalizedKeyword as nk')
            ->get();

        return $words;
    }

}