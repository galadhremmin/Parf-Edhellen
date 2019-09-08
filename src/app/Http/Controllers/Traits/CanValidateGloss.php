<?php
namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait CanValidateGloss
{
    public function validateGlossInRequest(Request $request, $id = 0, $review = false)
    {
        $maximumNumberOfTranslations = config('ed.max_number_of_translations');
        
        $rules = [
            'id'                         => 'sometimes|required|numeric|exists:glosses,id',
            'language_id'                => 'required|numeric|exists:languages,id',
            'speech_id'                  => 'required|numeric|exists:speeches,id',
            'word.word'                  => 'required|string|min:1|max:64',
            'sense.word.word'            => 'required|string|min:1|max:64',
            'source'                     => 'required|string|min:3',
            'is_rejected'                => 'required|boolean',
            'is_uncertain'               => 'required|boolean',
            'keywords'                   => 'sometimes|array',
            'keywords.*.word'            => 'sometimes|string|min:1|max:64',
            'tengwar'                    => 'sometimes|string|min:1|max:128',
            'translations.*.translation' => 'required|string|min:1|max:255',
            'translations'               => 'required|array|min:1|max:'.$maximumNumberOfTranslations,
            'details'                    => 'sometimes|array',
            'details.*.category'         => 'required|string',
            'details.*.order'            => 'required|numeric|min:1',
            'details.*.text'             => 'required|string'
        ];

        if (! $review) {
            $rules['account_id']     = 'required|numeric|exists:accounts,id';
            $rules['gloss_group_id'] = 'sometimes|numeric|exists:gloss_groups,id';
        }

        parent::validate($request, $rules);
    } 
}
