<?php

namespace Tests\Unit\Services\Senses;

use App\Services\Enumerations\SenseKind;
use App\Services\Enumerations\WordNetPos;
use App\Services\Senses\SenseClassifier;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Unit\Traits\CanBuildSenses;

class SenseClassifierTest extends TestCase
{
    use CanBuildSenses;

    public static function kindProvider(): array
    {
        return [
            'a noun' => ['tree', ['noun'], SenseKind::LEXICAL],
            'a place name' => ['eglarest', ['place name'], SenseKind::NAME],
            'a suffix' => ['superlative ending', ['suffix'], SenseKind::GRAMMAR],
            'a pronoun' => ['you', ['pronoun'], SenseKind::FUNCTION_WORD],
            'one ordinary use outweighs names' => ['star', ['masculine name', 'noun'], SenseKind::LEXICAL],
            'the commoner of two other kinds' => ['aldaron', ['masculine name', 'masculine name', 'suffix'], SenseKind::NAME],
            'a function word without a part of speech' => ['we', [], SenseKind::FUNCTION_WORD],
            'an ordinary word without a part of speech' => ['tree', [], SenseKind::LEXICAL],
            'an unknown part of speech says nothing' => ['we', ['?'], SenseKind::FUNCTION_WORD],
        ];
    }

    #[DataProvider('kindProvider')]
    public function test_classifies(string $headword, array $speeches, SenseKind $expected)
    {
        $this->assertSame($expected, resolve(SenseClassifier::class)->classify($this->buildSense($headword, $speeches)));
    }

    public function test_a_capitalised_sense_that_is_no_english_word_is_a_name()
    {
        $classifier = resolve(SenseClassifier::class);

        $this->assertSame(SenseKind::NAME, $classifier->classify($this->buildSense('Mithlond')));
        $this->assertSame(SenseKind::NAME, $classifier->classify($this->buildSense('Estë', untranslated: true)));
        // WordNet knows these, so an editor decides rather than a rule
        $this->assertSame(SenseKind::LEXICAL, $classifier->classify($this->buildSense('Oxford')));
        $this->assertSame(SenseKind::LEXICAL, $classifier->classify($this->buildSense('Tree')));
    }

    public function test_a_sense_that_only_repeats_its_own_word_translates_nothing()
    {
        $classifier = resolve(SenseClassifier::class);

        $this->assertSame(SenseKind::UNTRANSLATED, $classifier->classify($this->buildSense('adhanc', ['noun'], untranslated: true)));
        // the part of speech still settles a name, and a gloss that says more makes it an ordinary sense
        $this->assertSame(SenseKind::NAME, $classifier->classify($this->buildSense('thingol', ['masculine name'], untranslated: true)));
        $this->assertSame(SenseKind::LEXICAL, $classifier->classify($this->buildSense('mallorn', ['noun'])));
    }

    public function test_maps_parts_of_speech_to_wordnet()
    {
        $classifier = resolve(SenseClassifier::class);

        $this->assertSame([WordNetPos::ADJECTIVE, WordNetPos::ADJECTIVE_SATELLITE],
            $classifier->wordNetPos($this->buildSense('light', ['adjective'])));
        $this->assertSame([WordNetPos::NOUN, WordNetPos::VERB], $classifier->wordNetPos($this->buildSense('wound', ['noun', 'verb'])));
        $this->assertNull($classifier->wordNetPos($this->buildSense('light')));
    }
}
