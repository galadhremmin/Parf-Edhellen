<?php

namespace Tests\Unit\Services\Senses;

use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\SenseConceptReview;
use App\Models\SenseTerm;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Services\Senses\SenseReviewQueue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Traits\CanBuildSenses;

class SenseReviewQueueTest extends TestCase
{
    use CanBuildSenses;
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    private SenseReviewQueue $_queue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireWordNet();
        $this->_queue = resolve(SenseReviewQueue::class);

        // the dictionary's own queue is thousands long: empty it for the duration of the transaction, so what these
        // tests put in it is all there is
        SenseConceptReview::query()->delete();
    }

    public function test_the_most_used_sense_comes_first()
    {
        [$busiest, $quietest] = $this->sensesByUse();
        $this->waitFor($quietest, ConceptReviewReason::UNSURE);
        $this->waitFor($busiest, ConceptReviewReason::UNSURE);

        $item = $this->_queue->next();

        $this->assertSame($busiest->id, $item->senseId);
        $this->assertSame(2, $this->_queue->count());
    }

    public function test_a_skipped_sense_is_passed_over()
    {
        [$busiest, $quietest] = $this->sensesByUse();
        $this->waitFor($busiest, ConceptReviewReason::UNSURE);
        $this->waitFor($quietest, ConceptReviewReason::UNSURE);

        $item = $this->_queue->next([$busiest->id]);

        $this->assertSame($quietest->id, $item->senseId);
    }

    public function test_the_queue_can_be_narrowed_to_one_reason()
    {
        [$busiest, $quietest] = $this->sensesByUse();
        $this->waitFor($busiest, ConceptReviewReason::UNSURE);
        $this->waitFor($quietest, ConceptReviewReason::UNKNOWN_WORD);

        $item = $this->_queue->next([], ConceptReviewReason::UNKNOWN_WORD);

        $this->assertSame($quietest->id, $item->senseId);
        $this->assertSame(1, $this->_queue->count(ConceptReviewReason::UNKNOWN_WORD));
        $this->assertEquals(['unsure' => 1, 'unknown_word' => 1], $this->_queue->summary()->all());
    }

    public function test_a_waiting_sense_arrives_with_the_evidence_and_why_it_waits()
    {
        [$busiest] = $this->sensesByUse();
        $this->waitFor($busiest, ConceptReviewReason::UNSURE, 'a guess', 55);

        $item = $this->_queue->item($busiest->id);

        $this->assertSame($busiest->word->word, $item->sense);
        $this->assertSame(ConceptReviewReason::UNSURE, $item->reason);
        $this->assertSame('a guess', $item->detail);
        $this->assertSame(55, $item->confidence);
        $this->assertGreaterThan(0, $item->entries);
        $this->assertNotEmpty($item->request->senses);
    }

    public function test_the_entries_glossed_with_it_link_into_the_dictionary()
    {
        [$busiest] = $this->sensesByUse();
        $this->waitFor($busiest, ConceptReviewReason::UNSURE);

        $usages = $this->_queue->item($busiest->id)->usages;

        $this->assertNotEmpty($usages);
        $usage = $usages->first();
        $this->assertSame(route('gloss.ref', ['id' => $usage->lexicalEntryId]), $usage->url);
        $this->assertNotEmpty($usage->word);
        $this->assertNotEmpty($usage->language);
    }

    public function test_a_sense_nobody_waits_on_is_not_in_the_queue()
    {
        [$busiest] = $this->sensesByUse();

        $this->assertNull($this->_queue->item($busiest->id));
        $this->assertNull($this->_queue->next());
    }

    /**
     * Two normalised senses in use, the most glossed with first and a singleton last, so ordering can be asserted
     * against real entry counts rather than invented ones.
     *
     * @return Sense[]
     */
    private function sensesByUse(): array
    {
        $counted = LexicalEntry::active()
            ->selectRaw('sense_id, COUNT(*) AS entries')
            ->whereIn('sense_id', SenseTerm::select('sense_id'))
            ->groupBy('sense_id')
            ->orderByDesc('entries')
            ->limit(1)
            ->union(LexicalEntry::active()
                ->selectRaw('sense_id, COUNT(*) AS entries')
                ->whereIn('sense_id', SenseTerm::select('sense_id'))
                ->groupBy('sense_id')
                ->havingRaw('COUNT(*) = 1')
                ->limit(1))
            ->get();

        $senses = Sense::with('word')->whereIn('id', $counted->pluck('sense_id'))->get()
            ->sortByDesc(fn (Sense $sense) => $counted->firstWhere('sense_id', $sense->id)->entries)
            ->values();

        if ($senses->count() < 2) {
            $this->markTestSkipped('The dictionary holds too few senses in use to order a queue by.');
        }

        return [$senses->first(), $senses->last()];
    }

    private function waitFor(Sense $sense, ConceptReviewReason $reason, ?string $detail = null,
        ?int $confidence = null): void
    {
        resolve(ConceptRepository::class)->review($sense->id, $reason, $detail, $confidence);
    }
}
