<?php

namespace Tests\Unit\Services\Senses;

use App\Services\Senses\NormalizedTerm;
use App\Services\Senses\NounLemmatizer;
use App\Services\Senses\SenseNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SenseNormalizerTest extends TestCase
{
    private SenseNormalizer $_normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_normalizer = new SenseNormalizer(new NounLemmatizer(new StubWordNetLexicon));
    }

    public static function keyProvider(): array
    {
        return [
            'plural' => ['trees', ['tree']],
            'qualifier' => ['(tall straight) tree', ['tree']],
            'uncertainty mark' => ['? tree', ['tree']],
            'reconstruction mark' => ['*oak', ['oak']],
            'hyphenated compound' => ['pine-tree', ['pinetree']],
            'spaced compound' => ['pine tree', ['pinetree']],
            'plural compound' => ['pine-trees', ['pinetree']],
            'article' => ['the Elves', ['elf']],
            'quoted name' => ["'Star-queen'", ['starqueen']],
            'curly quotes' => ['‘Star-queen’', ['starqueen']],
            'diacritics are kept' => ['Éyë', ['éyë']],
            'gloss list' => ['gate, door', ['gate', 'door']],
            'semicolon' => ['star; sign, token', ['star', 'sign', 'token']],
            'separator inside a qualifier' => ['wood, forest (of trees; rare)', ['wood', 'forest']],
            'literal translation is not a synonym' => ['elf, (lit.) one of the star-folk', ['elf']],
            'editorial origin note' => ['to surround with walls; [orig.] to surround', ['to:surroundwithwalls']],
            'infinitive' => ['to fall (down)', ['to:fall']],
            'bracketed infinitive' => ['[to] free', ['to:free']],
            'verbs are not lemmatised' => ['to bless the days', ['to:blessthedays']],
            'short words are not lemmatised' => ['was', ['was']],
            'marks only' => ['?', []],
        ];
    }

    #[DataProvider('keyProvider')]
    public function test_keys(string $sense, array $expected)
    {
        $keys = $this->_normalizer->normalize($sense)->map(fn (NormalizedTerm $term) => $term->key)->all();

        $this->assertSame($expected, $keys);
    }

    public function test_a_verb_sense_without_to_is_keyed_as_a_verb()
    {
        $term = $this->_normalizer->normalize('sew', true)->first();

        $this->assertSame('to:sew', $term->key);
        $this->assertTrue($term->isVerb);
    }

    public function test_records_the_word_the_lemmatiser_reduced()
    {
        [$reduced, $unchanged] = $this->_normalizer->normalize('pine-trees, tree')->all();

        $this->assertSame('trees', $reduced->reducedFrom);
        $this->assertNull($unchanged->reducedFrom);
    }

    public function test_positions_are_contiguous_after_skipped_terms()
    {
        $terms = $this->_normalizer->normalize('elf, (lit.) shining one, fairy');

        $this->assertSame([0, 1], $terms->map(fn (NormalizedTerm $term) => $term->position)->all());
        $this->assertSame('fairy', $terms[1]->term);
    }
}
