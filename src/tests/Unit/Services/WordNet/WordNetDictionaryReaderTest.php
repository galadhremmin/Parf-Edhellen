<?php

namespace Tests\Unit\Services\WordNet;

use App\Services\WordNet\WordNetDictionaryReader;
use PHPUnit\Framework\TestCase;

class WordNetDictionaryReaderTest extends TestCase
{
    private WordNetDictionaryReader $_reader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_reader = new WordNetDictionaryReader(__DIR__.'/fixtures/dict');
    }

    public function test_reads_synsets_from_every_data_file_and_skips_the_license_header()
    {
        $synsets = collect(iterator_to_array($this->_reader->synsets(), false))->keyBy('id');

        $this->assertSame(['12288763-n', '11365176-n', '00001740-v', '00003552-a', '00001740-r'], $synsets->keys()->all());

        $oak = $synsets['12288763-n'];
        $this->assertSame('n', $oak['pos']);
        $this->assertSame('oak', $oak['label']);
        $this->assertSame('Tolkien', $synsets['11365176-n']['label']);
        $this->assertSame('noun.plant', $oak['lexname']);
        $this->assertStringStartsWith('a deciduous tree of the genus Quercus', $oak['definition']);
    }

    public function test_a_satellite_keeps_its_type_but_shares_the_adjective_id()
    {
        $synsets = collect(iterator_to_array($this->_reader->synsets(), false))->keyBy('id');

        $this->assertSame('s', $synsets['00003552-a']['pos']);
        $this->assertSame('adj.all', $synsets['00003552-a']['lexname']);
    }

    public function test_reads_hypernyms_and_instance_hypernyms_only()
    {
        $hypernyms = iterator_to_array($this->_reader->hypernyms(), false);

        $this->assertContains(['synset_id' => '12288763-n', 'hypernym_id' => '13124818-n', 'is_instance' => false], $hypernyms);
        $this->assertContains(['synset_id' => '11365176-n', 'hypernym_id' => '10442970-n', 'is_instance' => true], $hypernyms);
        // the oak's meronyms, holonyms and hyponyms are other pointer types
        $this->assertCount(1, collect($hypernyms)->where('synset_id', '12288763-n'));
    }

    public function test_reads_senses_with_spaces_for_underscores_and_the_adjective_id_for_satellites()
    {
        $senses = collect(iterator_to_array($this->_reader->senses(), false))->keyBy('lemma');

        $this->assertSame(['lemma' => 'oak', 'synset_id' => '12288763-n', 'pos' => 'n', 'sense_number' => 2, 'tag_count' => 1], $senses['oak']);
        $this->assertSame('11365176-n', $senses['j.r.r. tolkien']['synset_id']);
        $this->assertSame(22, $senses['breathe']['tag_count']);
        $this->assertSame(['00003552-a', 's'], [$senses['emergent']['synset_id'], $senses['emergent']['pos']]);
    }

    public function test_reads_exceptions_once_each()
    {
        $exceptions = collect(iterator_to_array($this->_reader->exceptions(), false))->where('pos', 'n')->values();

        $this->assertSame([
            ['pos' => 'n', 'form' => 'diastemata', 'base' => 'diastema'],
            ['pos' => 'n', 'form' => 'elves', 'base' => 'elf'],
        ], $exceptions->all());
    }
}
