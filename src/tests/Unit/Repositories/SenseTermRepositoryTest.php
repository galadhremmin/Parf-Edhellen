<?php

namespace Tests\Unit\Repositories;

use App\Events\SenseEdited;
use App\Jobs\ProcessSenseConceptResolution;
use App\Jobs\ProcessSenseNormalization;
use App\Models\Sense;
use App\Models\SenseTerm;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\SearchIndexRepository;
use App\Repositories\SenseTermRepository;
use App\Repositories\ValueObjects\ConceptAssignment;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Unit\Traits\CanCreateGloss;
use Tests\Unit\Traits\CanCreateSenses;

class SenseTermRepositoryTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpLexicalEntries;
    }
    use CanCreateSenses;
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    private SenseTermRepository $_repository;

    protected function setUp(): void
    {
        $this->setUpLexicalEntries();
        $this->_repository = resolve(SenseTermRepository::class);
    }

    public function test_rebuilds_the_terms_of_a_sense()
    {
        $name = 'grelk'.uniqid();
        $entry = $this->createEntry("(tall) {$name}-tree, {$name}wood", 'noun');

        $this->_repository->rebuildSense($entry->sense_id);

        $terms = SenseTerm::where('sense_id', $entry->sense_id)->orderBy('position')->get();
        $this->assertSame(["{$name}tree", "{$name}wood"], $terms->pluck('term_key')->all());
        $this->assertSame("(tall) {$name}-tree", $terms[0]->term);
    }

    public function test_a_sense_used_only_by_verbs_is_keyed_as_a_verb()
    {
        $entry = $this->createEntry('grelk'.uniqid(), 'verb');

        $this->_repository->rebuildSense($entry->sense_id);

        $this->assertStringStartsWith('to:', SenseTerm::where('sense_id', $entry->sense_id)->value('term_key'));
    }

    public function test_removes_the_terms_of_a_sense_no_longer_in_use()
    {
        $entry = $this->createEntry('grelk'.uniqid(), 'noun');
        $this->_repository->rebuildSense($entry->sense_id);

        $entry->is_deleted = true;
        $entry->save();
        $this->_repository->rebuildSense($entry->sense_id);

        $this->assertFalse(SenseTerm::where('sense_id', $entry->sense_id)->exists());
    }

    public function test_search_finds_an_entry_by_its_headword()
    {
        $name = 'grelk'.uniqid();
        $entry = $this->createEntry("{$name}-tree", 'noun');
        $this->_repository->rebuildSense($entry->sense_id);

        // fulltext can't join the halves of a hyphenated compound
        $this->assertContains($entry->id, $this->search("{$name}tree"));
        $this->assertContains($entry->id, $this->search("{$name} tree"));

        config(['ed-senses.term_search' => false]);
        $this->assertNotContains($entry->id, $this->search("{$name}tree"));
    }

    public function test_search_without_to_finds_verbs()
    {
        $name = 'grelk'.uniqid();
        $entry = $this->createEntry("to {$name}", 'verb');
        $this->_repository->rebuildSense($entry->sense_id);

        $this->assertContains($entry->id, $this->search($name));
    }

    public function test_suggests_the_headword_a_search_normalises_to()
    {
        $name = 'grelk'.uniqid();
        $entry = $this->createEntry("{$name}-tree", 'noun');
        $this->_repository->rebuildSense($entry->sense_id);

        $keywords = resolve(SearchIndexRepository::class)->findKeywords($this->searchValue("{$name} tree"));

        $this->assertSame(['g' => 1, 'k' => "{$name}-tree", 'nk' => "{$name}-tree", 'ok' => "{$name}-tree"], $keywords[0]);
    }

    public function test_a_search_finds_the_kinds_of_what_was_asked_for()
    {
        $name = 'grelk'.uniqid();
        $tree = $this->createEntry("{$name}", 'noun');
        $oak = $this->createEntry("{$name} oak", 'noun');
        $this->_repository->rebuildSense($tree->sense_id);
        $this->_repository->rebuildSense($oak->sense_id);

        $concepts = resolve(ConceptRepository::class);
        $broad = $concepts->forSynset('13124818-n');
        $narrow = $concepts->forSynset('12288763-n');
        $concepts->assign($tree->sense_id, ConceptSource::EDITOR, collect([new ConceptAssignment($broad, 0)]));
        $concepts->assign($oak->sense_id, ConceptSource::EDITOR, collect([new ConceptAssignment($narrow, 0)]));
        $concepts->rebuildClosure();

        // the oak entry has no word in common with the search, only a meaning below it
        $this->assertContains($oak->id, $this->search($name));
    }

    public function test_a_search_for_a_concepts_own_name_finds_what_means_it()
    {
        // WordNet's name for a meaning is often no word of the dictionary: nothing here is glossed "bungalow"
        $entry = $this->createEntry('cottage, hut '.uniqid(), 'noun');
        $this->_repository->rebuildSense($entry->sense_id);
        $concepts = resolve(ConceptRepository::class);
        $bungalow = $concepts->forSynset('02923176-n');
        $concepts->assign($entry->sense_id, ConceptSource::EDITOR, collect([new ConceptAssignment($bungalow, 0)]));
        $concepts->rebuildClosure();

        $this->assertContains($entry->id, $this->search('bungalow'));
    }

    public function test_an_edited_sense_is_queued_for_normalisation()
    {
        Queue::fake();
        $sense = Sense::first();

        event(new SenseEdited($sense));

        Queue::assertPushedOn('indexing', ProcessSenseNormalization::class);
        // the concept is resolved once the terms it reads exist
        Queue::assertPushedWithChain(ProcessSenseNormalization::class, [ProcessSenseConceptResolution::class]);
    }

    /**
     * @return int[] the IDs of the entries a dictionary search finds
     */
    private function search(string $word): array
    {
        $result = resolve(SearchIndexRepository::class)->resolveIndexToEntities(1, $this->searchValue($word));

        return collect($result['entities']['sections'] ?? [])
            ->flatMap(fn ($section) => collect($section['entities'])->pluck('id'))
            ->all();
    }

    private function searchValue(string $word): SearchIndexSearchValue
    {
        return new SearchIndexSearchValue(['word' => $word, 'include_old' => true]);
    }
}
