<?php

namespace Tests\Unit\Traits;

use App\Models\Gloss;
use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\SenseTerm;
use App\Models\Speech;
use App\Models\Word;
use App\Models\WordNetSynset;

/**
 * Builds unsaved senses with the relations classification and candidate lookup read.
 */
trait CanBuildSenses
{
    /**
     * @param  string[]  $speeches  the parts of speech of the entries using the sense, by name
     * @param  bool  $untranslated  whether the entries' words and glosses only repeat the sense, as "#manda" does
     */
    protected function buildSense(string $headword, array $speeches = [], bool $isVerb = false, bool $untranslated = false): Sense
    {
        $sense = new Sense;
        $sense->setRelation('word', new Word(['word' => $headword]));
        $sense->setRelation('terms', collect([new SenseTerm([
            'position' => 0,
            'term' => $headword,
            'lemma' => $headword,
            'term_key' => str_replace([' ', '-'], '', $headword),
            'is_verb' => $isVerb,
        ])]));

        // one entry per part of speech, and one with none at all when no part of speech is given
        $sense->setRelation('lexical_entries', collect($speeches ?: [null])->map(function (?string $speech) use ($headword, $untranslated) {
            $entry = new LexicalEntry([
                'speech_id' => $speech === null ? null : Speech::where('name', $speech)->firstOrFail()->id,
            ]);
            $entry->setRelation('word', new Word(['word' => $untranslated ? $headword : 'elvishword']));
            $entry->setRelation('glosses', collect([new Gloss(['translation' => $untranslated ? $headword : 'a translation'])]));

            return $entry;
        }));

        return $sense;
    }

    protected function requireWordNet(): void
    {
        if (! WordNetSynset::exists()) {
            $this->markTestSkipped('WordNet is not imported: run `php artisan ed-import:wordnet`.');
        }
    }
}
