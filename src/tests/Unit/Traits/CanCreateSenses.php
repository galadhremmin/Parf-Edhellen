<?php

namespace Tests\Unit\Traits;

use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\Speech;
use App\Repositories\WordRepository;
use Illuminate\Support\Facades\Auth;

/**
 * Saves a lexical entry with a sense of its own. Requires CanCreateGloss, whose template it fills in.
 */
trait CanCreateSenses
{
    protected function createEntry(string $sense, string $speech = 'noun'): LexicalEntry
    {
        $template = $this->createLexicalEntry(__FUNCTION__, 'grelkword'.uniqid());
        $lexicalEntry = $template['lexicalEntry'];
        $words = resolve(WordRepository::class);
        $senseWord = $words->save($sense, Auth::user()->id);

        $lexicalEntry->word_id = $words->save($template['word'], Auth::user()->id)->id;
        $lexicalEntry->sense_id = Sense::firstOrCreate(['id' => $senseWord->id], ['description' => $sense])->id;
        $lexicalEntry->speech_id = Speech::where('name', $speech)->firstOrFail()->id;
        $lexicalEntry->save();
        // search only returns entries with glosses
        $lexicalEntry->glosses()->saveMany($template['glosses']);

        return $lexicalEntry;
    }
}
