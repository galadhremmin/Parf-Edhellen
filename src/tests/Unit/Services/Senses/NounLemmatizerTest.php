<?php

namespace Tests\Unit\Services\Senses;

use App\Services\Senses\NounLemmatizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NounLemmatizerTest extends TestCase
{
    public static function lemmaProvider(): array
    {
        return [
            'regular plural' => ['trees', 'tree'],
            'ies plural' => ['skies', 'sky'],
            'ches plural' => ['churches', 'church'],
            'irregular plural' => ['elves', 'elf'],
            'irregular plural from the exception list' => ['teeth', 'tooth'],
            'plural whose base is far more common' => ['days', 'day'],
            'another plural lemma that reduces' => ['eyes', 'eye'],
            'singular ending in s' => ['pass', 'pass'],
            'lemma more common than its supposed base' => ['species', 'species'],
            'collective noun without an s' => ['people', 'people'],
            'no known base' => ['ponderous', 'ponderous'],
            'word WordNet does not know' => ['lembas', 'lembas'],
            'already singular' => ['tree', 'tree'],
        ];
    }

    #[DataProvider('lemmaProvider')]
    public function test_lemmatizes(string $word, string $expected)
    {
        $lemmatizer = new NounLemmatizer(new StubWordNetLexicon);

        $this->assertSame($expected, $lemmatizer->lemmatize($word));
    }
}
