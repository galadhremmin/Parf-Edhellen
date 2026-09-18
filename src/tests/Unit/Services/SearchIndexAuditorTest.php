<?php

namespace Tests\Unit\Services;

use App\Jobs\ProcessLexicalEntryReindex;
use App\Repositories\SearchIndexRepository;
use App\Services\SearchIndexAuditor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Traits\CanCreateGloss;

class SearchIndexAuditorTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpLexicalEntries;
        CanCreateGloss::getRepository as getLexicalEntryRepository;
    }
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    public function test_reports_an_entry_missing_a_term()
    {
        $entry = $this->createIndexedEntry();
        $repository = resolve(SearchIndexRepository::class);
        $lost = $repository->getForEntity($entry)->first();
        $lost->delete();

        $this->assertEquals([$lost->keyword], $this->missingTermsFor($entry->id));
    }

    public function test_reports_an_entry_with_no_index()
    {
        $entry = $this->createIndexedEntry();
        resolve(SearchIndexRepository::class)->deleteAll($entry);

        $auditor = resolve(SearchIndexAuditor::class);
        $this->assertContains($entry->id, $auditor->entriesWithoutIndex());
        // An entry with no index at all is reported by entriesWithoutIndex, not as missing terms.
        $this->assertEmpty($this->missingTermsFor($entry->id));
    }

    public function test_reports_nothing_for_a_freshly_indexed_entry()
    {
        $entry = $this->createIndexedEntry();

        $this->assertEmpty($this->missingTermsFor($entry->id));
        $this->assertNotContains($entry->id, resolve(SearchIndexAuditor::class)->entriesWithoutIndex());
    }

    private function createIndexedEntry()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'auditword'.uniqid()));
        $entry = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);

        ProcessLexicalEntryReindex::dispatchSync($entry);

        return $entry;
    }

    /**
     * @return string[] the terms the auditor reports as missing for one entry
     */
    private function missingTermsFor(int $id): array
    {
        $terms = [];

        resolve(SearchIndexAuditor::class)->eachEntryWithMissingTerms(function ($entryId, $missing) use (&$terms) {
            $terms = $missing->keys()->all();
        }, [$id]);

        return $terms;
    }
}
