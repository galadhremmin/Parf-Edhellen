<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use DB;

use App\Http\Controllers\Controller;
use App\Helpers\StringHelper;
use App\Models\{ 
    Gloss,
    GlossGroup
};

class WordFinderApiController extends Controller
{
    public function index(Request $request, int $languageId)
    {
        $groupIds = GlossGroup::safe()
            ->select('id')
            ->get()
            ->pluck('id')
            ->toArray();

        $glossary = [];
        $glosses = [];
        $words = [];

        for ($i = 0; $i < 8; $i += 1) {
            $gloss = Gloss::active()
                ->join('words', 'words.id', 'glosses.word_id')
                ->join('translations', 'translations.gloss_id', 'glosses.id')
                ->where('language_id', $languageId)
                ->whereIn('gloss_group_id', $groupIds)
                ->whereNotIn('translation', $glosses)
                ->whereNotIn('word', $words)
                ->where(DB::raw('LENGTH(normalized_word)'), '>=', 4)
                ->where('translation', '<>', DB::raw('word'))
                ->inRandomOrder()
                ->select('translation as gloss', 'word', 'glosses.id')
                ->first();

            if ($gloss === null) {
                break;
            }

            $glosses[] = $gloss->gloss;
            $words[] = $gloss->word;
            $glossary[] = $gloss;
        }

        return [
            'glossary' => $glossary
        ];
    }
}
