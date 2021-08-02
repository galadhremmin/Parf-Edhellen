<?php

namespace App\Http\Controllers;

use Cache;

use App\Http\Controllers\Abstracts\Controller;
use Illuminate\Http\Request;

use App\Models\{
    GameWordFinderGlossGroup,
    GlossGroup
};

class WordFinderConfigController extends Controller
{
    public function index()
    {
        $groups = GlossGroup::all();
        $selectedGroupIds = GameWordFinderGlossGroup::all()->groupBy('gloss_group_id');

        return view('word-finder.config.index', [
            'all_gloss_groups' => $groups,
            'selected_gloss_group_ids' => $selectedGroupIds
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'gloss_group_ids'   => 'array|required',
            'gloss_group_ids.*' => 'numeric|exists:gloss_groups,id'
        ]);

        GameWordFinderGlossGroup::truncate();
        foreach ($data['gloss_group_ids'] as $glossGroupId) {
            GameWordFinderGlossGroup::create([
                'gloss_group_id' => $glossGroupId
            ]);
        }

        // Remove any cache, if present.
        Cache::forget('ed.game.word-finder.gloss-groups');

        return redirect(route('word-finder.config.index'));
    }
}
