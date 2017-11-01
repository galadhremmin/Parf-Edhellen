<?php

namespace Tests\Unit\Traits;

use Auth;

use App\Repositories\GlossRepository;
use App\Models\{
    Gloss,
    GlossGroup,
    Language,
    Speech,
    Translation
};

trait CanCreateGloss
{
    use MocksAuth {
        MocksAuth::setUp as setUpAuth;
    } // ;

    protected $_glossGroup;
    protected $_glossRepository;

    protected function setUp()
    {
        $this->setUpAuth();

        $this->_glossRepository = resolve(GlossRepository::class);
        $this->_glossGroup = GlossGroup::firstOrCreate([
            'name' => 'Unit tests',
            'is_canon' => true
        ]);

        $this->cleanGlosses();
    }

    protected function tearDown()
    {
        $this->cleanGlosses();
    }

    protected function cleanGlosses()
    {
        $glosses = Gloss::where('gloss_group_id', $this->_glossGroup->id)
            ->get();

        foreach ($glosses as $gloss)
        {
            $gloss->keywords()->delete();
            $gloss->translations()->delete();
            
            $sense = $gloss->sense;
            if ($sense) {
                $sense->keywords()->delete();
                $sense->delete();
            }

            $gloss->delete();
        }
    }

    /**
     * Creates a gloss.
     *
     * @param string $word
     * @param string $sense
     * @param string $method
     * @return void
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
        $gloss->external_id = 'UA-Unit-GlossRepository-'.$method;

        $translations = $this->createTranslations();
        
        $keywords = [
            'test 3',
            'test 4', 
            'test 5'
        ];

        $sense = $method.'-'.$word;

        return [
            'word' => $word,
            'sense' => $sense,
            'gloss' => $gloss,
            'translations' => $translations,
            'keywords' => $keywords
        ];
    }

    protected function createTranslations()
    {
        return [
            new Translation([ 'translation' => 'test 0' ]),
            new Translation([ 'translation' => 'test 1' ]),
            new Translation([ 'translation' => 'test 2' ])
        ];
    }

    protected function getRepository()
    {
        return $this->_glossRepository;
    }
}