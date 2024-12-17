<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use Tests\Unit\Traits\CanCreateGloss;
use App\Repositories\SentenceRepository;
use App\Models\{
    Account,
    Inflection,
    Language,
    Sentence,
    SentenceFragment
};
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SentenceRepositoryTest extends TestCase
{
    use DatabaseTransactions; 

    use CanCreateGloss {
        CanCreateGloss::setUp as setUpGlosses;
        CanCreateGloss::getRepository as getGlossRepository;
    } // ; <-- remedies Visual Studio Code colouring bug

    public function testExpectsSuccessfulSentenceSaveLog()
    {
        $language = Language::first();
        $account  = Account::first();

        extract( $this->createGloss(__FUNCTION__) );
        $helloGloss = $this->getGlossRepository()->saveGloss('hello', 'greetings', $gloss, $translations, $keywords, $details);

        extract( $this->createGloss(__FUNCTION__) );
        $worldGloss = $this->getGlossRepository()->saveGloss('world', 'earth', $gloss, $translations, $keywords, $details);

        $this->assertNotEquals($helloGloss->id, $worldGloss->id);

        $fragments = [
            new SentenceFragment([
                'fragment'     => '你好',
                'tengwar'      => 'hello',
                'gloss_id'     => $helloGloss->id,
                'order'        => 10,
                'is_linebreak' => false,
                'type'         => 0,
                'comments'     => 'comments 1'
            ]),
            new SentenceFragment([
                'fragment'     => '世界',
                'tengwar'      => 'world',
                'gloss_id'     => $worldGloss->id,
                'order'        => 20,
                'is_linebreak' => false,
                'type'         => 0,
                'comments'     => 'comments 2'
            ])
        ];

        $sentence = new Sentence([
            'description'      => 'Test sentence',
            'language_id'      => $language->id,
            'source'           => 'Test source',
            'is_neologism'     => true,
            'account_id'       => $account->id,
            'long_description' => 'Test description',
            'name'             => 'Test'
        ]);

        $inflections = [[], []];

        resolve(SentenceRepository::class)->saveSentence($sentence, $fragments, $inflections);
        $sentence->load('sentence_fragments');

        $this->assertTrue($sentence->id !== 0);

        $savedFragments = $sentence->sentence_fragments;
        $this->assertEquals(count($fragments), $savedFragments->count());

        for ($i = 0; $i < count($fragments); $i += 1) {
            $savedFragment = $sentence->sentence_fragments[$i];

            $this->assertEquals($fragments[$i]->fragment, $savedFragment->fragment);
            $this->assertEquals($fragments[$i]->tengwar, $savedFragment->tengwar);
            $this->assertEquals($fragments[$i]->gloss_id, $savedFragment->gloss_id);
            $this->assertEquals($fragments[$i]->is_linebreak, $savedFragment->is_linebreak);
            $this->assertEquals($fragments[$i]->type, $savedFragment->type);
            $this->assertEquals($fragments[$i]->comments, $savedFragment->comments);

            $savedKeywords = $savedFragment->keywords;
            
            $this->assertEquals(0, $savedKeywords->count());
        }
    }
}
