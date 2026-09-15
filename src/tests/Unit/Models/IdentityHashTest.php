<?php

namespace Tests\Unit\Models;

use App\Models\Keyword;
use App\Models\SearchKeyword;
use Tests\TestCase;

class IdentityHashTest extends TestCase
{
    public function test_hash_is_independent_of_php_types()
    {
        $fromDatabase = ['keyword' => 'mîr', 'word_id' => 5, 'sense_id' => 7, 'lexical_entry_id' => 9, 'sentence_fragment_id' => null, 'keyword_language_id' => 1];
        $fromApp = ['keyword' => 'mîr', 'word_id' => '5', 'sense_id' => 7, 'lexical_entry_id' => '9', 'keyword_language_id' => '1', 'normalized_keyword' => 'mir'];

        $this->assertSame(Keyword::identityHash($fromDatabase), Keyword::identityHash($fromApp));
        $this->assertSame(
            SearchKeyword::identityHash(['is_old' => 0, 'keyword' => 'a']),
            SearchKeyword::identityHash(['is_old' => false, 'keyword' => 'a'])
        );
    }

    public function test_hash_distinguishes_what_the_collation_does_not()
    {
        $row = ['keyword' => 'la', 'word_id' => 5, 'sense_id' => 7, 'lexical_entry_id' => 9, 'keyword_language_id' => null];

        $this->assertNotSame(Keyword::identityHash($row), Keyword::identityHash(['keyword' => 'lá'] + $row));
        $this->assertNotSame(Keyword::identityHash($row), Keyword::identityHash(['keyword' => 'La'] + $row));
        $this->assertNotSame(Keyword::identityHash($row), Keyword::identityHash(['keyword_language_id' => 0] + $row));
    }
}
