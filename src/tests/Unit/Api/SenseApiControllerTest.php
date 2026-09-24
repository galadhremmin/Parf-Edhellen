<?php

namespace Tests\Unit\Api;

use App\Models\Concept;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Traits\CanBuildSenses;

class SenseApiControllerTest extends TestCase
{
    use CanBuildSenses;
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireWordNet();

        if (! Concept::exists()) {
            $this->markTestSkipped('No concepts: run `php artisan ed-senses:assign-by-rule`.');
        }
    }

    public function test_offers_meanings_and_the_wordings_in_use()
    {
        $response = $this->getJson('/api/v3/sense/find?q=tree');

        $response->assertOk();
        $response->assertJsonStructure([
            'concepts' => [['id', 'label', 'definition', 'synonyms', 'lineage', 'entries']],
            'senses' => [['senseId', 'sense', 'entries']],
        ]);
        $this->assertContains('tree', collect($response->json('concepts'))->pluck('label'));
    }

    public function test_a_query_matching_nothing_answers_with_nothing()
    {
        $response = $this->getJson('/api/v3/sense/find?q=grelkwordthatisnowhere');

        $response->assertOk();
        $this->assertSame([], $response->json('concepts'));
        $this->assertSame([], $response->json('senses'));
    }

    public function test_asking_nothing_answers_with_nothing()
    {
        $response = $this->getJson('/api/v3/sense/find');

        $response->assertOk();
        $this->assertSame([], $response->json('concepts'));
        $this->assertSame([], $response->json('senses'));
    }

    public function test_a_meaning_narrows_the_wordings_to_the_ones_that_mean_it()
    {
        $conceptId = collect($this->getJson('/api/v3/sense/find?q=tree')->json('concepts'))
            ->firstWhere('label', 'tree')['id'];

        $response = $this->getJson('/api/v3/sense/find?concept_id='.$conceptId);

        $response->assertOk();
        // the meaning is settled, so only wordings are on offer
        $this->assertSame([], $response->json('concepts'));
        $this->assertContains('tree', collect($response->json('senses'))->pluck('sense'));
    }

    public function test_an_unknown_meaning_is_refused()
    {
        $this->getJson('/api/v3/sense/find?concept_id=0')->assertStatus(422);
    }
}
