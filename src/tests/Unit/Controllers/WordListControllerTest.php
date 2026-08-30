<?php

namespace Tests\Unit\Controllers;

use App\Models\Account;
use App\Models\AuthorizationProvider;
use App\Models\LexicalEntry;
use App\Models\WordList;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class WordListControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAccount(): Account
    {
        $suffix = 'word-list-'.Str::random(8);

        $provider = AuthorizationProvider::create([
            'name' => "Test provider $suffix",
            'name_identifier' => $suffix,
            'logo_file_name' => "$suffix.jpg",
        ]);

        $account = Account::factory()->createOne([
            'authorization_provider_id' => $provider->id,
            'email' => "$suffix@example.com",
        ]);
        $account->addMembershipTo(RoleConstants::Users);

        return $account;
    }

    private function makeWordList(Account $account, bool $isPublic = false): WordList
    {
        $wordList = WordList::create([
            'account_id' => $account->id,
            'name' => 'Elvish words for testing',
            'description' => 'A list used by the automated tests.',
            'is_public' => $isPublic,
        ]);

        $lexicalEntry = LexicalEntry::active()->whereHas('glosses')->first();
        if ($lexicalEntry !== null) {
            $wordList->lexical_entries()->attach($lexicalEntry->id, ['order' => 0]);
        }

        return $wordList;
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_requires_authentication()
    {
        $this->get(route('word-list.index'))
            ->assertRedirect();
    }

    public function test_index_lists_the_accounts_word_lists()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account);

        $this->actingAs($account)
            ->get(route('word-list.index'))
            ->assertOk()
            ->assertSee($wordList->name, false);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_show_renders_for_the_owner()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account);

        $this->actingAs($account)
            ->get(route('word-list.show', ['id' => $wordList->id, 'name' => 'elvish_words_for_testing']))
            ->assertOk()
            ->assertSee('data-inject-module="word-list"', false);
    }

    public function test_show_redirects_to_the_canonical_name()
    {
        $account = $this->makeAccount();
        $wordList = $this->makeWordList($account);

        $this->actingAs($account)
            ->get(route('word-list.show', ['id' => $wordList->id]))
            ->assertRedirect(route('word-list.show', [
                'id' => $wordList->id,
                'name' => 'elvish_words_for_testing',
            ]));
    }

    public function test_show_hides_a_private_list_from_a_stranger()
    {
        $owner = $this->makeAccount();
        $stranger = $this->makeAccount();
        $wordList = $this->makeWordList($owner);

        $this->actingAs($stranger)
            ->get(route('word-list.show', ['id' => $wordList->id, 'name' => 'elvish_words_for_testing']))
            ->assertNotFound();
    }

    public function test_show_hides_a_private_list_from_a_guest()
    {
        $owner = $this->makeAccount();
        $wordList = $this->makeWordList($owner);

        $this->get(route('word-list.show', ['id' => $wordList->id, 'name' => 'elvish_words_for_testing']))
            ->assertNotFound();
    }

    public function test_show_renders_a_public_list_for_a_guest()
    {
        $owner = $this->makeAccount();
        $wordList = $this->makeWordList($owner, true);

        $this->get(route('word-list.show', ['id' => $wordList->id, 'name' => 'elvish_words_for_testing']))
            ->assertOk()
            ->assertSee('data-inject-prop-can-edit="false"', false);
    }
}
