<?php

namespace App\Http\Controllers\Traits;

use App\Helpers\StringHelper;
use App\Models\Gloss;
use App\Models\GlossDetail;
use App\Models\Translation;
use Illuminate\Http\Request;

trait CanMapGloss
{
    /**
     * @return array{
     *     word: string,
     *     sense: string,
     *     keywords: string[],
     *     translations: Translation[],
     *     details: GlossDetail[]
     * }
     */
    public function mapGloss(Gloss $gloss, Request $request): array
    {
        $word = $request->input('word.word');
        $sense = $request->input('sense.word.word');

        $gloss->account_id = intval($request->input('account.id') ?: $request->input('account_id'));
        $gloss->language_id = intval($request->input('language_id'));
        $gloss->speech_id = intval($request->input('speech_id'));

        $gloss->is_rejected = boolval($request->input('is_rejected'));
        $gloss->is_uncertain = boolval($request->input('is_uncertain'));

        $gloss->source = $request->input('source');
        $gloss->comments = $request->input('comments');

        $gloss->gloss_group_id = $request->has('gloss_group_id')
            ? intval($request->input('gloss_group_id'))
            : null;

        $gloss->label = $request->has('label')
            ? $request->input('label')
            : null;

        $gloss->tengwar = $request->has('tengwar')
            ? $request->input('tengwar')
            : null;

        $keywords = array_map(function ($k) {
            return StringHelper::toLower($k['word']);
        }, $request->input('keywords'));

        $translations = array_map(function ($t) {
            return new Translation([
                'translation' => StringHelper::toLower($t['translation']),
            ]);
        }, $request->input('translations'));

        $details = $request->has('gloss_details')
            ? array_map(function ($d) {
                return new GlossDetail([
                    'category' => trim($d['category']),
                    'text' => trim($d['text']),
                    'order' => intval($d['order']),
                ]);
            }, $request->input('gloss_details'))
            : [];

        return [
            'word' => $word,
            'sense' => $sense,
            'keywords' => $keywords,
            'translations' => $translations,
            'details' => $details,
        ];
    }
}
