<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\AuthorController;
use App\Models\Account;
use App\Models\AuthorizationProvider;
use App\Models\LexicalEntry;
use App\Models\WordList;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The word lists shown on somebody's profile.
 */
class AuthorProfileWordListsTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAccount(): Account
    {
        $suffix = 'author-word-list-'.Str::random(8);

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

    /**
     * @param  array<int,LexicalEntry>  $lexicalEntries
     */
    private function makeWordList(Account $account, bool $isPublic, string $name, array $lexicalEntries = []): WordList
    {
        $wordList = WordList::create([
            'account_id' => $account->id,
            'name' => $name,
            'is_public' => $isPublic,
        ]);

        $order = 0;
        foreach ($lexicalEntries as $lexicalEntry) {
            $wordList->lexical_entries()->attach($lexicalEntry->id, ['order' => $order++]);
        }

        return $wordList;
    }

    /**
     * @return array<int,LexicalEntry>
     */
    private function someLexicalEntries(int $count): array
    {
        return LexicalEntry::active()->whereHas('word')->with('word')->limit($count)->get()->all();
    }

    private function getPublicWordLists(Account $account): array
    {
        $method = new ReflectionMethod(AuthorController::class, 'getPublicWordLists');
        $method->setAccessible(true);

        return $method->invoke(app(AuthorController::class), $account);
    }

    public function test_it_shows_a_public_word_list_with_its_size_and_a_taste_of_its_words()
    {
        $account = $this->makeAccount();
        $entries = $this->someLexicalEntries(3);
        $this->makeWordList($account, true, 'Words worth keeping', $entries);

        $wordLists = $this->getPublicWordLists($account);

        $this->assertCount(1, $wordLists);
        $this->assertEquals('Words worth keeping', $wordLists[0]['name']);
        $this->assertEquals(count($entries), $wordLists[0]['number_of_entries']);
        $this->assertEquals(
            array_map(fn (LexicalEntry $entry) => $entry->word->word, $entries),
            $wordLists[0]['preview_words']
        );
    }

    public function test_it_hides_a_private_word_list_even_from_the_person_who_owns_it()
    {
        $account = $this->makeAccount();
        $this->makeWordList($account, false, 'Kept to myself', $this->someLexicalEntries(2));

        $this->assertCount(0, $this->getPublicWordLists($account));
    }

    public function test_it_shows_no_word_lists_belonging_to_somebody_else()
    {
        $account = $this->makeAccount();
        $stranger = $this->makeAccount();
        $this->makeWordList($stranger, true, 'Not mine', $this->someLexicalEntries(2));

        $this->assertCount(0, $this->getPublicWordLists($account));
    }

    public function test_it_previews_only_the_first_few_words_of_a_long_list()
    {
        $account = $this->makeAccount();
        $entries = $this->someLexicalEntries(9);
        $this->assertGreaterThan(6, count($entries), 'The fixture needs more entries than the preview shows.');

        $this->makeWordList($account, true, 'A long list', $entries);
        $wordLists = $this->getPublicWordLists($account);

        $this->assertEquals(count($entries), $wordLists[0]['number_of_entries']);
        $this->assertCount(6, $wordLists[0]['preview_words']);

        // The preview follows the owner's own order, so the list reads as they arranged it.
        $this->assertEquals(
            array_map(fn (LexicalEntry $entry) => $entry->word->word, array_slice($entries, 0, 6)),
            $wordLists[0]['preview_words']
        );
    }

    public function test_the_profile_page_renders_the_public_word_list()
    {
        $account = $this->makeAccount();
        $this->makeWordList($account, true, 'Shown on my profile', $this->someLexicalEntries(2));

        $this->get(route('author.profile-without-nickname', ['id' => $account->id]))
            ->assertOk()
            ->assertSee('Shown on my profile');
    }
}
