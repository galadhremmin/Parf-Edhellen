<?php
namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait CanValidateTranslation
{
    public function validateTranslationInRequest(Request $request, $id = 0, $review = false)
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
            $rules['id']                   = 'sometimes|required|numeric|exists:contributions,id';
        } else {
            $rules['account_id']           = 'required|numeric|exists:accounts,id';
            $rules['id']                   = 'sometimes|required|numeric|exists:translations,id';
            $rules['translation_group_id'] = 'sometimes|numeric|exists:translation_groups,id';
        }

        parent::validate($request, $rules, [
            'sense.word.word.required' => 'sense is required.'
        ]);
    } 
}
