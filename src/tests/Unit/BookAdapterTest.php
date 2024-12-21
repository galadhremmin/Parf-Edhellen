<?php

namespace Tests\Unit;

use App\Adapters\BookAdapter;
use App\Models\Translation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Queue;
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
        $expected = [
            'mal', 'malda', 'malina', 'nan', 'anat', 'tulca',
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
}
