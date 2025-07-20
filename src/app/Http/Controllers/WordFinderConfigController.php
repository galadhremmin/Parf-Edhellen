<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\GameWordFinderGlossGroup;
use App\Models\LexicalEntryGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WordFinderConfigController extends Controller
{
    public function index()
    {
        $groups = LexicalEntryGroup::all();
        $selectedGroupIds = GameWordFinderGlossGroup::all()->groupBy('lexical_entry_group_id');

        return view('admin.word-finder.index', [
            'all_lexical_entry_groups' => $groups,
            'selected_lexical_entry_group_ids' => $selectedGroupIds,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lexical_entry_group_ids' => 'array|required',
            'lexical_entry_group_ids.*' => 'numeric|exists:lexical_entry_groups,id',
        ]);

        DB::table('game_word_finder_gloss_groups')->delete();
        foreach ($data['lexical_entry_group_ids'] as $lexicalEntryGroupId) {
            GameWordFinderGlossGroup::create([
                'lexical_entry_group_id' => $lexicalEntryGroupId,
            ]);
        }

        // Remove any cache, if present.
        Cache::forget('ed.game.word-finder.lexical-entry-groups');

        return redirect(route('word-finder.config.index'));
    }
}
