<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\GameWordFinderGlossGroup;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    DB,
    Cache
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
            $lexicalEntry = LexicalEntry::active()
                ->join('words', 'words.id', 'lexical_entries.word_id')
                ->join('glosses', 'glosses.lexical_entry_id', 'lexical_entries.id')
                ->where('language_id', $languageId)
                ->whereIn('lexical_entry_group_id', $groupIds)
                ->whereNotIn('translation', $glosses)
                ->whereNotIn('word', $words)
                ->whereNotIn('lexical_entries.id', $ids)
                ->where(DB::raw('LENGTH(normalized_word)'), '>=', 4)
                ->where('translation', '<>', DB::raw('word'))
                ->whereNot('word', 'LIKE', '?%')
                ->inRandomOrder()
                ->select('translation as gloss', 'word', 'glosses.id')
                ->first();

            if ($lexicalEntry === null) {
                break;
            }

            $glosses[] = $lexicalEntry->gloss;
            $words[] = $lexicalEntry->word;
            $glossary[] = $lexicalEntry;
            $ids[] = $lexicalEntry->id;
        }

        return [
            'glossary' => $glossary,
        ];
    }

    private function getGlossGroupIds(): array
    {
        $lexicalEntryGroupIds = Cache::remember('ed.game.word-finder.lexical-entry-groups', 60 * 60 * 24 /* seconds */, function () {
            return GameWordFinderGlossGroup::pluck('lexical_entry_group_id')->toArray();
        });

        if (! is_array($lexicalEntryGroupIds) || count($lexicalEntryGroupIds) < 1) {
            $lexicalEntryGroupIds = LexicalEntryGroup::safe()
                ->pluck('id')
                ->toArray();
        }

        return $lexicalEntryGroupIds;
    }
}
