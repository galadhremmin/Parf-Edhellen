<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\GameWordFinderGlossGroup;
use App\Models\GlossGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WordFinderConfigController extends Controller
{
    public function index()
    {
        $groups = GlossGroup::all();
        $selectedGroupIds = GameWordFinderGlossGroup::all()->groupBy('gloss_group_id');

        return view('admin.word-finder.index', [
            'all_gloss_groups' => $groups,
            'selected_gloss_group_ids' => $selectedGroupIds,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'gloss_group_ids' => 'array|required',
            'gloss_group_ids.*' => 'numeric|exists:gloss_groups,id',
        ]);

        DB::table('game_word_finder_gloss_groups')->delete();
        foreach ($data['gloss_group_ids'] as $glossGroupId) {
            GameWordFinderGlossGroup::create([
                'gloss_group_id' => $glossGroupId,
            ]);
        }

        // Remove any cache, if present.
        Cache::forget('ed.game.word-finder.gloss-groups');

        return redirect(route('word-finder.config.index'));
    }
}
