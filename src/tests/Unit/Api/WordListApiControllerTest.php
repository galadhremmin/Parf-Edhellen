<?php

namespace Tests\Unit\Api;

use App\Models\Account;
use App\Models\LexicalEntry;
use App\Models\WordList;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WordListApiControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAccount(): Account
    {
        /** @var Account */
        $account = Account::factory()->createOne();
        $account->addMembershipTo(RoleConstants::Users);

        return $account;
    }

    /**
     * Drops the acting user. `actingAs` persists for the remainder of the test, so without this a
     * "guest" request would silently still be authenticated and the assertion would prove nothing.
     */
    private function becomeGuest(): void
    {
        $this->app->make('auth')->forgetGuards();
    }

    private function makeWordList(Account $account, bool $isPublic = false): WordList
    {
        return WordList::create([
            'account_id' => $account->id,
            'name' => 'API test list',
            'is_public' => $isPublic,
        ]);
    }

    /**
     * @return LexicalEntry[]
     */
    private function someLexicalEntries(int $count): array
    {
        return LexicalEntry::active()
            ->whereHas('glosses')
            ->whereHas('word')
            ->limit($count)
            ->get()
            ->all();
    }

    // -------------------------------------------------------------------------
    // show
    // -------------------------------------------------------------------------

    public function test_show_returns_an_adapted_payload()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account);

        $entries = $this->someLexicalEntries(2);
        foreach ($entries as $i => $entry) {
            $wordList->lexical_entries()->attach($entry->id, ['order' => $i]);
        }

        $response = $this->actingAs($account)
            ->getJson(route('api.word-lists.show', ['id' => $wordList->id]))
            ->assertOk();

        $payload = $response->json('word_list');

        // The adapter must not leak internal columns.
        $this->assertArrayNotHasKey('account_id', $payload);
        $this->assertSame($wordList->name, $payload['name']);
        $this->assertCount(count($entries), $payload['entries']);

        // The translation is a first class field on every row.
        foreach ($payload['entries'] as $entry) {
            $this->assertArrayHasKey('translation', $entry);
            $this->assertArrayHasKey('url', $entry);
        }
    }

    public function test_show_allows_a_guest_to_read_a_public_list()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account, true);

        $this->getJson(route('api.word-lists.show', ['id' => $wordList->id]))
            ->assertOk()
            ->assertJsonPath('word_list.is_mine', false);
    }

    public function test_show_denies_a_guest_a_private_list()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account);

        $this->getJson(route('api.word-lists.show', ['id' => $wordList->id]))
            ->assertNotFound();
    }

    public function test_show_omits_deleted_entries()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account);

        $entries = $this->someLexicalEntries(2);
        $this->assertCount(2, $entries, 'The test database needs at least two usable lexical entries.');

        foreach ($entries as $i => $entry) {
            $wordList->lexical_entries()->attach($entry->id, ['order' => $i]);
        }

        $entries[0]->is_deleted = 1;
        $entries[0]->save();

        $this->actingAs($account)
            ->getJson(route('api.word-lists.show', ['id' => $wordList->id]))
            ->assertOk()
            ->assertJsonCount(1, 'word_list.entries');
    }

    // -------------------------------------------------------------------------
    // visibility
    // -------------------------------------------------------------------------

    public function test_update_makes_a_list_public()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account);

        $this->actingAs($account)
            ->putJson(route('api.word-lists.update', ['id' => $wordList->id]), [
                'is_public' => true,
            ])
            ->assertOk()
            ->assertJsonPath('word_list.is_public', true);

        $this->assertTrue((bool) $wordList->fresh()->is_public);

        // ... and is then readable by a guest.
        $this->becomeGuest();
        $this->getJson(route('api.word-lists.show', ['id' => $wordList->id]))
            ->assertOk();
    }

    public function test_update_makes_a_list_private_again()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account, true);

        $this->actingAs($account)
            ->putJson(route('api.word-lists.update', ['id' => $wordList->id]), [
                'is_public' => false,
            ])
            ->assertOk()
            ->assertJsonPath('word_list.is_public', false);

        $this->becomeGuest();
        $this->getJson(route('api.word-lists.show', ['id' => $wordList->id]))
            ->assertNotFound();
    }

    public function test_update_refuses_a_list_owned_by_somebody_else()
    {
        $owner = $this->makeAccount();
        $stranger = $this->makeAccount();
        $wordList = $this->makeWordList($owner);

        $this->actingAs($stranger)
            ->putJson(route('api.word-lists.update', ['id' => $wordList->id]), [
                'is_public' => true,
            ])
            ->assertNotFound();

        $this->assertFalse((bool) $wordList->fresh()->is_public);
    }

    // -------------------------------------------------------------------------
    // bulk operations
    // -------------------------------------------------------------------------

    public function test_remove_entries_detaches_in_bulk()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account);

        $entries = $this->someLexicalEntries(2);
        foreach ($entries as $entry) {
            $wordList->lexical_entries()->attach($entry->id);
        }

        $this->actingAs($account)
            ->postJson(route('api.word-lists.bulk-remove-entries', ['id' => $wordList->id]), [
                'lexical_entry_ids' => array_map(fn ($e) => $e->id, $entries),
            ])
            ->assertOk();

        $this->assertSame(0, $wordList->lexical_entries()->count());
    }

    public function test_move_entries_moves_between_two_owned_lists()
    {
        $account = $this->makeAccount();
        $source = $this->makeWordList($account);
        $target = $this->makeWordList($account);

        $entries = $this->someLexicalEntries(1);
        $source->lexical_entries()->attach($entries[0]->id);

        $this->actingAs($account)
            ->postJson(route('api.word-lists.bulk-move-entries', ['id' => $source->id]), [
                'lexical_entry_ids' => [$entries[0]->id],
                'target_word_list_id' => $target->id,
            ])
            ->assertOk()
            ->assertJsonPath('number_of_entries', 1);

        $this->assertSame(0, $source->lexical_entries()->count());
        $this->assertSame(1, $target->lexical_entries()->count());
    }

    public function test_move_entries_refuses_a_target_owned_by_somebody_else()
    {
        $account = $this->makeAccount();
        $stranger = $this->makeAccount();
        $source = $this->makeWordList($account);
        $target = $this->makeWordList($stranger);

        $entries = $this->someLexicalEntries(1);
        $source->lexical_entries()->attach($entries[0]->id);

        $this->actingAs($account)
            ->postJson(route('api.word-lists.bulk-move-entries', ['id' => $source->id]), [
                'lexical_entry_ids' => [$entries[0]->id],
                'target_word_list_id' => $target->id,
            ])
            ->assertNotFound();

        $this->assertSame(1, $source->lexical_entries()->count());
    }

    public function test_move_entries_ignores_entries_the_source_does_not_hold()
    {
        $account = $this->makeAccount();
        $source = $this->makeWordList($account);
        $target = $this->makeWordList($account);

        // An entry that exists, but is not a member of the source list.
        $entries = $this->someLexicalEntries(1);

        $this->actingAs($account)
            ->postJson(route('api.word-lists.bulk-move-entries', ['id' => $source->id]), [
                'lexical_entry_ids' => [$entries[0]->id],
                'target_word_list_id' => $target->id,
            ])
            ->assertOk()
            ->assertJsonPath('number_of_entries', 0);

        $this->assertSame(0, $target->lexical_entries()->count());
    }
}
