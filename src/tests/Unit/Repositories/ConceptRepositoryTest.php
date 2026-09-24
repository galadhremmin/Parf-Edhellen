<?php

namespace Tests\Unit\Repositories;

use App\Models\Concept;
use App\Models\ConceptLabel;
use App\Models\Sense;
use App\Models\SenseConcept;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\ValueObjects\ConceptAssignment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\CanBuildSenses;

class ConceptRepositoryTest extends TestCase
{
    use CanBuildSenses;
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    private const OAK_TREE = '12288763-n';

    private ConceptRepository $_repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireWordNet();
        $this->_repository = resolve(ConceptRepository::class);
    }

    public function test_a_concept_comes_with_its_ancestors_and_the_words_it_goes_by()
    {
        $oak = $this->_repository->forSynset(self::OAK_TREE);

        $this->assertSame('oak', $oak->label);
        $this->assertSame('tree', $oak->parent->label);
        $this->assertSame('entity', $this->root($oak)->label);
        $this->assertEqualsCanonicalizing(['oak', 'oaktree'], ConceptLabel::where('concept_id', $oak->id)->pluck('term_key')->all());
    }

    public function test_assigning_replaces_what_the_same_source_assigned_but_not_what_others_did()
    {
        $sense = Sense::firstOrFail();
        $oak = $this->_repository->forSynset(self::OAK_TREE);
        $tree = $oak->parent;
        $this->_repository->assign($sense->id, ConceptSource::EDITOR, collect([new ConceptAssignment($tree, 0)]));

        $this->_repository->assign($sense->id, ConceptSource::BACKFILL, collect([new ConceptAssignment($tree, 0)]));
        $this->_repository->assign($sense->id, ConceptSource::BACKFILL, collect([new ConceptAssignment($oak, 0, 80)]));

        $assignments = SenseConcept::where('sense_id', $sense->id)->pluck('source', 'concept_id');
        $this->assertEquals([$tree->id => ConceptSource::EDITOR, $oak->id => ConceptSource::BACKFILL], $assignments->all());
    }

    public function test_a_locked_sense_is_left_alone()
    {
        $sense = Sense::firstOrFail();
        $oak = $this->_repository->forSynset(self::OAK_TREE);
        $this->_repository->assign($sense->id, ConceptSource::EDITOR, collect([new ConceptAssignment($oak->parent, 0)]));
        SenseConcept::where('sense_id', $sense->id)->update(['is_locked' => true]);

        $this->assertFalse($this->_repository->assign($sense->id, ConceptSource::BACKFILL, collect([new ConceptAssignment($oak, 0)])));
        $this->assertSame([$oak->parent->id], SenseConcept::where('sense_id', $sense->id)->pluck('concept_id')->all());
    }

    public function test_the_closure_links_every_concept_to_each_ancestor()
    {
        $oak = $this->_repository->forSynset(self::OAK_TREE);

        $this->_repository->rebuildClosure();

        $ancestors = DB::table('concept_closure')->where('descendant_id', $oak->id)->pluck('depth', 'ancestor_id');
        $this->assertSame(0, $ancestors[$oak->id]);
        $this->assertSame(1, $ancestors[$oak->parent_id]);
        $this->assertContains($oak->id, $oak->parent->descendants()->pluck('concepts.id'));
    }

    public function test_offers_the_kinds_of_what_was_searched_for()
    {
        $oak = $this->_repository->forSynset(self::OAK_TREE);
        $tree = $oak->parent;
        $sense = Sense::firstOrFail();
        $this->_repository->assign($sense->id, ConceptSource::EDITOR, collect([new ConceptAssignment($tree, 0)]));
        $this->_repository->rebuildClosure();

        $kinds = $this->_repository->narrowerFor([$sense->id], 12);

        // a kind of tree, with entries to read, and never the tree itself
        $this->assertContains('oak', $kinds->pluck('label'));
        $this->assertNotContains('tree', $kinds->pluck('label'));
        $this->assertGreaterThan(0, $kinds->firstWhere('label', 'oak')->entries);
    }

    public function test_offers_what_the_search_is_itself_a_kind_of()
    {
        $oak = $this->_repository->forSynset(self::OAK_TREE);
        $sense = Sense::firstOrFail();
        $this->_repository->assign($sense->id, ConceptSource::EDITOR, collect([new ConceptAssignment($oak, 0)]));
        $this->_repository->rebuildClosure();

        $broader = $this->_repository->broaderFor([$sense->id], 4);

        // nearest first, and stopping before the abstractions an oak also belongs to
        $this->assertSame('tree', $broader->first()->label);
        $this->assertNotContains('entity', $broader->pluck('label'));
    }

    public function test_offers_nothing_for_a_sense_with_no_concept()
    {
        $this->assertEmpty($this->_repository->narrowerFor([Sense::firstOrFail()->id], 12));
    }

    private function root(Concept $concept): Concept
    {
        while ($concept->parent !== null) {
            $concept = $concept->parent;
        }

        return $concept;
    }
}
