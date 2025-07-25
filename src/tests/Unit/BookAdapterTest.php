<?php

namespace Tests\Unit;

use App\Adapters\BookAdapter;
use App\Models\Gloss;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Unit\Traits\CanCreateGloss;

class BookAdapterTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpGlosses;
        CanCreateGloss::getRepository as getLexicalEntryRepository;
    }
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    private $_adapter;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->setUpGlosses();

        $this->_adapter = resolve(BookAdapter::class);
    }

    public function test_adapt_gloss()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'galadh'));
        $lexicalEntry = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $languages = new Collection([$lexicalEntry->language]);
        $inflections = collect([]);
        $comments = [$lexicalEntry->id => 10];
        $atomDate = true;

        $adapted = $this->_adapter->adaptLexicalEntry($lexicalEntry, $languages, $inflections, $comments, $atomDate);

        $this->assertEquals(\stdClass::class, get_class($adapted));
        $this->assertEquals($lexicalEntry->created_at->toAtomString(), $adapted->created_at);
        $this->assertEquals($lexicalEntry->language->id, $adapted->language->id);
        $this->assertEquals($comments[$lexicalEntry->id], $adapted->comment_count);
        $this->assertEquals(implode(config('ed.gloss_translations_separator'), array_map(function ($g) {
            return $g->translation;
        }, $glosses)), $adapted->all_glosses);
    }

    public function test_adapt_glosses_without_details()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'galadh'));

        $lexicalEntry = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, []);

        $versions = $this->getLexicalEntryRepository()->getLexicalEntryVersions($lexicalEntry->id);
        $adapted = $this->_adapter->adaptLexicalEntryVersions($versions->getVersions(), $versions->getLatestVersionId());
        $adaptedGlossary = &$adapted['versions'];

        $this->assertEquals(1, count($adaptedGlossary));
        $this->assertNotNull($adaptedGlossary[0]->lexical_entry_details);
        $this->assertEquals(0, count($adaptedGlossary[0]->lexical_entry_details));
    }

    public function test_rating()
    {
        $glosses = [
            'mal', 'malina', 'malda', 'nan', 'tulca', 'anat',
        ];
        // Updated expected order based on new rating algorithm
        // 'mal' should be first (exact match), followed by 'malda' and 'malina' (starts with)
        $expected = [
            'mal', 'malda', 'malina', 'anat', 'nan', 'tulca',
        ];
        $glossary = [];
        foreach ($glosses as $gloss) {
            extract($this->createLexicalEntry(__FUNCTION__, $gloss));
            $savedGloss = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
            $savedGloss->load('glosses', 'lexical_entry_details');
            $glossary[] = $savedGloss;
        }

        $adapted = $this->_adapter->adaptLexicalEntries($glossary, collect([]), [], 'mal');
        $adaptedGlossary = &$adapted['sections'][0]['entities'];

        for ($i = 0; $i < count($expected); $i += 1) {
            $this->assertEquals($expected[$i], $adaptedGlossary[$i]->word);
        }
    }

    public function test_should_get_versions()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'galadh'));
        $lexicalEntry0 = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $lexicalEntry->is_uncertain = false;
        $glosses = $this->createGlosses();
        $details = $this->createLexicalEntryDetails($lexicalEntry0);
        $lexicalEntry1 = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $newWord = $word.' 1';
        $details = $this->createLexicalEntryDetails($lexicalEntry);
        $lexicalEntry2 = $this->getLexicalEntryRepository()->saveLexicalEntry($newWord, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $glosses = array_merge(
            $this->createGlosses(),
            [new Gloss(['translation' => 'test '.count($glosses)])]
        );
        $details = $this->createLexicalEntryDetails($lexicalEntry);
        $lexicalEntry3 = $this->getLexicalEntryRepository()->saveLexicalEntry($newWord, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $lexicalEntry0->refresh();
        $lexicalEntry1->refresh();
        $lexicalEntry2->refresh();
        $lexicalEntry3->refresh();

        $this->assertEquals($lexicalEntry0->id, $lexicalEntry1->id);
        $this->assertEquals($lexicalEntry0->id, $lexicalEntry2->id);
        $this->assertEquals($lexicalEntry0->id, $lexicalEntry3->id);

        $versions = $this->getLexicalEntryRepository()->getLexicalEntryVersions($lexicalEntry0->id);
        $adapted = $this->_adapter->adaptLexicalEntryVersions($versions->getVersions(), $versions->getLatestVersionId());
        $lexicalEntries = &$adapted['versions'];

        $this->assertEquals(4, count($lexicalEntries));

        $this->assertTrue((bool) $lexicalEntries[0]->_is_latest);
        $this->assertFalse((bool) $lexicalEntries[1]->_is_latest);
        $this->assertFalse((bool) $lexicalEntries[2]->_is_latest);
        $this->assertFalse((bool) $lexicalEntries[3]->_is_latest);
    }

    public function test_calculate_rating_exact_word_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'This is a tree word');
        
        BookAdapter::calculateRating($lexicalEntry, 'galadh');
        
        // Exact word match should get highest score (100 * 1000000 = 100000000)
        $this->assertEquals(100000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_exact_translation_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'This is about trees');
        
        BookAdapter::calculateRating($lexicalEntry, 'tree');
        
        // Exact translation match should get high score
        $this->assertGreaterThan(8000000, $lexicalEntry->rating);
        $this->assertLessThan(100000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_word_boundary_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['big tree'], 'This is about trees');
        
        BookAdapter::calculateRating($lexicalEntry, 'tree');
        
        // Word boundary match should get good score
        $this->assertGreaterThan(7000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_comment_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['light'], 'This word means tree in Sindarin');
        
        BookAdapter::calculateRating($lexicalEntry, 'tree');
        
        // Comment match should get medium score
        $this->assertGreaterThan(600000, $lexicalEntry->rating);
        $this->assertLessThan(8000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_lexical_entry_details_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['light'], 'No tree here', [
            ['category' => 'Etymology', 'text' => 'From tree root']
        ]);
        
        BookAdapter::calculateRating($lexicalEntry, 'tree');
        
        // Gloss details match should get lower score
        // Multiple search terms can contribute, so expect higher score
        $this->assertGreaterThan(50000, $lexicalEntry->rating);
        $this->assertLessThan(1000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_source_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['light'], 'No tree here');
        $lexicalEntry->source = 'Tree Dictionary';
        
        BookAdapter::calculateRating($lexicalEntry, 'tree');
        
        // Source match should get lowest score
        // Multiple search terms can contribute significantly
        $this->assertGreaterThan(1000, $lexicalEntry->rating);
        $this->assertLessThan(1000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_normalized_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'No match here');
        
        BookAdapter::calculateRating($lexicalEntry, 'GALADH');
        
        // Normalized match should work
        $this->assertGreaterThan(90000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_starts_with_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'No match here');
        
        BookAdapter::calculateRating($lexicalEntry, 'gal');
        
        // Starts with match should work
        $this->assertGreaterThan(70000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_contains_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'No match here');
        
        BookAdapter::calculateRating($lexicalEntry, 'ala');
        
        // Contains match should work
        $this->assertGreaterThan(50000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_similarity_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'No match here');
        
        BookAdapter::calculateRating($lexicalEntry, 'galad');
        
        // Similarity match should work
        $this->assertGreaterThan(70000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_no_match()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['light'], 'No tree here');
        
        BookAdapter::calculateRating($lexicalEntry, 'completely_different');
        
        // No match should get default score
        $this->assertEquals(10, $lexicalEntry->rating);
    }

    public function test_calculate_rating_uncertain_gloss_penalty()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'This is a tree');
        $lexicalEntry->is_uncertain = true;
        
        BookAdapter::calculateRating($lexicalEntry, 'tree');
        
        // Uncertain gloss should be ranked lower but still positive
        $this->assertGreaterThan(0, $lexicalEntry->rating);
        $this->assertLessThan(1000000, $lexicalEntry->rating); // Should be much lower than certain gloss
    }

    public function test_calculate_rating_non_canon_gloss_penalty()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'This is a tree');
        $lexicalEntry->is_canon = false;
        
        BookAdapter::calculateRating($lexicalEntry, 'tree');
        
        // Non-canon gloss should be ranked lower but still positive
        $this->assertGreaterThan(0, $lexicalEntry->rating);
        $this->assertLessThan(1000000, $lexicalEntry->rating); // Should be much lower than canon gloss
    }

    public function test_calculate_rating_empty_search_word()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'This is a tree');
        
        $result = BookAdapter::calculateRating($lexicalEntry, '');
        
        // Empty search word should return maximum value
        $this->assertEquals(1 << 31, $result);
    }

    public function test_calculate_rating_multiple_translations()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['light', 'tree', 'bright'], 'No match here');
        
        BookAdapter::calculateRating($lexicalEntry, 'tree');
        
        // Should match the best translation
        $this->assertGreaterThan(8000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_multiple_lexical_entry_details()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['light'], 'No match here', [
            ['category' => 'Etymology', 'text' => 'From light root'],
            ['category' => 'Usage', 'text' => 'Used for tree in some contexts']
        ]);
        
        BookAdapter::calculateRating($lexicalEntry, 'tree');
        
        // Should match the best detail
        $this->assertGreaterThan(50000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_field_priority_order()
    {
        // Create gloss with matches in different fields
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['light'], 'This word means tree in Sindarin', [
            ['category' => 'Etymology', 'text' => 'From tree root']
        ]);
        $lexicalEntry->source = 'Tree Dictionary';
        
        BookAdapter::calculateRating($lexicalEntry, 'tree');
        
        // Should prioritize word > translations > comments > details > source
        // Since no word/translation match, comments should win
        $this->assertGreaterThan(600000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_diacritics_handling()
    {
        $lexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'No match here');
        
        BookAdapter::calculateRating($lexicalEntry, 'galádh');
        
        // Should handle diacritics
        $this->assertGreaterThan(90000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_case_insensitive()
    {
        $lexicalEntry = $this->createMockLexicalEntry('Galadh', ['Tree'], 'No match here');
        
        BookAdapter::calculateRating($lexicalEntry, 'galadh');
        
        // Should be case insensitive
        $this->assertGreaterThan(90000000, $lexicalEntry->rating);
    }

    public function test_calculate_rating_certain_vs_uncertain_ranking()
    {
        // Create certain and uncertain glosses with same content
        $certainLexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'This is a tree');
        $certainLexicalEntry->is_canon = true;
        $certainLexicalEntry->is_uncertain = false;
        
        $uncertainLexicalEntry = $this->createMockLexicalEntry('galadh', ['tree'], 'This is a tree');
        $uncertainLexicalEntry->is_canon = true;
        $uncertainLexicalEntry->is_uncertain = true;
        
        BookAdapter::calculateRating($certainLexicalEntry, 'tree');
        BookAdapter::calculateRating($uncertainLexicalEntry, 'tree');
        
        // Certain gloss should have higher rating than uncertain gloss
        $this->assertGreaterThan($uncertainLexicalEntry->rating, $certainLexicalEntry->rating);
        
        // Both should be positive
        $this->assertGreaterThan(0, $certainLexicalEntry->rating);
        $this->assertGreaterThan(0, $uncertainLexicalEntry->rating);
        
        // Uncertain gloss should be about 10% of certain gloss rating
        $this->assertEquals($uncertainLexicalEntry->rating, $certainLexicalEntry->rating * 0.1, '', 0.1);
    }

    /**
     * Helper method to create a mock gloss object for testing
     */
    private function createMockLexicalEntry(string $word, array $glosses, string $comments = '', array $glossDetails = []): \stdClass
    {
        $gloss = new \stdClass();
        $gloss->word = $word;
        $gloss->comments = $comments;
        $gloss->source = '';
        $gloss->is_canon = true;
        $gloss->is_uncertain = false;
        // Create gloss objects
        $gloss->glosses = collect([]);
        foreach ($glosses as $glossText) {
            $glossObj = new \stdClass();
            $glossObj->translation = $glossText;
            $gloss->glosses->push($glossObj);
        }
        // Create gloss detail objects
        $gloss->lexical_entry_details = collect([]);
        foreach ($glossDetails as $detail) {
            $detailObj = new \stdClass();
            $detailObj->text = $detail['text'];
            $detailObj->category = $detail['category'];
            $gloss->lexical_entry_details->push($detailObj);
        }
        return $gloss;
    }
}
