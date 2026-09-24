<?php

namespace Tests\Unit\Services\Senses;

use App\Services\Senses\ConceptCandidateFinder;
use Tests\TestCase;
use Tests\Unit\Traits\CanBuildSenses;

class ConceptCandidateFinderTest extends TestCase
{
    use CanBuildSenses;

    private ConceptCandidateFinder $_finder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireWordNet();
        $this->_finder = resolve(ConceptCandidateFinder::class);
    }

    public function test_a_headword_with_one_meaning_named_after_it_is_self_evident()
    {
        $lookup = $this->_finder->lookUp($this->buildSense('zebra', ['noun']));

        $this->assertNotNull($this->_finder->selfEvident($lookup));
    }

    public function test_a_headword_with_several_meanings_needs_judgement()
    {
        $lookup = $this->_finder->lookUp($this->buildSense('oak', ['noun']));

        $this->assertGreaterThan(1, $lookup->synsetIds->count());
        $this->assertNull($this->_finder->selfEvident($lookup));
    }

    public function test_an_only_meaning_named_after_another_word_needs_confirmation()
    {
        // WordNet's only "fair-haired" is "blue-eyed": favourite
        $lookup = $this->_finder->lookUp($this->buildSense('fair-haired', ['adjective']));

        $this->assertCount(1, $lookup->synsetIds);
        $this->assertNull($this->_finder->selfEvident($lookup));
    }

    public function test_one_particular_thing_is_never_self_evident()
    {
        // WordNet's only "Chinese Wall" is the one in China
        $lookup = $this->_finder->lookUp($this->buildSense('chinese wall', ['noun']));

        $this->assertCount(1, $lookup->synsetIds);
        $this->assertNull($this->_finder->selfEvident($lookup));
    }

    public function test_a_word_without_a_part_of_speech_can_be_a_verb()
    {
        $lookup = $this->_finder->lookUp($this->buildSense('have'));

        $this->assertNull($this->_finder->selfEvident($lookup));
    }

    public function test_a_phrase_is_looked_up_by_its_head()
    {
        $this->assertSame('mouth', $this->_finder->lookUp($this->buildSense('mouth of a great river', ['noun']))->lookedUp);
        $this->assertSame('clearing', $this->_finder->lookUp($this->buildSense('clearing in forest', ['noun']))->lookedUp);
        $this->assertSame('house', $this->_finder->lookUp($this->buildSense('house by the sea', ['noun']))->lookedUp);
        $this->assertSame('queen', $this->_finder->lookUp($this->buildSense('star-queen', ['noun']))->lookedUp);
        $this->assertSame('building', $this->_finder->lookUp($this->buildSense('great towering building', ['noun']))->lookedUp);
        $this->assertSame('go', $this->_finder->lookUp($this->buildSense('go on eating greedily', ['verb'], isVerb: true))->lookedUp);
        $this->assertTrue($this->_finder->lookUp($this->buildSense('star-queen', ['noun']))->viaPhraseHead);
    }

    public function test_describes_candidates_with_what_they_are_a_kind_of()
    {
        $lookup = $this->_finder->lookUp($this->buildSense('oak', ['noun']));
        $tree = $this->_finder->describe($lookup->synsetIds)->firstWhere('synsetId', '12288763-n');

        $this->assertSame('oak', $tree->label);
        $this->assertContains('oak tree', $tree->synonyms);
        $this->assertSame('tree', $tree->lineage[0]);
    }
}
