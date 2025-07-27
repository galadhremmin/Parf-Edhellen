<?php

namespace Tests\Unit\Traits;

use App\Models\LexicalEntry;
use App\Models\LexicalEntryDetail;
use App\Models\LexicalEntryGroup;
use App\Models\Language;
use App\Models\Speech;
use App\Models\Gloss;
use App\Repositories\LexicalEntryRepository;
use Illuminate\Support\Facades\Auth;

trait CanCreateGloss
{
    use MocksAuth {
        MocksAuth::setUp as setUpAuth;
    } // ;

    protected ?LexicalEntryGroup $_lexicalEntryGroup;

    protected LexicalEntryRepository $_lexicalEntryRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuth();

        $this->_lexicalEntryRepository = resolve(LexicalEntryRepository::class);
        $this->_lexicalEntryGroup = LexicalEntryGroup::firstOrCreate([
            'name' => 'Unit tests',
            'is_canon' => true,
        ]);
    }

    protected function cleanLexicalEntries()
    {
        $lexicalEntries = LexicalEntry::where('lexical_entry_group_id', $this->_lexicalEntryGroup->id)
            ->get();
        $senses = [];

        foreach ($lexicalEntries as $lexicalEntry) {
            $senses[] = $lexicalEntry->sense;

            $lexicalEntry->keywords()->delete();
            $lexicalEntry->glosses()->delete();
            $lexicalEntry->lexical_entry_details()->delete();
            $lexicalEntry->lexical_entry_versions()->delete();
            $lexicalEntry->delete();
        }
    }

    /**
     * Creates a gloss.
     *
     * @param  string  $sense
     * @return array
     */
    protected function createLexicalEntry(string $method = __FUNCTION__, string $word = 'test-word')
    {
        $accountId = Auth::user()->id;
        $languageId = Language::where('name', 'Sindarin')
            ->firstOrFail()->id;
        $speechId = Speech::where('name', 'verb')
            ->firstOrFail()->id;

        $lexicalEntry = new LexicalEntry;
        $lexicalEntry->account_id = $accountId;
        $lexicalEntry->language_id = $languageId;
        $lexicalEntry->lexical_entry_group_id = $this->_lexicalEntryGroup->id;
        $lexicalEntry->is_uncertain = 1;
        $lexicalEntry->source = 'Unit test';
        $lexicalEntry->comments = 'This lexical entry was created in a unit test.';
        $lexicalEntry->tengwar = 'yljjh6';
        $lexicalEntry->speech_id = $speechId;
        $lexicalEntry->external_id = 'UA-Unit-LexicalEntryRepository-'.$method.'-'.uniqid();
        $lexicalEntry->label = null;

        $glosses = $this->createGlosses();

        $keywords = [
            'test 3',
            'test 4',
            'test 5',
        ];

        $details = $this->createLexicalEntryDetails($lexicalEntry);

        $sense = $method.'-'.$word;

        return [
            'word' => $word,
            'sense' => $sense,
            'lexicalEntry' => $lexicalEntry,
            'glosses' => $glosses,
            'keywords' => $keywords,
            'details' => $details,
        ];
    }

    protected function createGlosses()
    {
        return [
            new Gloss(['translation' => 'test 0']),
            new Gloss(['translation' => 'test 1']),
            new Gloss(['translation' => 'test 2']),
        ];
    }

    protected function createLexicalEntryDetails(LexicalEntry $lexicalEntry)
    {
        return [
            new LexicalEntryDetail([
                'category' => 'Section 1',
                'text' => 'This is the first item for '.$lexicalEntry->external_id,
                'order' => 10,
            ]),
            new LexicalEntryDetail([
                'category' => 'Section 2',
                'text' => 'This is the second item for '.$lexicalEntry->external_id,
                'order' => 20,
            ]),
            new LexicalEntryDetail([
                'category' => 'Section 3',
                'text' => 'This is the third item for '.$lexicalEntry->external_id,
                'order' => 30,
            ]),
            new LexicalEntryDetail([
                'category' => 'Section 4',
                'text' => 'This is the fourth item for '.$lexicalEntry->external_id,
                'order' => 40,
            ]),
            new LexicalEntryDetail([
                'category' => 'Section 5',
                'text' => 'This is the fifth item for '.$lexicalEntry->external_id,
                'order' => 50,
            ]),
        ];
    }

    protected function getRepository()
    {
        return $this->_lexicalEntryRepository;
    }
}
