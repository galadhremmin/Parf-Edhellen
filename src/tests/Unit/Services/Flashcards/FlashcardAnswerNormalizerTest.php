<?php

namespace Tests\Unit\Services\Flashcards;

use App\Services\Flashcards\FlashcardAnswerNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FlashcardAnswerNormalizerTest extends TestCase
{
    private FlashcardAnswerNormalizer $_normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_normalizer = resolve(FlashcardAnswerNormalizer::class);
    }

    public static function normalizationProvider(): array
    {
        return [
            'lower cases' => ['Star', 'star'],
            'trims' => ['  star  ', 'star'],
            'folds accents' => ['nár', 'nar'],
            'strips the infinitive marker' => ['to run', 'run'],
            'strips a trailing qualifier' => ['star (of the sky)', 'star'],
            'keeps an inner bracket' => ['star (of) the sky', 'star (of) the sky'],
            'collapses whitespace' => ['a  star', 'a star'],
            'does not slugify' => ['a star', 'a star'],
            'empty stays empty' => ['', ''],
            'null stays empty' => [null, ''],
            'qualifier only' => ['(unglossed)', ''],
        ];
    }

    #[DataProvider('normalizationProvider')]
    public function test_normalizes(?string $input, string $expected)
    {
        $this->assertSame($expected, $this->_normalizer->normalize($input));
    }

    public static function matchProvider(): array
    {
        return [
            'case differs' => ['Star', 'star', true],
            'accent differs' => ['nár', 'nar', true],
            'infinitive differs' => ['to run', 'run', true],
            'qualifier differs' => ['star (of the sky)', 'star', true],
            'genuinely different' => ['star', 'moon', false],
            'empty never matches' => ['', '', false],
            'null never matches' => [null, null, false],
        ];
    }

    #[DataProvider('matchProvider')]
    public function test_matches(?string $a, ?string $b, bool $expected)
    {
        $this->assertSame($expected, $this->_normalizer->matches($a, $b));
    }
}
