<?php

namespace Tests\Unit\Repositories;

use App\Models\Account;
use App\Models\Language;
use App\Models\Sentence;
use App\Models\SentenceFragment;
use App\Repositories\SentenceRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Traits\CanCreateGloss;

class SentenceRepositoryTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpGlosses;
        CanCreateGloss::getRepository as getLexicalEntryRepository;
    }
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    public function test_expects_successful_sentence_save_log()
    {
        $language = Language::first();
        $account = Account::first();

        extract($this->createLexicalEntry(__FUNCTION__, 'hello'));
        $helloGloss = $this->getLexicalEntryRepository()->saveLexicalEntry('hello', 'greetings', $lexicalEntry, $glosses, $keywords, $details);

        extract($this->createLexicalEntry(__FUNCTION__, 'world'));
        $worldGloss = $this->getLexicalEntryRepository()->saveLexicalEntry('world', 'earth', $lexicalEntry, $glosses, $keywords, $details);

        $this->assertNotEquals($helloGloss->id, $worldGloss->id);

        $fragments = [
            new SentenceFragment([
                'fragment' => '你好',
                'tengwar' => 'hello',
                'lexical_entry_id' => $helloGloss->id,
                'order' => 10,
                'is_linebreak' => false,
                'type' => 0,
                'comments' => 'comments 1',
            ]),
            new SentenceFragment([
                'fragment' => '世界',
                'tengwar' => 'world',
                'lexical_entry_id' => $worldGloss->id,
                'order' => 20,
                'is_linebreak' => false,
                'type' => 0,
                'comments' => 'comments 2',
            ]),
        ];

        $sentence = new Sentence([
            'description' => 'Test sentence',
            'language_id' => $language->id,
            'source' => 'Test source',
            'is_neologism' => true,
            'account_id' => $account->id,
            'long_description' => 'Test description',
            'name' => 'Test',
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
            $this->assertEquals($fragments[$i]->lexical_entry_id, $savedFragment->lexical_entry_id);
            $this->assertEquals($fragments[$i]->is_linebreak, $savedFragment->is_linebreak);
            $this->assertEquals($fragments[$i]->type, $savedFragment->type);
            $this->assertEquals($fragments[$i]->comments, $savedFragment->comments);

            $savedKeywords = $savedFragment->keywords;

            $this->assertEquals(0, $savedKeywords->count());
        }
    }
}
