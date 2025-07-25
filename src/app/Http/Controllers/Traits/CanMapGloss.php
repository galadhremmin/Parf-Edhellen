<?php

namespace App\Http\Controllers\Traits;

use App\Helpers\StringHelper;
use App\Models\Gloss;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryDetail;
use Illuminate\Http\Request;

trait CanMapGloss
{
    /**
     * @return array{
     *     word: string,
     *     sense: string,
     *     keywords: string[],
     *     translations: Gloss[],
     *     details: LexicalEntryDetail[]
     * }
     */
    public function mapLexicalEntry(LexicalEntry $lexicalEntry, Request $request): array
    {
        $word = $request->input('word.word');
        $sense = $request->input('sense.word.word');

        $lexicalEntry->account_id = intval($request->input('account.id') ?: $request->input('account_id'));
        $lexicalEntry->language_id = intval($request->input('language_id'));
        $lexicalEntry->speech_id = intval($request->input('speech_id'));

        $lexicalEntry->is_rejected = boolval($request->input('is_rejected'));
        $lexicalEntry->is_uncertain = boolval($request->input('is_uncertain'));

        $lexicalEntry->source = $request->input('source');
        $lexicalEntry->comments = $request->input('comments');

        $lexicalEntry->lexical_entry_group_id = $request->has('lexical_entry_group_id')
            ? intval($request->input('lexical_entry_group_id'))
            : null;

        $lexicalEntry->label = $request->has('label')
            ? $request->input('label')
            : null;

        $lexicalEntry->tengwar = $request->has('tengwar')
            ? $request->input('tengwar')
            : null;

        $keywords = array_map(function ($k) {
            return StringHelper::toLower($k['word']);
        }, $request->input('keywords'));

        $glosses = array_map(function ($t) {
            return new Gloss([
                'translation' => StringHelper::toLower($t['translation']),
            ]);
        }, $request->input('glosses'));

        $details = $request->has('lexical_entry_details')
            ? array_map(function ($d) {
                return new LexicalEntryDetail([
                    'category' => trim($d['category']),
                    'text' => trim($d['text']),
                    'order' => intval($d['order']),
                ]);
            }, $request->input('lexical_entry_details'))
            : [];

        return [
            'word' => $word,
            'sense' => $sense,
            'keywords' => $keywords,
            'glosses' => $glosses,
            'details' => $details,
        ];
    }
}
