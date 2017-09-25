<?php
namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use App\Models\Translation;
use App\Helpers\StringHelper;

trait CanMapTranslation
{
    public function mapTranslation(Translation $translation, Request $request)
    {
        $word  = $request->input('word');
        $sense = $request->input('sense.word.word');

        $translation->account_id   = intval($request->input('account_id'));
        $translation->language_id  = intval($request->input('language_id'));
        $translation->speech_id    = intval($request->input('speech_id'));

        $translation->is_rejected  = boolval($request->input('is_rejected'));
        $translation->is_uncertain = boolval($request->input('is_uncertain'));
        $translation->is_latest    = 1;
            
        $translation->translation  = $request->input('translation');
        $translation->source       = $request->input('source');
        $translation->comments     = $request->input('comments');

        $translation->translation_group_id = $request->has('translation_group_id') 
            ? intval($request->input('translation_group_id'))
            : null;

        $translation->tengwar  = $request->has('tengwar')
            ? $request->input('tengwar')
            : null;

        $keywords = array_map(function ($k) {
            return StringHelper::toLower($k['word']);
        }, $request->input('keywords'));

        return [
            'word'     => $word,
            'sense'    => $sense,
            'keywords' => $keywords
        ];
    }
}
