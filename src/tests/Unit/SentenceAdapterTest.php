<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Adapters\{
    SentenceAdapter,
    LatinSentenceBuilder
};
use DB;

class SentenceAdapterTest extends TestCase
{
    private $_adapter;
    private $_data;

    public function setUp() 
    {
        $this->_adapter = resolve(SentenceAdapter::class);
        $this->_data = json_decode(file_get_contents(__DIR__.'/SentenceAdapterTest.1.json'), true);
        $this->_builder = new LatinSentenceBuilder($this->_data['sentence_fragments']);
    }

    public function testSuccessfulInitializationAtConstruction()
    {
        $this->assertAttributeEquals(34, '_numberOfFragments', $this->_builder);
    }

    public function testGetFragment()
    {
        $i = 0;
        foreach ($this->_data['sentence_fragments'] as $fragment) {
            $this->assertEquals($this->_builder->getFragment($i), $fragment);
            $i += 1;
        }
    }
}