<?php

namespace Tests\Unit\Traits;

use Auth;

use App\Repositories\GlossRepository;
use App\Models\{
    Gloss,
    GlossDetail,
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
            $gloss->gloss_details()->delete();
            
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
        $gloss->external_id = 'UA-Unit-GlossRepository-'.$method;
        $gloss->label = null;

        $translations = $this->createTranslations();
        
        $keywords = [
            'test 3',
            'test 4', 
            'test 5'
        ];

        $details = $this->createGlossDetails($gloss);

        $sense = $method.'-'.$word;

        return [
            'word' => $word,
            'sense' => $sense,
            'gloss' => $gloss,
            'translations' => $translations,
            'keywords' => $keywords,
            'details' => $details
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

    protected function createGlossDetails(Gloss $gloss)
    {
        $accountId = $gloss->account_id;
        return [
            new GlossDetail([
                'category'   => 'Section 1',
                'text'       => 'This is the first item for '.$gloss->external_id,
                'order'      => 10,
                'account_id' => $accountId
            ]),
            new GlossDetail([
                'category'   => 'Section 2',
                'text'       => 'This is the second item for '.$gloss->external_id,
                'order'      => 20,
                'account_id' => $accountId
            ]),
            new GlossDetail([
                'category'   => 'Section 3',
                'text'       => 'This is the third item for '.$gloss->external_id,
                'order'      => 30,
                'account_id' => $accountId
            ]),
            new GlossDetail([
                'category'   => 'Section 4',
                'text'       => 'This is the fourth item for '.$gloss->external_id,
                'order'      => 40,
                'account_id' => $accountId
            ]),
            new GlossDetail([
                'category'   => 'Section 5',
                'text'       => 'This is the fifth item for '.$gloss->external_id,
                'order'      => 50,
                'account_id' => $accountId
            ])
        ];
    }

    protected function getRepository()
    {
        return $this->_glossRepository;
    }
}