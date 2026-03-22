<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\GameCrosswordLexicalEntryGroup;
use App\Models\GameCrosswordRephraseSpeech;
use App\Models\LexicalEntryGroup;
use App\Models\Speech;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CrosswordConfigController extends Controller
{
    public function index()
    {
        $groups = LexicalEntryGroup::all();
        $selectedGroupIds = GameCrosswordLexicalEntryGroup::all()->groupBy('lexical_entry_group_id');

        $speeches = Speech::orderBy('order')->get();
        $selectedSpeechIds = GameCrosswordRephraseSpeech::pluck('speech_id')->flip();

        return view('admin.crossword.index', [
            'all_lexical_entry_groups'    => $groups,
            'selected_lexical_entry_group_ids' => $selectedGroupIds,
            'all_speeches'                => $speeches,
            'selected_rephrase_speech_ids' => $selectedSpeechIds,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lexical_entry_group_ids'   => 'array|required',
            'lexical_entry_group_ids.*' => 'numeric|exists:lexical_entry_groups,id',
            'rephrase_speech_ids'       => 'array|nullable',
            'rephrase_speech_ids.*'     => 'numeric|exists:speeches,id',
        ]);

        DB::table('game_crossword_lexical_entry_groups')->delete();
        foreach ($data['lexical_entry_group_ids'] as $lexicalEntryGroupId) {
            GameCrosswordLexicalEntryGroup::create([
                'lexical_entry_group_id' => $lexicalEntryGroupId,
            ]);
        }

        GameCrosswordRephraseSpeech::query()->delete();
        foreach ($data['rephrase_speech_ids'] ?? [] as $speechId) {
            GameCrosswordRephraseSpeech::create(['speech_id' => $speechId]);
        }

        Cache::forget('ed.game.crossword.lexical-entry-groups');

        return redirect(route('crossword.config.index'));
    }
}
