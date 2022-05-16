<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use DB;
use Cache;

use App\Http\Controllers\Abstracts\Controller;
use App\Helpers\StringHelper;
use App\Models\{ 
    GameWordFinderGlossGroup,
    Gloss,
    GlossGroup
};

class WordFinderApiController extends Controller
{
    public function play(Request $request, int $languageId)
    {
        $groupIds = $this->getGlossGroupIds();

        $glossary = [];
        $glosses = [];
        $words = [];
        $ids = [];

        for ($i = 0; $i < 8; $i += 1) {
            $gloss = Gloss::active()
                ->join('words', 'words.id', 'glosses.word_id')
                ->join('translations', 'translations.gloss_id', 'glosses.id')
                ->where('language_id', $languageId)
                ->whereIn('gloss_group_id', $groupIds)
                ->whereNotIn('translation', $glosses)
                ->whereNotIn('word', $words)
                ->whereNotIn('glosses.id', $ids)
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
            $ids[] = $gloss->id;
        }

        return [
            'glossary' => $glossary
        ];
    }

    private function getGlossGroupIds(): array
    {
        $glossGroupIds = Cache::remember('ed.game.word-finder.gloss-groups', 60 * 60 * 24 /* seconds */, function () {
            return GameWordFinderGlossGroup::pluck('gloss_group_id')->toArray();
        });

        if (! is_array($glossGroupIds) || count($glossGroupIds) < 1) {
            $glossGroupIds = GlossGroup::safe()
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
        }

        return $glossGroupIds;
    }
}
