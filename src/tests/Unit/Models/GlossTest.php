<?php

namespace Tests\Unit\Models;

use App\Models\Gloss;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

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
            'label' => '123',
        ]);
    }

    public function test_equals_itself(): void
    {
        $this->assertTrue($this->_gloss->equals($this->_gloss));
    }

    public function test_equals_a_clone_with_same_values(): void
    {
        $clonedGloss = $this->_gloss->replicate();
        $this->assertTrue($this->_gloss->equals($clonedGloss));
    }

    public function test_does_notequal_ad_ifferent_gloss(): void
    {
        $clonedGloss = $this->_gloss->replicate();
        $clonedGloss->word_id += 1;
        $this->assertFalse($this->_gloss->equals($clonedGloss));
    }

    public function test_does_equal_even_though_id_changed(): void
    {
        $clonedGloss = $this->_gloss->replicate();
        $clonedGloss->id += 1;
        $this->assertTrue($this->_gloss->equals($clonedGloss));
    }

    public function test_does_equal_even_though_date_changed(): void
    {
        $clonedGloss = $this->_gloss->replicate();
        $clonedGloss->created_at = Carbon::now();
        $this->assertTrue($this->_gloss->equals($clonedGloss));
    }

    public function test_does_reject_invalid_values(): void
    {
        $this->assertFalse($this->_gloss->equals(null));
        $this->assertFalse($this->_gloss->equals([]));
        $this->assertFalse($this->_gloss->equals(0));
        $this->assertFalse($this->_gloss->equals(''));
    }
}
