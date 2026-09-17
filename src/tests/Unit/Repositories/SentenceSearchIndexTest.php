<?php

namespace Tests\Unit\Repositories;

use App\Jobs\ProcessSentenceReindex;
use App\Models\Account;
use App\Models\Language;
use App\Models\Sentence;
use App\Models\SentenceFragment;
use App\Repositories\SearchIndexRepository;
use App\Repositories\SentenceRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Traits\CanCreateGloss;

class SentenceSearchIndexTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpGlosses;
        CanCreateGloss::getRepository as getLexicalEntryRepository;
    }
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    public function test_indexes_linked_words_as_inflections_and_unlinked_words_under_themselves()
    {
        $suffix = uniqid();
        [$sentence, $linked, $unlinked] = $this->createSentence($suffix);

        ProcessSentenceReindex::dispatchSync($sentence);
        $repository = resolve(SearchIndexRepository::class);

        // The linked word is an inflection of its entry, so results read "entry -> fragment".
        $linkedRows = $repository->getForEntity($linked);
        $this->assertCount(1, $linkedRows);
        $this->assertEquals('mutated'.$suffix, $linkedRows->first()->keyword);
        $this->assertEquals('entryword'.$suffix, $linkedRows->first()->word);

        // The unlinked word has no entry to hang off, so it's indexed under itself.
        $unlinkedRows = $repository->getForEntity($unlinked);
        $this->assertCount(1, $unlinkedRows);
        $this->assertEquals('unlinked'.$suffix, $unlinkedRows->first()->keyword);
        $this->assertEquals('unlinked'.$suffix, $unlinkedRows->first()->word);
    }

    public function test_index_rows_carry_the_phrase_language()
    {
        $suffix = uniqid();
        [$sentence, $linked, $unlinked] = $this->createSentence($suffix);

        ProcessSentenceReindex::dispatchSync($sentence);
        $repository = resolve(SearchIndexRepository::class);

        // Searches filtered by language match on language_id, so phrases drop out when it's missing.
        foreach ([$linked, $unlinked] as $fragment) {
            $this->assertEquals($sentence->language_id, $repository->getForEntity($fragment)->first()->language_id);
        }
    }

    public function test_reindexing_replaces_stale_rows()
    {
        $suffix = uniqid();
        [$sentence, $linked] = $this->createSentence($suffix);

        ProcessSentenceReindex::dispatchSync($sentence);
        $before = resolve(SearchIndexRepository::class)->getForEntity($linked)->count();

        ProcessSentenceReindex::dispatchSync($sentence);

        $this->assertEquals($before, resolve(SearchIndexRepository::class)->getForEntity($linked)->count());
    }

    /**
     * @return array{0: Sentence, 1: SentenceFragment, 2: SentenceFragment}
     */
    private function createSentence(string $suffix): array
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'entryword'.$suffix));
        $entry = $this->getLexicalEntryRepository()->saveLexicalEntry('entryword'.$suffix, 'sense'.$suffix, $lexicalEntry, $glosses, $keywords, $details);

        $fragments = [
            new SentenceFragment([
                'fragment' => 'mutated'.$suffix,
                'lexical_entry_id' => $entry->id,
                'order' => 10,
                'is_linebreak' => false,
                'type' => 0,
                'comments' => '',
            ]),
            new SentenceFragment([
                'fragment' => 'unlinked'.$suffix,
                'order' => 20,
                'is_linebreak' => false,
                'type' => 0,
                'comments' => '',
            ]),
        ];

        $sentence = new Sentence([
            'description' => 'Search index test',
            'language_id' => Language::first()->id,
            'source' => 'Unit test',
            'is_neologism' => true,
            'account_id' => Account::first()->id,
            'long_description' => 'Search index test',
            'name' => 'Search index test '.$suffix,
        ]);

        resolve(SentenceRepository::class)->saveSentence($sentence, $fragments, [[], []]);
        $sentence->load('sentence_fragments');

        return [$sentence, $sentence->sentence_fragments[0], $sentence->sentence_fragments[1]];
    }
}
