<?php

namespace Tests\Unit\Services\Senses;

use App\Services\Senses\NameConcepts;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Unit\Traits\CanBuildSenses;

class NameConceptsTest extends TestCase
{
    use CanBuildSenses;

    public static function nameProvider(): array
    {
        return [
            'a man' => [['masculine name'], '06348677-n'],
            'a woman' => [['feminine name'], '06348677-n'],
            'a family' => [['family name'], '06348274-n'],
            'a place' => [['place name'], '06355208-n'],
            'a collective' => [['collective name'], '06344646-n'],
            'whichever kind more entries record' => [['place name', 'masculine name', 'place name'], '06355208-n'],
            'a name of no stated kind' => [[], '06344646-n'],
        ];
    }

    #[DataProvider('nameProvider')]
    public function test_places_a_name(array $speeches, string $expected)
    {
        $this->assertSame($expected, resolve(NameConcepts::class)->synsetIdFor($this->buildSense('gildir', $speeches)));
    }
}
