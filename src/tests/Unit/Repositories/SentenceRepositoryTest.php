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

    public function test_expects_get_sentence_to_carry_the_headword_and_gloss()
    {
        $sentence = $this->createSentenceWithTwoFragments(__FUNCTION__);

        $data = resolve(SentenceRepository::class)->getSentence($sentence->id);

        $this->assertNotNull($data);

        $fragment = $data['sentence_fragments']->firstWhere('fragment', '你好');
        $this->assertNotNull($fragment);

        // The written form is what stands in the text; the headword is what the dictionary lists
        // it under. The phrase page shows both, so both must survive getSentence().
        $this->assertNotNull($fragment->lexical_entry);
        $this->assertEquals('hello', $fragment->lexical_entry->word->word);
        $this->assertEquals('greetings', $fragment->lexical_entry->sense->word->word);
    }

    public function test_expects_adjacent_sentences_ordered_by_name()
    {
        // A language of its own: the real corpus has phrases whose names would otherwise sort
        // between the three created here, and adjacency is the whole point of the assertion.
        $language = new Language([
            'name' => 'Adjacency test tongue',
            'is_invented' => true,
            'short_name' => 'adjt',
            'order' => 0,
        ]);
        $language->save();
        $account = Account::first();

        $names = ['AAA adjacency first', 'BBB adjacency middle', 'CCC adjacency last'];
        $sentences = [];
        foreach ($names as $name) {
            $sentence = new Sentence([
                'description' => 'Test sentence',
                'language_id' => $language->id,
                'source' => 'Test source',
                'is_neologism' => false,
                'is_approved' => true,
                'account_id' => $account->id,
                'name' => $name,
            ]);
            $sentence->save();
            $sentences[] = $sentence;
        }

        $repository = resolve(SentenceRepository::class);

        $middle = $repository->getAdjacentSentences($sentences[1]);
        $this->assertEquals($sentences[0]->id, $middle['previous']->id);
        $this->assertEquals($sentences[2]->id, $middle['next']->id);

        $first = $repository->getAdjacentSentences($sentences[0]);
        $this->assertNull($first['previous']);
        $this->assertEquals($sentences[1]->id, $first['next']->id);

        $last = $repository->getAdjacentSentences($sentences[2]);
        $this->assertEquals($sentences[1]->id, $last['previous']->id);
        $this->assertNull($last['next']);
    }

    public function test_expects_adjacent_sentences_to_step_through_a_shared_name()
    {
        // Nothing constrains sentence names to be unique. Comparing on the name alone would
        // step over a tied pair rather than through it, silently skipping a phrase.
        $language = new Language([
            'name' => 'Shared name test tongue',
            'is_invented' => true,
            'short_name' => 'shrt',
            'order' => 0,
        ]);
        $language->save();
        $account = Account::first();

        $sentences = [];
        foreach (['AAA first', 'MMM shared', 'MMM shared', 'ZZZ last'] as $name) {
            $sentence = new Sentence([
                'description' => 'Test sentence',
                'language_id' => $language->id,
                'source' => 'Test source',
                'is_neologism' => false,
                'is_approved' => true,
                'account_id' => $account->id,
                'name' => $name,
            ]);
            $sentence->save();
            $sentences[] = $sentence;
        }

        $repository = resolve(SentenceRepository::class);

        // The two tied phrases are ordered between themselves by id, and neither is skipped.
        $firstOfPair = $repository->getAdjacentSentences($sentences[1]);
        $this->assertEquals($sentences[0]->id, $firstOfPair['previous']->id);
        $this->assertEquals($sentences[2]->id, $firstOfPair['next']->id);

        $secondOfPair = $repository->getAdjacentSentences($sentences[2]);
        $this->assertEquals($sentences[1]->id, $secondOfPair['previous']->id);
        $this->assertEquals($sentences[3]->id, $secondOfPair['next']->id);
    }

    private function createSentenceWithTwoFragments(string $seed): Sentence
    {
        $language = Language::first();
        $account = Account::first();

        extract($this->createLexicalEntry($seed, 'hello'));
        $helloGloss = $this->getLexicalEntryRepository()->saveLexicalEntry('hello', 'greetings', $lexicalEntry, $glosses, $keywords, $details);

        extract($this->createLexicalEntry($seed, 'world'));
        $worldGloss = $this->getLexicalEntryRepository()->saveLexicalEntry('world', 'earth', $lexicalEntry, $glosses, $keywords, $details);

        $fragments = [
            new SentenceFragment([
                'fragment' => '你好',
                'tengwar' => 'hello',
                'lexical_entry_id' => $helloGloss->id,
                'order' => 10,
                'is_linebreak' => false,
                'type' => 0,
                'comments' => '',
            ]),
            new SentenceFragment([
                'fragment' => '世界',
                'tengwar' => 'world',
                'lexical_entry_id' => $worldGloss->id,
                'order' => 20,
                'is_linebreak' => false,
                'type' => 0,
                'comments' => '',
            ]),
        ];

        $sentence = new Sentence([
            'description' => 'Test sentence',
            'language_id' => $language->id,
            'source' => 'Test source',
            'is_neologism' => false,
            'is_approved' => true,
            'account_id' => $account->id,
            'name' => 'Test '.$seed,
        ]);

        resolve(SentenceRepository::class)->saveSentence($sentence, $fragments, [[], []]);

        return $sentence;
    }
}
