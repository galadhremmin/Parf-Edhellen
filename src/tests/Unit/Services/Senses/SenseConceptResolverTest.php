<?php

namespace Tests\Unit\Services\Senses;

use App\Interfaces\IJudgesSenseConcepts;
use App\Models\Concept;
use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\SenseConcept;
use App\Models\SenseConceptReview;
use App\Repositories\ConceptAuditRepository;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\SenseTermRepository;
use App\Repositories\ValueObjects\ConceptAssignment;
use App\Services\Enumerations\ConceptOutcome;
use App\Services\Senses\SenseConceptResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Traits\CanBuildSenses;
use Tests\Unit\Traits\CanCreateGloss;
use Tests\Unit\Traits\CanCreateSenses;

class SenseConceptResolverTest extends TestCase
{
    use CanBuildSenses;
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpLexicalEntries;
    }
    use CanCreateSenses;
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    // a headword with one WordNet meaning, and no sense of its own in the dictionary
    private const AARDVARK = '02085443-n';

    private const TREE = '13124818-n';

    private FakeJudge $_judge;

    protected function setUp(): void
    {
        $this->setUpLexicalEntries();
        $this->requireWordNet();

        $this->_judge = new FakeJudge;
        $this->app->instance(IJudgesSenseConcepts::class, $this->_judge);
    }

    public function test_a_name_is_placed_without_asking_a_judge()
    {
        $sense = $this->sense('gildir'.uniqid(), 'masculine name');

        $this->assertSame(ConceptOutcome::ASSIGNED_BY_RULE, $this->resolve($sense));
        $this->assertSame('first name', $this->conceptOf($sense)->label);
        $this->assertTrue($this->_judge->asked->isEmpty());
    }

    public function test_a_grammatical_label_is_not_a_concept()
    {
        $sense = $this->sense('pronominal suffix '.uniqid(), 'suffix');

        $this->assertSame(ConceptOutcome::NOT_A_CONCEPT, $this->resolve($sense));
        $this->assertNull($this->conceptOf($sense));
        $this->assertTrue($this->_judge->asked->isEmpty());
    }

    public function test_a_headword_with_one_meaning_is_placed_without_asking_a_judge()
    {
        $sense = $this->sense('aardvark', 'noun');

        $this->assertSame(ConceptOutcome::ASSIGNED_BY_RULE, $this->resolve($sense));
        $this->assertSame(self::AARDVARK, $this->conceptOf($sense)->synset_id);
        $this->assertTrue($this->_judge->asked->isEmpty());
    }

    public function test_a_sense_inherits_the_concept_its_headword_already_has()
    {
        $name = 'grelk'.uniqid();
        $settled = $this->sense($name, 'noun');
        $tree = resolve(ConceptRepository::class)->forSynset(self::TREE);
        resolve(ConceptRepository::class)->assign($settled->id, ConceptSource::EDITOR, collect([new ConceptAssignment($tree, 0)]));

        // the same headword, spelled differently
        $sense = $this->sense("(tall) {$name}", 'noun');

        $this->assertSame(ConceptOutcome::INHERITED, $this->resolve($sense));
        $this->assertSame($tree->id, $this->conceptOf($sense)->id);
        $this->assertTrue($this->_judge->asked->isEmpty());
    }

    public function test_a_judged_meaning_is_assigned_with_its_confidence()
    {
        // a phrase, so the meanings of "tree" are what the judge is offered
        $sense = $this->sense('grelk'.uniqid().'-tree', 'noun');
        $this->_judge->willAnswer($sense->id, [self::TREE], confidence: 85);

        $this->assertSame(ConceptOutcome::ASSIGNED, $this->resolve($sense));
        $assignment = SenseConcept::where('sense_id', $sense->id)->firstOrFail();
        $this->assertSame(ConceptSource::GEMINI, $assignment->source);
        $this->assertSame(85, $assignment->confidence);
    }

    public function test_a_word_the_sense_is_a_kind_of_becomes_a_concept_of_its_own()
    {
        $name = 'grelk'.uniqid();
        $sense = $this->sense($name, 'noun');
        $this->_judge->willAnswer($sense->id, betterWord: 'tree', relation: 'kind_of');

        $this->assertSame(ConceptOutcome::ASSIGNED, $this->resolve($sense));
        $concept = $this->conceptOf($sense);
        $this->assertSame($name, $concept->label);
        $this->assertNull($concept->synset_id);
        $this->assertSame(self::TREE, $concept->parent->synset_id);
    }

    public function test_a_word_that_means_the_same_uses_that_words_concept()
    {
        $sense = $this->sense('grelk'.uniqid(), 'noun');
        $this->_judge->willAnswer($sense->id, betterWord: 'tree', relation: 'synonym');

        $this->assertSame(ConceptOutcome::ASSIGNED, $this->resolve($sense));
        $this->assertSame(self::TREE, $this->conceptOf($sense)->synset_id);
    }

    public function test_an_unsure_answer_waits_for_an_editor()
    {
        $sense = $this->sense('grelk'.uniqid().'-tree', 'noun');
        $this->_judge->willAnswer($sense->id, [self::TREE], confidence: 40);

        $this->assertSame(ConceptOutcome::NEEDS_REVIEW, $this->resolve($sense));
        $this->assertNull($this->conceptOf($sense));
        $review = SenseConceptReview::findOrFail($sense->id);
        $this->assertSame(ConceptReviewReason::UNSURE, $review->reason);
        $this->assertSame(40, $review->confidence);
        $this->assertSame(self::TREE, $review->detail);
    }

    public function test_a_meaning_that_was_never_offered_is_not_trusted()
    {
        // nothing is on offer for a word WordNet has never seen
        $sense = $this->sense('grelk'.uniqid(), 'noun');
        $this->_judge->willAnswer($sense->id, [self::TREE]);

        $this->assertSame(ConceptOutcome::NEEDS_REVIEW, $this->resolve($sense));
        $this->assertNull($this->conceptOf($sense));
        $this->assertSame(ConceptReviewReason::INVALID_ANSWER, SenseConceptReview::findOrFail($sense->id)->reason);
    }

    public function test_a_word_wordnet_does_not_know_either_waits_for_an_editor()
    {
        $sense = $this->sense('grelk'.uniqid(), 'noun');
        $this->_judge->willAnswer($sense->id, betterWord: 'grelkish', relation: 'kind_of');

        $this->assertSame(ConceptOutcome::NEEDS_REVIEW, $this->resolve($sense));
        $this->assertSame(ConceptReviewReason::UNKNOWN_WORD, SenseConceptReview::findOrFail($sense->id)->reason);
    }

    public function test_a_sense_no_judge_could_answer_waits_for_an_editor()
    {
        $sense = $this->sense('grelk'.uniqid(), 'noun');

        $this->assertSame(ConceptOutcome::NEEDS_REVIEW, $this->resolve($sense));
        $this->assertSame(ConceptReviewReason::NOT_JUDGED, SenseConceptReview::findOrFail($sense->id)->reason);
    }

    public function test_every_decision_is_written_to_the_audit_log()
    {
        // a phrase, so WordNet offers the meanings of its head word
        $sense = $this->sense('grelk'.uniqid().'-tree', 'noun');
        $this->_judge->willAnswer($sense->id, [self::TREE], confidence: 85);
        $this->resolve($sense);

        $decision = resolve(ConceptAuditRepository::class)->forSense($sense->id)->first();
        $this->assertSame(ConceptSource::GEMINI, $decision->source);
        $this->assertSame(ConceptOutcome::ASSIGNED, $decision->outcome);
        $this->assertSame('fake judge', $decision->decided_by);
        $this->assertSame([self::TREE], $decision->synset_ids);
        $this->assertSame(85, $decision->confidence);
        $this->assertSame('tree', $decision->concepts[0]['label']);
        $this->assertNotEmpty($decision->candidate_synset_ids);
        $this->assertNotNull($decision->prompt_hash);
    }

    public function test_the_audit_log_records_a_rule_and_the_reason_a_sense_waits()
    {
        $name = $this->sense('gildir'.uniqid(), 'masculine name');
        $waiting = $this->sense('grelk'.uniqid(), 'noun');
        $this->resolve($name);
        $this->resolve($waiting);

        $audit = resolve(ConceptAuditRepository::class);
        $this->assertSame('name', $audit->forSense($name->id)->first()->decided_by);

        $unjudged = $audit->forSense($waiting->id)->first();
        $this->assertSame(ConceptOutcome::NEEDS_REVIEW, $unjudged->outcome);
        $this->assertSame(ConceptReviewReason::NOT_JUDGED, $unjudged->review_reason);
    }

    public function test_the_audit_log_outlives_the_sense_it_is_about()
    {
        $sense = $this->sense('grelk'.uniqid(), 'noun');
        $this->resolve($sense);

        // a sense cannot go while an entry still uses it
        $entry = LexicalEntry::where('sense_id', $sense->id)->firstOrFail();
        $entry->glosses()->delete();
        $entry->delete();
        Sense::whereKey($sense->id)->delete();

        $this->assertNotEmpty(resolve(ConceptAuditRepository::class)->forSense($sense->id));
    }

    public function test_a_pass_that_may_not_ask_leaves_the_sense_for_a_judge()
    {
        $sense = $this->sense('grelk'.uniqid(), 'noun');
        $this->_judge->willAnswer($sense->id, [self::TREE]);

        $outcome = resolve(SenseConceptResolver::class)->resolve($this->load($sense->id), mayAsk: false);

        $this->assertSame(ConceptOutcome::NEEDS_REVIEW, $outcome);
        $this->assertTrue($this->_judge->asked->isEmpty());
        $this->assertSame('left to be judged', resolve(ConceptAuditRepository::class)->forSense($sense->id)->first()->decided_by);
    }

    public function test_a_sense_that_already_has_a_concept_is_left_alone()
    {
        $sense = $this->sense('aardvark', 'noun');
        $this->resolve($sense);

        $this->assertSame(ConceptOutcome::ALREADY_ASSIGNED, $this->resolve($sense));
    }

    /**
     * Saves an entry with this sense, and loads the sense the way the resolver expects it.
     */
    private function sense(string $sense, string $speech): Sense
    {
        $entry = $this->createEntry($sense, $speech);
        resolve(SenseTermRepository::class)->rebuildSense($entry->sense_id);

        return $this->load($entry->sense_id);
    }

    private function resolve(Sense $sense): ConceptOutcome
    {
        return resolve(SenseConceptResolver::class)->resolve($this->load($sense->id));
    }

    private function load(int $senseId): Sense
    {
        return resolve(SenseTermRepository::class)->normalizable()->with('terms')->whereKey($senseId)->firstOrFail();
    }

    private function conceptOf(Sense $sense): ?Concept
    {
        return Concept::whereIn('id', SenseConcept::where('sense_id', $sense->id)->pluck('concept_id'))->first();
    }
}
