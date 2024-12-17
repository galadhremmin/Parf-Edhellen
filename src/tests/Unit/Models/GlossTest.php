<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Gloss;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GlossTest extends TestCase
{
    use DatabaseTransactions; 

    private $_gloss;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_gloss = new Gloss([
            'account_id' => 123,
            'language_id' => 1,
            'word_id' => 13,
            'speech_id' => 15,
            'gloss_group_id' => 0,
            'sense' => '',
            'is_rejected' => true,
            'external_id' => null,
            'has_details' => true,
            'label' => '123'
        ]);
    }
    
    public function testEqualsItself(): void
    {
        $this->assertTrue($this->_gloss->equals($this->_gloss));
    }
    
    public function testEqualsACloneWithSameValues(): void
    {
        $clonedGloss = $this->_gloss->replicate();
        $this->assertTrue($this->_gloss->equals($clonedGloss));
    }
    
    public function testDoesNotequalADIfferentGloss(): void
    {
        $clonedGloss = $this->_gloss->replicate();
        $clonedGloss->word_id += 1;
        $this->assertFalse($this->_gloss->equals($clonedGloss));
    }
    
    public function testDoesEqualEvenThoughIdChanged(): void
    {
        $clonedGloss = $this->_gloss->replicate();
        $clonedGloss->id += 1;
        $this->assertTrue($this->_gloss->equals($clonedGloss));
    }
    
    public function testDoesEqualEvenThoughDateChanged(): void
    {
        $clonedGloss = $this->_gloss->replicate();
        $clonedGloss->created_at = Carbon::now();
        $this->assertTrue($this->_gloss->equals($clonedGloss));
    }
    
    public function testDoesRejectInvalidValues(): void
    {
        $this->assertFalse($this->_gloss->equals(null));
        $this->assertFalse($this->_gloss->equals([]));
        $this->assertFalse($this->_gloss->equals(0));
        $this->assertFalse($this->_gloss->equals(''));
    }
}
