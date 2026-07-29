<?php

namespace Tests\Unit\Repositories;

use App\Jobs\RebuildLexicalEntryDerivationData;
use App\Models\Gloss;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryDerivation;
use App\Models\LexicalEntryDetail;
use App\Models\LexicalEntryPhoneticDevelopment;
use App\Repositories\Enumerations\LexicalEntryChange;
use App\Repositories\LexicalEntryDerivationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Tests\Unit\Traits\CanCreateGloss;

class LexicalEntryRepositoryTest extends TestCase
{
    use CanCreateGloss {
        CanCreateGloss::setUp as setUpLexicalEntries;
        CanCreateGloss::getRepository as getLexicalEntryRepository;
    }
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    /**
     * A basic example of versioning when saving glosses.
     *
     * @return void
     */
    public function test_save_gloss()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'testword'));

        // Create an origin gloss, to validate the versioning system. By appending 'origin' to the word string,
        // the next gloss saved (with an unsuffixed word) create a new version of the gloss.
        $existingLexicalEntry = $this->getLexicalEntryRepository()->saveLexicalEntry($word.' origin', $sense, $lexicalEntry, $glosses, $keywords, $details);
        // Create a new gloss, derived from the origin gloss.
        $newLexicalEntry = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $savedLexicalEntry = LexicalEntry::findOrFail($newLexicalEntry->id);
        $existingLexicalEntry->refresh();

        $this->assertEquals($existingLexicalEntry->id, $newLexicalEntry->id);
        $this->assertEquals($lexicalEntry->id, $newLexicalEntry->id);

        // Look for two versions of the gloss
        $this->assertEquals(2, $newLexicalEntry->lexical_entry_versions()->count());

        $this->assertEquals($lexicalEntry->language_id, $savedLexicalEntry->language_id);
        $this->assertEquals($lexicalEntry->lexical_entry_group_id, $savedLexicalEntry->lexical_entry_group_id);
        $this->assertEquals($lexicalEntry->is_uncertain, $savedLexicalEntry->is_uncertain);
        $this->assertEquals($lexicalEntry->source, $savedLexicalEntry->source);
        $this->assertEquals($lexicalEntry->comments, $savedLexicalEntry->comments);
        $this->assertEquals($lexicalEntry->tengwar, $savedLexicalEntry->tengwar);
        $this->assertEquals($lexicalEntry->speech_id, $savedLexicalEntry->speech_id);
        $this->assertEquals($lexicalEntry->external_id, $savedLexicalEntry->external_id);

        $this->assertEquals($savedLexicalEntry->glosses->count(), count($glosses));
        $this->assertTrue(
            $savedLexicalEntry->glosses->every(function ($g) use ($glosses) {
                return ! empty(array_filter($glosses, function ($g0) use ($g) {
                    return $g->translation === $g0->translation;
                }));
            })
        );

        $actual = $savedLexicalEntry->keywords->map(function ($k) {
            return $k->keyword;
        })->toArray();
        $expected = array_unique(
            array_merge([$word, $savedLexicalEntry->sense->word->word], $keywords, array_map(function ($g) {
                return $g->translation;
            }, $glosses))
        );

        sort($actual);
        sort($expected);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(count($expected), $existingLexicalEntry->keywords()->count());

        $actual = $savedLexicalEntry->sense->keywords
            ->map(function ($k) {
                return $k->keyword;
            })->toArray();
        $expected = $savedLexicalEntry->keywords->merge($existingLexicalEntry->keywords)->map(function ($f) {
            return $f->keyword;
        })->toArray();

        sort($actual);
        sort($expected);

        $this->assertEquals($expected, $actual);
    }

    public function test_should_not_save()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'testword'));

        $changed = 0;
        $gloss0 = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details, $changed);
        $this->assertEquals(LexicalEntryChange::NEW->value, $changed);

        $changed = false;
        $gloss1 = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details, $changed);
        $this->assertEquals(LexicalEntryChange::NO_CHANGE->value, $changed);

        $this->assertEquals($gloss0->id, $gloss1->id);
    }

    public function test_should_delete()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'testword'));

        $savedLexicalEntry = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
        $this->assertEquals($lexicalEntry->id, $savedLexicalEntry->id);

        $this->getLexicalEntryRepository()->deleteLexicalEntryWithId($lexicalEntry->id);

        // resynchronize the model with the database
        $lexicalEntry->refresh();

        $this->assertEquals(1, $lexicalEntry->is_deleted);
        $this->assertEquals(0, $lexicalEntry->keywords()->count());
        $this->assertEquals(0, $lexicalEntry->sense->keywords()->count());
    }

    public function test_should_get_versions()
    {
        extract($this->createLexicalEntry(__FUNCTION__, 'testword'));
        $gloss0 = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $lexicalEntry->is_uncertain = false;
        $glosses = $this->createGlosses();
        $details = $this->createLexicalEntryDetails($lexicalEntry);
        $gloss1 = $this->getLexicalEntryRepository()->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $newWord = $word.' 1';
        $glosses = $this->createGlosses();
        $details = $this->createLexicalEntryDetails($lexicalEntry);
        $gloss2 = $this->getLexicalEntryRepository()->saveLexicalEntry($newWord, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $newTranslation = uniqid();
        $glosses = array_merge(
            $this->createGlosses(),
            [new Gloss(['translation' => $newTranslation])]
        );
        $details = $this->createLexicalEntryDetails($lexicalEntry);
        $gloss3 = $this->getLexicalEntryRepository()->saveLexicalEntry($newWord, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $this->assertEquals($gloss0->id, $gloss1->id);
        $this->assertEquals($gloss0->id, $gloss2->id);
        $this->assertEquals($gloss0->id, $gloss3->id);

        $versions = $this->getLexicalEntryRepository()->getLexicalEntryVersions($gloss0->id);

        $this->assertEquals($versions->getLatestVersionId(), $versions->getVersions()->first()->id);
        $this->assertEquals($versions->getLatestVersionId(), $gloss3->latest_lexical_entry_version_id);

        $gloss3->refresh();
        $gloss3Translations = $gloss3->glosses->map(function ($g) {
            return $g->translation;
        });
        $actualTranslations = $versions->getVersions()->first()->glosses->map(function ($g) {
            return $g->translation;
        });
        $this->assertEquals($gloss3Translations, $actualTranslations);
        $this->assertTrue($gloss3Translations->contains($newTranslation));
    }

    public function test_detects_lexical_entry_metadata_changes()
    {
        $r = $this->getLexicalEntryRepository();

        extract($this->createLexicalEntry(__FUNCTION__, 'testword'));
        $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
        $lexicalEntry->refresh();

        $changed = 0;

        $lexicalEntry->source = uniqid();
        $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details, $changed);
        $versions = $r->getLexicalEntryVersions($lexicalEntry->id);

        $this->assertTrue((bool) $changed);
        $this->assertEquals(LexicalEntryChange::METADATA->value, $changed);
        $this->assertEquals(2, $versions->getVersions()->count());
    }

    public function test_detects_lexical_entry_details_changes()
    {
        $r = $this->getLexicalEntryRepository();

        extract($this->createLexicalEntry(__FUNCTION__, 'testword'));
        $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
        $lexicalEntry->refresh();

        $changed = 0;

        $newDetail = new LexicalEntryDetail([
            'category' => 'Category '.uniqid(),
            'text' => 'Text '.uniqid(),
            'order' => 100,
        ]);
        $details[] = $newDetail;
        $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details, $changed);
        $versions = $r->getLexicalEntryVersions($lexicalEntry->id);

        $this->assertTrue((bool) $changed);
        $this->assertEquals(LexicalEntryChange::DETAILS->value, $changed);
        $this->assertEquals(1, $lexicalEntry->lexical_entry_details->filter(function ($d) use ($newDetail) {
            return $d->text === $newDetail->text && $d->category === $newDetail->category;
        })->count());
        $this->assertEquals(2, $versions->getVersions()->count());
    }

    public function test_detects_lexical_entry_glosses_changes()
    {
        $r = $this->getLexicalEntryRepository();

        extract($this->createLexicalEntry(__FUNCTION__, 'testword'));
        $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
        $lexicalEntry->refresh();

        $changed = 0;

        $newGloss = new Gloss([
            'translation' => 'Gloss '.uniqid(),
        ]);
        $glosses[] = $newGloss;
        $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details, $changed);
        $versions = $r->getLexicalEntryVersions($lexicalEntry->id);

        $this->assertTrue((bool) $changed);
        $this->assertEquals(LexicalEntryChange::GLOSSES->value | LexicalEntryChange::KEYWORDS->value, $changed);
        $this->assertEquals(1, $lexicalEntry->glosses->filter(function ($g) use ($newGloss) {
            return $g->translation === $newGloss->translation;
        })->count());
        $this->assertEquals(2, $versions->getVersions()->count());
    }

    public function test_detects_lexical_entry_keyword_changes()
    {
        $r = $this->getLexicalEntryRepository();

        extract($this->createLexicalEntry(__FUNCTION__, 'testword'));
        $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);
        $lexicalEntry->refresh();

        $changed = 0;

        $newKeyword = 'new keyword '.uniqid();
        $keywords[] = $newKeyword;
        $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details, $changed);
        $versions = $r->getLexicalEntryVersions($lexicalEntry->id);

        $this->assertTrue((bool) $changed);
        $this->assertEquals(LexicalEntryChange::KEYWORDS->value, $changed);
        $this->assertTrue($lexicalEntry->keywords->contains(function ($k) use ($newKeyword) {
            return $k->keyword === $newKeyword;
        }));
        $this->assertEquals(2, $versions->getVersions()->count());
    }

    public function test_navigation_properties_for_lexical_entry()
    {
        $r = $this->getLexicalEntryRepository();

        extract($this->createLexicalEntry(__FUNCTION__, 'testword'));
        $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $this->assertNotNull($lexicalEntry->account);
        $this->assertNotNull($lexicalEntry->language);
        $this->assertNotNull($lexicalEntry->lexical_entry_group);
        $this->assertNotNull($lexicalEntry->speech);

        $this->assertEquals(count($glosses), $lexicalEntry->glosses->count());
        $this->assertEquals(count($details), $lexicalEntry->lexical_entry_details->count());
    }

    /**
     * getLexicalEntries() (and the other callers of createLexicalEntryQuery()/
     * getLexicalEntriesWithDetails()) return raw stdClass rows from a query-builder query, not
     * Eloquent LexicalEntry instances — this is the path most dictionary views (search results,
     * word lookups) actually go through. Derivations/phonetic developments must be attached onto
     * those rows the same way lexical_entry_details already is.
     */
    public function test_get_lexical_entries_attaches_derivations_and_phonetic_developments()
    {
        $r = $this->getLexicalEntryRepository();

        extract($this->createLexicalEntry(__FUNCTION__.'-root', 'KIRIS'));
        $root = $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);

        extract($this->createLexicalEntry(__FUNCTION__.'-child', 'crist'));
        $child = $r->saveLexicalEntry($word, $sense, $lexicalEntry, $glosses, $keywords, $details);

        $uuid = (string) Uuid::uuid4();
        $derivationRepository = resolve(LexicalEntryDerivationRepository::class);
        $derivationRepository->saveManyOnLexicalEntry($child, collect([
            new LexicalEntryDerivation([
                'derivation_group_uuid' => $uuid,
                'order' => 0,
                'parent_external_id' => $root->external_id,
                'parent_form' => 'KIRIS',
            ]),
        ]), collect([
            new LexicalEntryPhoneticDevelopment([
                'derivation_group_uuid' => $uuid,
                'order' => 0,
                'word' => 'crist',
            ]),
        ]));
        $derivationRepository->resolveParentReferences();

        // Precomputed by RebuildLexicalEntryDerivationData — nothing is computed live anymore.
        app()->call([new RebuildLexicalEntryDerivationData, 'handle']);

        $rows = $r->getLexicalEntries([$child->id]);

        // One row per gloss (the query joins glosses without aggregating) — all sharing the
        // same lexical_entry_id, so each carries the same attached precomputed data.
        $this->assertCount(count($glosses), $rows);
        $this->assertEquals(\stdClass::class, get_class($rows[0]));
        $this->assertNotNull($rows[0]->precomputed_derivation_data);
        $this->assertCount(1, $rows[0]->precomputed_derivation_data->derivations);
        $this->assertEquals($root->id, $rows[0]->precomputed_derivation_data->derivations[0][0]['parent_lexical_entry_id']);
        $this->assertCount(1, $rows[0]->precomputed_derivation_data->phonetic_developments);

        // The root itself has no derivations/phonetic-developments of its own, but it does have a
        // precomputed row — it's cited as the child's parent, so it has a non-empty derivatives
        // (descendant) tree.
        $rootRows = $r->getLexicalEntries([$root->id]);
        $this->assertNotNull($rootRows[0]->precomputed_derivation_data);
        $this->assertCount(0, $rootRows[0]->precomputed_derivation_data->derivations);
        $this->assertCount(0, $rootRows[0]->precomputed_derivation_data->phonetic_developments);
        $this->assertCount(1, $rootRows[0]->precomputed_derivation_data->derivatives['children']);
    }
}
