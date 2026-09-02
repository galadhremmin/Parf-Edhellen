<?php

namespace Tests\Unit\Api;

use App\Models\Account;
use App\Models\FlashcardResult;
use App\Models\LexicalEntry;
use App\Models\WordList;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The deck endpoints: dealing a finite deck from a word list, and scoring it.
 */
class WordListDeckApiTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAccount(): Account
    {
        /** @var Account */
        $account = Account::factory()->createOne();
        $account->addMembershipTo(RoleConstants::Users);

        return $account;
    }

    private function makeWordList(Account $account, bool $isPublic = false): WordList
    {
        return WordList::create([
            'account_id' => $account->id,
            'name' => 'Deck test list',
            'is_public' => $isPublic,
        ]);
    }

    /**
     * Entries that can genuinely be turned into cards: a word, and a gloss that is not merely a
     * repetition of that word.
     *
     * @return LexicalEntry[]
     */
    private function studiableEntries(int $count): array
    {
        return LexicalEntry::active()
            ->whereHas('glosses')
            ->whereHas('word')
            ->with(['word', 'glosses'])
            ->where('language_id', 1)
            ->limit($count * 3)
            ->get()
            ->filter(function (LexicalEntry $entry) {
                $word = mb_strtolower((string) $entry->word?->word);

                return $entry->glosses->contains(
                    fn ($gloss) => mb_strtolower((string) $gloss->translation) !== $word
                );
            })
            ->take($count)
            ->values()
            ->all();
    }

    private function makeListWithEntries(Account $account, int $count, bool $isPublic = false): array
    {
        $wordList = $this->makeWordList($account, $isPublic);
        $entries = $this->studiableEntries($count);

        foreach ($entries as $i => $entry) {
            $wordList->lexical_entries()->attach($entry->id, ['order' => $i]);
        }

        return [$wordList, $entries];
    }

    // -------------------------------------------------------------------------
    // deck
    // -------------------------------------------------------------------------

    public function test_deals_a_deck_from_the_list()
    {
        $account = $this->makeAccount();
        [$wordList, $entries] = $this->makeListWithEntries($account, 5);

        $deck = $this->actingAs($account)
            ->postJson(route('api.word-lists.deck', ['id' => $wordList->id]), ['direction' => 'forward'])
            ->assertOk()
            ->json('deck');

        $this->assertSame($wordList->id, $deck['word_list_id']);
        $this->assertSame('forward', $deck['direction']);
        $this->assertNotEmpty($deck['cards']);
        $this->assertLessThanOrEqual(count($entries), count($deck['cards']));

        $listed = array_map(fn ($entry) => $entry->id, $entries);
        foreach ($deck['cards'] as $card) {
            $this->assertContains($card['lexical_entry_id'], $listed, 'a deck may only hold words from its list');
        }
    }

    public function test_every_card_carries_the_same_number_of_options_and_a_correct_one()
    {
        $account = $this->makeAccount();
        [$wordList] = $this->makeListWithEntries($account, 5);

        $deck = $this->actingAs($account)
            ->postJson(route('api.word-lists.deck', ['id' => $wordList->id]))
            ->assertOk()
            ->json('deck');

        foreach ($deck['cards'] as $card) {
            // A varying option count would leak information about the card before it is answered.
            $this->assertCount($deck['option_count'], $card['options']);

            $keys = array_column($card['options'], 'key');
            $this->assertSame(array_unique($keys), $keys, 'option keys must be unique within a card');
            $this->assertContains($card['back']['correct_option_key'], $keys);

            $correct = array_values(array_filter(
                $card['options'],
                fn ($option) => $option['key'] === $card['back']['correct_option_key']
            ));
            $this->assertSame($card['back']['answer'], $correct[0]['text']);
        }
    }

    public function test_a_card_never_offers_its_own_answer_twice()
    {
        $account = $this->makeAccount();
        [$wordList] = $this->makeListWithEntries($account, 8);

        $deck = $this->actingAs($account)
            ->postJson(route('api.word-lists.deck', ['id' => $wordList->id]))
            ->assertOk()
            ->json('deck');

        foreach ($deck['cards'] as $card) {
            $texts = array_map(fn ($option) => mb_strtolower($option['text']), $card['options']);
            $this->assertSame(array_unique($texts), $texts, 'the same text must not be offered twice');
        }
    }

    public function test_the_retry_subset_cannot_be_used_to_read_entries_outside_the_list()
    {
        $account = $this->makeAccount();
        [$wordList, $entries] = $this->makeListWithEntries($account, 3);

        // An entry that exists in the dictionary but is not in this list.
        $outsider = LexicalEntry::active()
            ->whereNotIn('id', array_map(fn ($e) => $e->id, $entries))
            ->whereHas('glosses')
            ->firstOrFail();

        $deck = $this->actingAs($account)
            ->postJson(route('api.word-lists.deck', ['id' => $wordList->id]), [
                'lexical_entry_ids' => [$entries[0]->id, $outsider->id],
            ])
            ->assertOk()
            ->json('deck');

        $dealt = array_column($deck['cards'], 'lexical_entry_id');
        $skipped = array_column($deck['skipped'], 'lexical_entry_id');

        $this->assertNotContains($outsider->id, $dealt);
        $this->assertNotContains($outsider->id, $skipped, 'an outside entry must not even be acknowledged');
    }

    public function test_rejects_a_private_list_belonging_to_somebody_else_as_missing()
    {
        $owner = $this->makeAccount();
        [$wordList] = $this->makeListWithEntries($owner, 2);

        $stranger = $this->makeAccount();

        // 404 rather than 403: the existence of a private list is itself private.
        $this->actingAs($stranger)
            ->postJson(route('api.word-lists.deck', ['id' => $wordList->id]))
            ->assertNotFound();
    }

    public function test_allows_a_public_list_to_be_studied_by_anybody_signed_in()
    {
        $owner = $this->makeAccount();
        [$wordList] = $this->makeListWithEntries($owner, 3, isPublic: true);

        $this->actingAs($this->makeAccount())
            ->postJson(route('api.word-lists.deck', ['id' => $wordList->id]))
            ->assertOk();
    }

    public function test_reports_an_empty_list_as_an_empty_deck_rather_than_an_error()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account);

        $deck = $this->actingAs($account)
            ->postJson(route('api.word-lists.deck', ['id' => $wordList->id]))
            ->assertOk()
            ->json('deck');

        $this->assertSame([], $deck['cards']);
        $this->assertSame(0, $deck['number_of_requested']);
    }

    public function test_rejects_an_unknown_direction()
    {
        $account = $this->makeAccount();
        [$wordList] = $this->makeListWithEntries($account, 2);

        $this->actingAs($account)
            ->postJson(route('api.word-lists.deck', ['id' => $wordList->id]), ['direction' => 'sideways'])
            ->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // results
    // -------------------------------------------------------------------------

    public function test_scores_and_records_a_finished_deck()
    {
        $account = $this->makeAccount();
        [$wordList] = $this->makeListWithEntries($account, 4);

        $deck = $this->actingAs($account)
            ->postJson(route('api.word-lists.deck', ['id' => $wordList->id]))
            ->assertOk()
            ->json('deck');

        $answers = [];
        foreach ($deck['cards'] as $i => $card) {
            $answers[] = [
                'lexical_entry_id' => $card['lexical_entry_id'],
                'gloss_id' => $card['gloss_id'],
                // Answer the first card correctly and deliberately fumble the rest.
                'answer' => $i === 0 ? $card['back']['answer'] : 'definitely not the answer',
            ];
        }

        $results = $this->postJson(
            route('api.word-lists.deck-results', ['id' => $wordList->id]),
            ['direction' => 'forward', 'answers' => $answers]
        )->assertOk()->json('results');

        $this->assertSame(1, $results['number_of_correct']);
        $this->assertSame(count($answers) - 1, $results['number_of_wrong']);
        $this->assertCount(count($answers), $results['cards']);

        // The summary is rendered from these, so they must be present without the client
        // joining back against a deck it has already replaced.
        foreach ($results['cards'] as $card) {
            $this->assertNotSame('', $card['word']);
            $this->assertNotEmpty($card['url']);
        }

        $stored = FlashcardResult::where('account_id', $account->id)
            ->where('word_list_id', $wordList->id)
            ->get();

        $this->assertCount(count($answers), $stored);
        $this->assertSame(1, $stored->where('correct', 1)->count());
        foreach ($stored as $row) {
            $this->assertNull($row->flashcard_id, 'a word list result belongs to no flashcard');
            $this->assertSame('forward', $row->direction);
        }
    }

    public function test_scores_server_side_and_ignores_entries_outside_the_list()
    {
        $account = $this->makeAccount();
        [$wordList] = $this->makeListWithEntries($account, 2);

        $outsider = LexicalEntry::active()
            ->whereNotIn('id', $wordList->lexical_entries()->pluck('lexical_entries.id'))
            ->firstOrFail();

        $results = $this->actingAs($account)
            ->postJson(route('api.word-lists.deck-results', ['id' => $wordList->id]), [
                'answers' => [
                    ['lexical_entry_id' => $outsider->id, 'gloss_id' => null, 'answer' => 'anything'],
                ],
            ])
            ->assertOk()
            ->json('results');

        $this->assertSame([], $results['cards']);
        $this->assertSame(0, FlashcardResult::where('word_list_id', $wordList->id)->count());
    }

    public function test_an_abandoned_card_is_recorded_as_wrong()
    {
        $account = $this->makeAccount();
        [$wordList, $entries] = $this->makeListWithEntries($account, 1);

        $results = $this->actingAs($account)
            ->postJson(route('api.word-lists.deck-results', ['id' => $wordList->id]), [
                'answers' => [
                    ['lexical_entry_id' => $entries[0]->id, 'gloss_id' => null, 'answer' => ''],
                ],
            ])
            ->assertOk()
            ->json('results');

        $this->assertSame(0, $results['number_of_correct']);
        $this->assertFalse($results['cards'][0]['correct']);
    }
}
