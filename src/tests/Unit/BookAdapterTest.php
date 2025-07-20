<?php

namespace Tests\Unit;

use App\Adapters\BookAdapter;
use App\Models\Translation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Unit\Traits\CanCreateGloss;

class BookAdapterTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpGlosses;
        CanCreateGloss::getRepository as getGlossRepository;
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
        extract($this->createGloss(__FUNCTION__));
        $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);

        $languages = new Collection([$gloss->language]);
        $inflections = collect([]);
        $comments = [$gloss->id => 10];
        $atomDate = true;

        $adapted = $this->_adapter->adaptGloss($gloss, $languages, $inflections, $comments, $atomDate);

        $this->assertEquals(\stdClass::class, get_class($adapted));
        $this->assertEquals($gloss->created_at->toAtomString(), $adapted->created_at);
        $this->assertEquals($gloss->language->id, $adapted->language->id);
        $this->assertEquals($comments[$gloss->id], $adapted->comment_count);
        $this->assertEquals(implode(config('ed.gloss_translations_separator'), array_map(function ($t) {
            return $t->translation;
        }, $translations)), $adapted->all_translations);
    }

    public function test_adapt_glosses_without_details()
    {
        extract($this->createGloss(__FUNCTION__));

        $gloss = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, []);

        $versions = $this->getGlossRepository()->getGlossVersions($gloss->id);
        $adapted = $this->_adapter->adaptGlossVersions($versions->getVersions(), $versions->getLatestVersionId());
        $adaptedGlossary = &$adapted['versions'];

        $this->assertEquals(1, count($adaptedGlossary));
        $this->assertNotNull($adaptedGlossary[0]->gloss_details);
        $this->assertEquals(0, count($adaptedGlossary[0]->gloss_details));
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
            extract($this->createGloss(__FUNCTION__, $gloss));
            $savedGloss = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
            $savedGloss->load('translations', 'gloss_details');
            $glossary[] = $savedGloss;
        }

        $adapted = $this->_adapter->adaptGlosses($glossary, collect([]), [], 'mal');
        $adaptedGlossary = &$adapted['sections'][0]['entities'];

        for ($i = 0; $i < count($expected); $i += 1) {
            $this->assertEquals($expected[$i], $adaptedGlossary[$i]->word);
        }
    }

    public function test_should_get_versions()
    {
        extract($this->createGloss(__FUNCTION__));
        $gloss0 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);

        $gloss->is_uncertain = false;
        $translations = $this->createTranslations();
        $details = $this->createGlossDetails($gloss);
        $gloss1 = $this->getGlossRepository()->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);

        $newWord = $word.' 1';
        $translations = $this->createTranslations();
        $details = $this->createGlossDetails($gloss);
        $gloss2 = $this->getGlossRepository()->saveGloss($newWord, $sense, $gloss, $translations, $keywords, $details);

        $translations = array_merge(
            $this->createTranslations(),
            [new Translation(['translation' => 'test '.count($translations)])]
        );
        $details = $this->createGlossDetails($gloss);
        $gloss3 = $this->getGlossRepository()->saveGloss($newWord, $sense, $gloss, $translations, $keywords, $details);

        $gloss0->refresh();
        $gloss1->refresh();
        $gloss2->refresh();
        $gloss3->refresh();

        $this->assertEquals($gloss0->id, $gloss1->id);
        $this->assertEquals($gloss0->id, $gloss2->id);
        $this->assertEquals($gloss0->id, $gloss3->id);

        $versions = $this->getGlossRepository()->getGlossVersions($gloss0->id);
        $adapted = $this->_adapter->adaptGlossVersions($versions->getVersions(), $versions->getLatestVersionId());
        $glosses = &$adapted['versions'];

        $this->assertEquals(4, count($glosses));

        $this->assertTrue((bool) $glosses[0]->_is_latest);
        $this->assertFalse((bool) $glosses[1]->_is_latest);
        $this->assertFalse((bool) $glosses[2]->_is_latest);
        $this->assertFalse((bool) $glosses[3]->_is_latest);
    }

    public function test_calculate_rating_exact_word_match()
    {
        $gloss = $this->createMockGloss('galadh', ['tree'], 'This is a tree word');
        
        BookAdapter::calculateRating($gloss, 'galadh');
        
        // Exact word match should get highest score (100 * 1000000 = 100000000)
        $this->assertEquals(100000000, $gloss->rating);
    }

    public function test_calculate_rating_exact_translation_match()
    {
        $gloss = $this->createMockGloss('galadh', ['tree'], 'This is about trees');
        
        BookAdapter::calculateRating($gloss, 'tree');
        
        // Exact translation match should get high score
        $this->assertGreaterThan(8000000, $gloss->rating);
        $this->assertLessThan(100000000, $gloss->rating);
    }

    public function test_calculate_rating_word_boundary_match()
    {
        $gloss = $this->createMockGloss('galadh', ['big tree'], 'This is about trees');
        
        BookAdapter::calculateRating($gloss, 'tree');
        
        // Word boundary match should get good score
        $this->assertGreaterThan(7000000, $gloss->rating);
    }

    public function test_calculate_rating_comment_match()
    {
        $gloss = $this->createMockGloss('galadh', ['light'], 'This word means tree in Sindarin');
        
        BookAdapter::calculateRating($gloss, 'tree');
        
        // Comment match should get medium score
        $this->assertGreaterThan(600000, $gloss->rating);
        $this->assertLessThan(8000000, $gloss->rating);
    }

    public function test_calculate_rating_gloss_details_match()
    {
        $gloss = $this->createMockGloss('galadh', ['light'], 'No tree here', [
            ['category' => 'Etymology', 'text' => 'From tree root']
        ]);
        
        BookAdapter::calculateRating($gloss, 'tree');
        
        // Gloss details match should get lower score
        // Multiple search terms can contribute, so expect higher score
        $this->assertGreaterThan(50000, $gloss->rating);
        $this->assertLessThan(1000000, $gloss->rating);
    }

    public function test_calculate_rating_source_match()
    {
        $gloss = $this->createMockGloss('galadh', ['light'], 'No tree here');
        $gloss->source = 'Tree Dictionary';
        
        BookAdapter::calculateRating($gloss, 'tree');
        
        // Source match should get lowest score
        // Multiple search terms can contribute significantly
        $this->assertGreaterThan(1000, $gloss->rating);
        $this->assertLessThan(1000000, $gloss->rating);
    }

    public function test_calculate_rating_normalized_match()
    {
        $gloss = $this->createMockGloss('galadh', ['tree'], 'No match here');
        
        BookAdapter::calculateRating($gloss, 'GALADH');
        
        // Normalized match should work
        $this->assertGreaterThan(90000000, $gloss->rating);
    }

    public function test_calculate_rating_starts_with_match()
    {
        $gloss = $this->createMockGloss('galadh', ['tree'], 'No match here');
        
        BookAdapter::calculateRating($gloss, 'gal');
        
        // Starts with match should work
        $this->assertGreaterThan(70000000, $gloss->rating);
    }

    public function test_calculate_rating_contains_match()
    {
        $gloss = $this->createMockGloss('galadh', ['tree'], 'No match here');
        
        BookAdapter::calculateRating($gloss, 'ala');
        
        // Contains match should work
        $this->assertGreaterThan(50000000, $gloss->rating);
    }

    public function test_calculate_rating_similarity_match()
    {
        $gloss = $this->createMockGloss('galadh', ['tree'], 'No match here');
        
        BookAdapter::calculateRating($gloss, 'galad');
        
        // Similarity match should work
        $this->assertGreaterThan(70000000, $gloss->rating);
    }

    public function test_calculate_rating_no_match()
    {
        $gloss = $this->createMockGloss('galadh', ['light'], 'No tree here');
        
        BookAdapter::calculateRating($gloss, 'completely_different');
        
        // No match should get default score
        $this->assertEquals(10, $gloss->rating);
    }

    public function test_calculate_rating_uncertain_gloss_penalty()
    {
        $gloss = $this->createMockGloss('galadh', ['tree'], 'This is a tree');
        $gloss->is_uncertain = true;
        
        BookAdapter::calculateRating($gloss, 'tree');
        
        // Uncertain gloss should be ranked lower but still positive
        $this->assertGreaterThan(0, $gloss->rating);
        $this->assertLessThan(1000000, $gloss->rating); // Should be much lower than certain gloss
    }

    public function test_calculate_rating_non_canon_gloss_penalty()
    {
        $gloss = $this->createMockGloss('galadh', ['tree'], 'This is a tree');
        $gloss->is_canon = false;
        
        BookAdapter::calculateRating($gloss, 'tree');
        
        // Non-canon gloss should be ranked lower but still positive
        $this->assertGreaterThan(0, $gloss->rating);
        $this->assertLessThan(1000000, $gloss->rating); // Should be much lower than canon gloss
    }

    public function test_calculate_rating_empty_search_word()
    {
        $gloss = $this->createMockGloss('galadh', ['tree'], 'This is a tree');
        
        $result = BookAdapter::calculateRating($gloss, '');
        
        // Empty search word should return maximum value
        $this->assertEquals(1 << 31, $result);
    }

    public function test_calculate_rating_multiple_translations()
    {
        $gloss = $this->createMockGloss('galadh', ['light', 'tree', 'bright'], 'No match here');
        
        BookAdapter::calculateRating($gloss, 'tree');
        
        // Should match the best translation
        $this->assertGreaterThan(8000000, $gloss->rating);
    }

    public function test_calculate_rating_multiple_gloss_details()
    {
        $gloss = $this->createMockGloss('galadh', ['light'], 'No match here', [
            ['category' => 'Etymology', 'text' => 'From light root'],
            ['category' => 'Usage', 'text' => 'Used for tree in some contexts']
        ]);
        
        BookAdapter::calculateRating($gloss, 'tree');
        
        // Should match the best detail
        $this->assertGreaterThan(50000, $gloss->rating);
    }

    public function test_calculate_rating_field_priority_order()
    {
        // Create gloss with matches in different fields
        $gloss = $this->createMockGloss('galadh', ['light'], 'This word means tree in Sindarin', [
            ['category' => 'Etymology', 'text' => 'From tree root']
        ]);
        $gloss->source = 'Tree Dictionary';
        
        BookAdapter::calculateRating($gloss, 'tree');
        
        // Should prioritize word > translations > comments > details > source
        // Since no word/translation match, comments should win
        $this->assertGreaterThan(600000, $gloss->rating);
    }

    public function test_calculate_rating_diacritics_handling()
    {
        $gloss = $this->createMockGloss('galadh', ['tree'], 'No match here');
        
        BookAdapter::calculateRating($gloss, 'galádh');
        
        // Should handle diacritics
        $this->assertGreaterThan(90000000, $gloss->rating);
    }

    public function test_calculate_rating_case_insensitive()
    {
        $gloss = $this->createMockGloss('Galadh', ['Tree'], 'No match here');
        
        BookAdapter::calculateRating($gloss, 'galadh');
        
        // Should be case insensitive
        $this->assertGreaterThan(90000000, $gloss->rating);
    }

    public function test_calculate_rating_certain_vs_uncertain_ranking()
    {
        // Create certain and uncertain glosses with same content
        $certainGloss = $this->createMockGloss('galadh', ['tree'], 'This is a tree');
        $certainGloss->is_canon = true;
        $certainGloss->is_uncertain = false;
        
        $uncertainGloss = $this->createMockGloss('galadh', ['tree'], 'This is a tree');
        $uncertainGloss->is_canon = true;
        $uncertainGloss->is_uncertain = true;
        
        BookAdapter::calculateRating($certainGloss, 'tree');
        BookAdapter::calculateRating($uncertainGloss, 'tree');
        
        // Certain gloss should have higher rating than uncertain gloss
        $this->assertGreaterThan($uncertainGloss->rating, $certainGloss->rating);
        
        // Both should be positive
        $this->assertGreaterThan(0, $certainGloss->rating);
        $this->assertGreaterThan(0, $uncertainGloss->rating);
        
        // Uncertain gloss should be about 10% of certain gloss rating
        $this->assertEquals($uncertainGloss->rating, $certainGloss->rating * 0.1, '', 0.1);
    }

    /**
     * Helper method to create a mock gloss object for testing
     */
    private function createMockGloss(string $word, array $translations, string $comments = '', array $glossDetails = []): \stdClass
    {
        $gloss = new \stdClass();
        $gloss->word = $word;
        $gloss->comments = $comments;
        $gloss->source = '';
        $gloss->is_canon = true;
        $gloss->is_uncertain = false;
        
        // Create translation objects
        $gloss->translations = [];
        foreach ($translations as $translation) {
            $translationObj = new \stdClass();
            $translationObj->translation = $translation;
            $gloss->translations[] = $translationObj;
        }
        
        // Create gloss detail objects
        $gloss->gloss_details = [];
        foreach ($glossDetails as $detail) {
            $detailObj = new \stdClass();
            $detailObj->text = $detail['text'];
            $detailObj->category = $detail['category'];
            $gloss->gloss_details[] = $detailObj;
        }
        
        return $gloss;
    }
}
