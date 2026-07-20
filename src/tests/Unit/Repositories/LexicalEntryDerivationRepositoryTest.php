<?php

namespace Tests\Unit\Repositories;

use App\Models\LexicalEntryDerivation;
use App\Models\LexicalEntryPhoneticDevelopment;
use App\Repositories\LexicalEntryDerivationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Tests\Unit\Traits\CanCreateGloss;

class LexicalEntryDerivationRepositoryTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpLexicalEntries;
    }
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    private LexicalEntryDerivationRepository $_repository;

    protected function setUp(): void
    {
        $this->setUpLexicalEntries();
        $this->_repository = resolve(LexicalEntryDerivationRepository::class);
    }

    public function test_save_resolve_and_retrieve_derivations()
    {
        // Emulate S. crist < ✶kirissē < √KIRIS with two competing hypotheses.
        $root = $this->saveEntry(__FUNCTION__.'-root', 'KIRIS');
        $child = $this->saveEntry(__FUNCTION__.'-child', 'crist');

        $primaryUuid = (string) Uuid::uuid4();
        $secondaryUuid = (string) Uuid::uuid4();

        $this->_repository->saveManyOnLexicalEntry($child, collect([
            new LexicalEntryDerivation([
                'derivation_group_uuid' => $primaryUuid,
                'order' => 0,
                'parent_external_id' => $root->external_id,
                'parent_form' => 'KIRIS',
                'source' => 'SA/kir',
                'intermediate_stages' => ['kirissē'],
            ]),
            new LexicalEntryDerivation([
                'derivation_group_uuid' => $secondaryUuid,
                'order' => 0,
                'parent_external_id' => 'UA-Unit-does-not-exist',
                'parent_form' => 'ris',
                'is_uncertain' => true,
            ]),
        ]), collect([
            new LexicalEntryPhoneticDevelopment([
                'derivation_group_uuid' => $primaryUuid,
                'order' => 0,
                'word' => 'kirisse',
            ]),
            new LexicalEntryPhoneticDevelopment([
                'derivation_group_uuid' => $primaryUuid,
                'order' => 1,
                'word' => 'krist',
                'rule' => 'iri-ri',
                'previous_word' => 'kirisse',
            ]),
        ]));

        $derivations = $this->_repository->getDerivationsForLexicalEntry($child->id);
        $this->assertEquals(2, $derivations->count());
        $this->assertEquals(['kirissē'], $derivations[$primaryUuid]->first()->intermediate_stages);
        $this->assertTrue((bool) $derivations[$secondaryUuid]->first()->is_uncertain);

        $developments = $this->_repository->getPhoneticDevelopmentsForLexicalEntry($child->id);
        $this->assertEquals(1, $developments->count());
        $this->assertEquals(['kirisse', 'krist'], $developments[$primaryUuid]->pluck('word')->toArray());

        // Second import pass: the resolvable parent is linked, the unresolvable one is left alone.
        $resolved = $this->_repository->resolveParentReferences([$this->_lexicalEntryGroup->id]);
        $this->assertEquals(1, $resolved);

        $derivations = $this->_repository->getDerivationsForLexicalEntry($child->id);
        $this->assertEquals($root->id, $derivations[$primaryUuid]->first()->parent_lexical_entry_id);
        $this->assertNull($derivations[$secondaryUuid]->first()->parent_lexical_entry_id);

        $descendants = $this->_repository->getDerivationsFromLexicalEntry($root->id);
        $this->assertEquals([$child->id], $descendants->pluck('lexical_entry_id')->toArray());

        // Re-importing with no derivations must clear the previous rows.
        $this->_repository->saveManyOnLexicalEntry($child, collect([]), collect([]));
        $this->assertEquals(0, $this->_repository->getDerivationsForLexicalEntry($child->id)->count());
        $this->assertEquals(0, $this->_repository->getPhoneticDevelopmentsForLexicalEntry($child->id)->count());
    }

    public function test_save_with_no_derivations_and_none_existing_is_a_noop()
    {
        $entry = $this->saveEntry(__FUNCTION__, 'tuil');

        DB::enableQueryLog();
        $this->_repository->saveManyOnLexicalEntry($entry, collect([]), collect([]));
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertEquals(0, $this->_repository->getDerivationsForLexicalEntry($entry->id)->count());
        $this->assertNoWriteQueries($queries);
    }

    public function test_resaving_identical_content_under_a_new_uuid_is_a_noop()
    {
        // Re-imports generate a fresh derivation_group_uuid every run even when the
        // underlying Eldamo data hasn't changed, so the delta check must compare content.
        $root = $this->saveEntry(__FUNCTION__.'-root', 'ZIR');
        $child = $this->saveEntry(__FUNCTION__.'-child', 'izre');

        $makeRows = fn (string $uuid) => [
            collect([
                new LexicalEntryDerivation([
                    'derivation_group_uuid' => $uuid,
                    'order' => 0,
                    'parent_external_id' => $root->external_id,
                    'parent_form' => 'ZIR',
                    'source' => 'SA/zir',
                ]),
            ]),
            collect([
                new LexicalEntryPhoneticDevelopment([
                    'derivation_group_uuid' => $uuid,
                    'order' => 0,
                    'word' => 'izre',
                ]),
            ]),
        ];

        [$derivations, $developments] = $makeRows((string) Uuid::uuid4());
        $this->_repository->saveManyOnLexicalEntry($child, $derivations, $developments);

        [$derivations, $developments] = $makeRows((string) Uuid::uuid4());
        DB::enableQueryLog();
        $this->_repository->saveManyOnLexicalEntry($child, $derivations, $developments);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertNoWriteQueries($queries);

        // A genuine content change (new source) must still trigger a write.
        [$derivations, $developments] = $makeRows((string) Uuid::uuid4());
        $derivations[0]->source = 'SA/zir-revised';
        $this->_repository->saveManyOnLexicalEntry($child, $derivations, $developments);

        $saved = $this->_repository->getDerivationsForLexicalEntry($child->id)->first()->first();
        $this->assertEquals('SA/zir-revised', $saved->source);
    }

    private function assertNoWriteQueries(array $queries): void
    {
        foreach ($queries as $query) {
            $this->assertMatchesRegularExpression('/^select/i', $query['query']);
        }
    }

    private function saveEntry(string $method, string $word)
    {
        extract($this->createLexicalEntry($method, $word));

        return $this->getRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
    }
}
