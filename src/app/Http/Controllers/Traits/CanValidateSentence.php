<?php
namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

use App\Adapters\SentenceBuilder;

trait CanValidateSentence
{
    public function validateSentenceInRequest(Request $request, int $id = 0, bool $review = false)
    {
        $rules = [
            'name'         => 'required|string|min:1|max:128|unique:sentences,name'.($id === 0 ? '' : ','.$id.',id'),
            'description'  => 'required|string|max:255',
            'language_id'  => 'required|numeric|exists:languages,id',
            'source'       => 'required|min:3|max:64',
            'is_neologism' => 'required|boolean'
        ];

        if (! $review) {
            $rules['account_id'] = 'sometimes|numeric|exists:accounts,id';
            $rules['id']         = 'sometimes|required|numeric|exists:sentences,id';
        } else {
            $rules['id']         = 'sometimes|required|numeric|exists:contributions,id';
        }
        
        parent::validate($request, $rules);
    } 

    public function validateFragmentsInRequest(Request $request)
    {
        // This is unfortunately a multi-tiered validation process, as its validation
        // rules are heavily dependant on the request data payload.
        //
        // step 1: Ensure that there is a parameter called _fragments_.
        $rules = [
            'fragments'        => 'required|array',
            'fragments.*.type' => 'required|numeric|min:0|max:255'
        ];
        parent::validate($request, $rules);

        // Step 2: construct a new set of rules dependant on the payload
        $rules = [];
        $fragments = $request->input('fragments');
        $numberOfFragments = count($fragments);
    
        for ($i = 0; $i < $numberOfFragments; $i += 1) {
            $prefix = 'fragments.'.$i.'.';

            // Line breaks are treated in a very restricted manner and therefore requires minimum
            // validation. 
            if ($fragments[$i]['type'] === SentenceBuilder::TYPE_CODE_NEWLINE) {
                continue;
            }

            $rules[$prefix.'fragment'] = 'required|max:48';

            if (! $fragments[$i]['type']) {
                $rules[$prefix.'tengwar']          = 'required|max:128';
                $rules[$prefix.'translation_id']   = 'required|exists:translations,id';
                $rules[$prefix.'speech_id']        = 'required|exists:speeches,id';

                // inflections are optional, but when present, have to be declared as an array
                $rules[$prefix.'inflections']      = 'sometimes|array';
                $rules[$prefix.'inflections.*.id'] = 'sometimes|exists:inflections,id';
            }
        }

        parent::validate($request, $rules);
    }
}
