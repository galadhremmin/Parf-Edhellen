<?php

namespace Tests\Unit\Repositories;

use App\Jobs\ProcessLexicalEntryReindex;
use App\Models\Keyword;
use App\Models\SearchKeyword;
use App\Repositories\KeywordRepository;
use App\Repositories\SearchIndexRepository;
use App\Repositories\WordRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Traits\CanCreateGloss;

class SearchIndexRepositoryTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpLexicalEntries;
        CanCreateGloss::getRepository as getLexicalEntryRepository;
    }
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    public function test_recreates_index_after_it_was_deleted()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'indexword'.uniqid()));
        $entry = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
        $repository = resolve(SearchIndexRepository::class);

        $repository->deleteAll($entry);
        $repository->createIndex($entry, $entry->word, $entry->language);
        $this->assertCount(1, $repository->getForEntity($entry));

        // A queue worker is a long-lived process: re-indexing an edited entry in the same process must write again.
        $repository->deleteAll($entry);
        $repository->createIndex($entry, $entry->word, $entry->language);
        $this->assertCount(1, $repository->getForEntity($entry));
    }

    public function test_reindex_job_replaces_index_with_current_data()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'indexword'.uniqid()));
        $entry = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
        $repository = resolve(SearchIndexRepository::class);

        // A row left behind by an earlier version of the entry.
        $staleWord = resolve(WordRepository::class)->save('stale '.uniqid(), $entry->account_id);
        $repository->createIndex($entry, $staleWord, $entry->language);

        ProcessLexicalEntryReindex::dispatchSync($entry);
        $expected = collect(array_merge([$word, $sense], $keywords, array_map(fn ($g) => $g->translation, $glosses)))
            ->map(fn ($k) => mb_strtolower($k))
            ->unique()->sort()->values()->all();
        // The entry is a verb, so English keywords are also indexed as "to ..." infinitives.
        $actual = $repository->getForEntity($entry)->pluck('keyword')
            ->reject(fn ($k) => str_starts_with($k, 'to ') && ! in_array($k, $expected))
            ->unique()->sort()->values()->all();
        $this->assertEquals($expected, $actual);

        $count = $repository->getForEntity($entry)->count();
        ProcessLexicalEntryReindex::dispatchSync($entry);
        $this->assertEquals($count, $repository->getForEntity($entry)->count());
    }

    public function test_reindex_job_clears_index_of_deleted_entry()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'indexword'.uniqid()));
        $entry = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
        $repository = resolve(SearchIndexRepository::class);

        ProcessLexicalEntryReindex::dispatchSync($entry);
        $this->assertNotEmpty($repository->getForEntity($entry));

        $entry->is_deleted = true;
        $entry->save();
        ProcessLexicalEntryReindex::dispatchSync($entry);

        $this->assertEmpty($repository->getForEntity($entry));
    }

    public function test_keeps_accent_variants_apart()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'indexword'.uniqid()));
        $entry = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
        $repository = resolve(SearchIndexRepository::class);
        $keywords = resolve(KeywordRepository::class);
        $words = resolve(WordRepository::class);

        $suffix = uniqid();
        foreach (['la'.$suffix, 'lá'.$suffix] as $variant) {
            $variantWord = $words->save($variant, $entry->account_id);
            $repository->createIndex($entry, $variantWord, $entry->language);
            $keywords->createKeyword($variantWord, $entry->sense, $entry, $entry->language);
        }

        // Both columns have accent-insensitive collations, so compare bytes.
        $this->assertEquals(2, SearchKeyword::where('entity_id', $entry->id)->whereRaw('BINARY keyword IN (?, ?)', ['la'.$suffix, 'lá'.$suffix])->count());
        $this->assertEquals(2, Keyword::where('lexical_entry_id', $entry->id)->whereRaw('BINARY keyword IN (?, ?)', ['la'.$suffix, 'lá'.$suffix])->count());
    }

    public function test_does_not_duplicate_identical_index()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'indexword'.uniqid()));
        $entry = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
        $repository = resolve(SearchIndexRepository::class);

        $repository->deleteAll($entry);
        $repository->createIndex($entry, $entry->word, $entry->language);
        $repository->createIndex($entry, $entry->word, $entry->language);

        $this->assertCount(1, $repository->getForEntity($entry));
    }
}
