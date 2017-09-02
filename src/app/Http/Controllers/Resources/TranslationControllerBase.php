<?php

namespace App\Http\Controllers\Resources;

use App\Models\{ Translation, Keyword, Word, Language };
use App\Adapters\BookAdapter;
use App\Repositories\TranslationRepository;
use App\Helpers\StringHelper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslationControllerBase extends Controller
{
    protected $_bookAdapter;
    protected $_translationRepository;

    public function __construct(BookAdapter $adapter, TranslationRepository $translationRepository) 
    {
        $this->_bookAdapter = $adapter;
        $this->_translationRepository = $translationRepository;
    }

    protected function mapTranslation(Translation $translation, Request $request)
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

    protected function validateRequest(Request $request, $id = 0, $review = false)
    {
        $rules = [
            'language_id'     => 'required|numeric|exists:languages,id',
            'speech_id'       => 'required|numeric|exists:speeches,id',
            'word'            => 'required|string|min:1|max:64',
            'sense.word.word' => 'required|string|min:1|max:64',
            'translation'     => 'required|string|min:1|max:255',
            'source'          => 'required|string|min:3',
            'is_rejected'     => 'required|boolean',
            'is_uncertain'    => 'required|boolean',
            'keywords'        => 'sometimes|array',
            'keywords.*.word' => 'sometimes|string|min:1|max:64',
            'tengwar'         => 'sometimes|string|min:1|max:128'
        ];

        if ($review) {
            $rules['id']                   = 'sometimes|required|numeric|exists:translation_reviews,id';
        } else {
            $rules['account_id']           = 'required|numeric|exists:accounts,id';
            $rules['id']                   = 'sometimes|required|numeric|exists:translations,id';
            $rules['translation_group_id'] = 'sometimes|numeric|exists:translation_groups,id';
        }

        $this->validate($request, $rules, [
            'exists'  => ':attribute is required.',
            'min' => ':attribute is too short.',
            'max' => ':attribute is too long.',
            'required' => ':attribute is required.',
            'sense.word.word.required' => 'sense is required.'
        ]);
    } 
}
