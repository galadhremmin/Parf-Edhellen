<?php

namespace Tests\Unit\Traits;

use App\Models\Gloss;
use App\Models\GlossDetail;
use App\Models\GlossGroup;
use App\Models\Language;
use App\Models\Speech;
use App\Models\Translation;
use App\Repositories\GlossRepository;
use Auth;

trait CanCreateGloss
{
    use MocksAuth {
        MocksAuth::setUp as setUpAuth;
    } // ;

    protected ?GlossGroup $_glossGroup;

    protected GlossRepository $_glossRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuth();

        $this->_glossRepository = resolve(GlossRepository::class);
        $this->_glossGroup = GlossGroup::firstOrCreate([
            'name' => 'Unit tests',
            'is_canon' => true,
        ]);

        $this->cleanGlosses();
    }

    protected function cleanGlosses()
    {
        $glosses = Gloss::where('gloss_group_id', $this->_glossGroup->id)
            ->get();
        $senses = [];

        foreach ($glosses as $gloss) {
            $senses[] = $gloss->sense;

            $gloss->keywords()->delete();
            $gloss->translations()->delete();
            $gloss->gloss_details()->delete();
            $gloss->gloss_versions()->delete();
            $gloss->delete();
        }
    }

    /**
     * Creates a gloss.
     *
     * @param  string  $sense
     * @return array
     */
    protected function createGloss(string $method = __FUNCTION__, string $word = 'test-word')
    {
        $accountId = Auth::user()->id;
        $languageId = Language::where('name', 'Sindarin')
            ->firstOrFail()->id;
        $speechId = Speech::where('name', 'verb')
            ->firstOrFail()->id;

        $gloss = new Gloss;
        $gloss->account_id = $accountId;
        $gloss->language_id = $languageId;
        $gloss->gloss_group_id = $this->_glossGroup->id;
        $gloss->is_uncertain = 1;
        $gloss->source = 'Unit test';
        $gloss->comments = 'This gloss was created in an unit test.';
        $gloss->tengwar = 'yljjh6';
        $gloss->speech_id = $speechId;
        $gloss->external_id = 'UA-Unit-GlossRepository-'.$method.'-'.uniqid();
        $gloss->label = null;

        $translations = $this->createTranslations();

        $keywords = [
            'test 3',
            'test 4',
            'test 5',
        ];

        $details = $this->createGlossDetails($gloss);

        $sense = $method.'-'.$word;

        return [
            'word' => $word,
            'sense' => $sense,
            'gloss' => $gloss,
            'translations' => $translations,
            'keywords' => $keywords,
            'details' => $details,
        ];
    }

    protected function createTranslations()
    {
        return [
            new Translation(['translation' => 'test 0']),
            new Translation(['translation' => 'test 1']),
            new Translation(['translation' => 'test 2']),
        ];
    }

    protected function createGlossDetails(Gloss $gloss)
    {
        $accountId = $gloss->account_id;

        return [
            new GlossDetail([
                'category' => 'Section 1',
                'text' => 'This is the first item for '.$gloss->external_id,
                'order' => 10,
            ]),
            new GlossDetail([
                'category' => 'Section 2',
                'text' => 'This is the second item for '.$gloss->external_id,
                'order' => 20,
            ]),
            new GlossDetail([
                'category' => 'Section 3',
                'text' => 'This is the third item for '.$gloss->external_id,
                'order' => 30,
            ]),
            new GlossDetail([
                'category' => 'Section 4',
                'text' => 'This is the fourth item for '.$gloss->external_id,
                'order' => 40,
            ]),
            new GlossDetail([
                'category' => 'Section 5',
                'text' => 'This is the fifth item for '.$gloss->external_id,
                'order' => 50,
            ]),
        ];
    }

    protected function getRepository()
    {
        return $this->_glossRepository;
    }
}
