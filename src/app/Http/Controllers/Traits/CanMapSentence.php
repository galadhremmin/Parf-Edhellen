<?php

namespace App\Http\Controllers\Traits;

use App\Models\LexicalEntryInflection;
use App\Models\Sentence;
use App\Models\SentenceFragment;
use App\Models\SentenceTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait CanMapSentence
{
    public function mapSentence(Sentence $sentence, Request $request)
    {
        $sentence->name = $request->input('name');
        $sentence->source = $request->input('source');
        $sentence->description = $request->input('description');
        $sentence->account_id = intval($request->input('account.id'));
        $sentence->long_description = $request->input('long_description') ?? null;
        $sentence->language_id = intval($request->input('language_id'));
        $sentence->is_neologism = intval($request->input('is_neologism'));
        $sentence->is_approved = 1; // always approved by default

        $fragmentsMap = $this->mapSentenceFragments($sentence, $request);

        return array_merge([
            'sentence' => $sentence,
        ], $fragmentsMap);
    }

    public function mapSentenceFragments(Sentence $sentence, Request $request)
    {
        $fragments = [];
        $inflections = [];
        $translations = [];

        foreach ($request->input('fragments') as $fragmentData) {
            $fragment = new SentenceFragment;

            $fragment->type = intval($fragmentData['type']);
            $fragment->fragment = $fragmentData['fragment'];

            if (isset($fragmentData['tengwar'])) {
                $fragment->tengwar = $fragmentData['tengwar'];
            }

            if (! $fragment->type) {
                $fragment->comments = $fragmentData['comments'] ?? ''; // cannot be NULL
                $fragment->speech_id = intval($fragmentData['speech_id']);
                $fragment->lexical_entry_id = intval($fragmentData['lexical_entry_id']);
            } else {
                $fragment->comments = '';

                // Certain types of fragments does not have a textual body, and should therefore be an empty string.
                if (empty($fragment->fragment)) {
                    $fragment->fragment = '';
                }
            }

            $fragment->paragraph_number = intval($fragmentData['paragraph_number']);
            $fragment->sentence_number = intval($fragmentData['sentence_number']);
            $fragment->order = count($fragments) * 10;
            $fragment->sentence_id = $sentence->id;

            $fragments[] = $fragment;

            $inflectionsForFragment = [];
            if (! $fragment->type && isset($fragmentData['lexical_entry_inflections'])) {
                $order = 0;
                foreach ($fragmentData['lexical_entry_inflections'] as $inflection) {
                    $inflectionRel = new LexicalEntryInflection([
                        'inflection_id' => intval($inflection['inflection_id']),
                        'order' => $order++,
                        'speech_id' => $fragment->speech_id,
                        'language_id' => $sentence->language_id,
                        'lexical_entry_id' => $fragment->lexical_entry_id,
                        'account_id' => $sentence->account_id,
                    ]);

                    $inflectionsForFragment[] = $inflectionRel;
                }
            }

            $inflections[] = $inflectionsForFragment;
        }

        if ($request->has('translations')) {
            foreach ($request->input('translations') as $translation) {
                $translations[] = new SentenceTranslation([
                    'paragraph_number' => intval($translation['paragraph_number']),
                    'sentence_number' => intval($translation['sentence_number']),
                    'translation' => $translation['translation'],
                ]);
            }
        }

        return [
            'fragments' => new Collection($fragments),
            'inflections' => new Collection($inflections),
            'translations' => new Collection($translations),
        ];
    }
}
