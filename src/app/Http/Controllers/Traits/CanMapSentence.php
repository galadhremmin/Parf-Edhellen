<?php
namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

use App\Models\{ 
    Sentence, 
    SentenceFragment,
    SentenceFragmentInflectionRel
};

trait CanMapSentence
{
    public function mapSentence(Sentence $sentence, Request $request) 
    {
        $sentence->name             = $request->input('name');
        $sentence->source           = $request->input('source');
        $sentence->description      = $request->input('description');
        $sentence->long_description = $request->input('long_description') ?? null;
        $sentence->account_id       = intval($request->input('account_id'));
        $sentence->language_id      = intval($request->input('language_id'));
        $sentence->is_neologism     = intval($request->input('is_neologism'));
        $sentence->is_approved      = 1; // always approved by default

        $order = 0;
        $fragments = [];
        $inflections = [];

        foreach ($request->input('fragments') as $fragmentData) {
            $fragment = new SentenceFragment;

            $fragment->type     = intval($fragmentData['type']);
            $fragment->fragment = $fragmentData['fragment'];

            if (isset($fragmentData['tengwar'])) {
                $fragment->tengwar  = $fragmentData['tengwar'];
            }

            if (! $fragment->type) {
                $fragment->comments  = $fragmentData['comments'] ?? ''; // cannot be NULL
                $fragment->speech_id = intval($fragmentData['speech_id']);
                $fragment->gloss_id  = intval($fragmentData['gloss_id']);
            } else {
                $fragment->comments = '';

                // Certain types of fragments does not have a textual body, and should therefore be an empty string.
                if (empty($fragment->fragment)) {
                    $fragment->fragment = '';
                }
            }

            $fragment->order       = count($fragments) * 10;
            $fragment->sentence_id = $sentence->id;

            $fragments[] = $fragment;

            $inflectionsForFragment = [];
            if (! $fragment->type && isset($fragmentData['inflections'])) {
                foreach ($fragmentData['inflections'] as $inflection) {
                    $inflectionRel = new SentenceFragmentInflectionRel;

                    $inflectionRel->inflection_id        = $inflection['id'];
                    $inflectionRel->sentence_fragment_id = $fragment->id;

                    $inflectionsForFragment[] = $inflectionRel;
                }
            }

            $inflections[] = $inflectionsForFragment;
        }

        return [
            'sentence' => $sentence,
            'fragments' => $fragments,
            'inflections' => $inflections
        ];
    }
}
