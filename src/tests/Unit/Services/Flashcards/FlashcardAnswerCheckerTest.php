<?php

namespace Tests\Unit\Services\Flashcards;

use App\Services\Flashcards\FlashcardAnswerChecker;
use Tests\TestCase;

class FlashcardAnswerCheckerTest extends TestCase
{
    private FlashcardAnswerChecker $_checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_checker = resolve(FlashcardAnswerChecker::class);
    }

    public function test_accepts_an_exact_answer()
    {
        $this->assertTrue($this->_checker->isCorrect('star', ['star']));
    }

    public function test_accepts_a_differently_cased_answer()
    {
        // strcmp() was byte exact, so "Star" used to be marked wrong for "star".
        $this->assertTrue($this->_checker->isCorrect('Star', ['star']));
    }

    public function test_accepts_any_gloss_on_the_entry_not_only_the_one_shown()
    {
        $this->assertTrue($this->_checker->isCorrect('heavenly body', ['star', 'heavenly body']));
    }

    public function test_accepts_an_answer_differing_only_by_the_infinitive_marker()
    {
        $this->assertTrue($this->_checker->isCorrect('eat', ['to eat']));
    }

    public function test_rejects_a_wrong_answer()
    {
        $this->assertFalse($this->_checker->isCorrect('sky', ['star', 'heavenly body']));
    }

    public function test_rejects_an_empty_answer()
    {
        // An abandoned card must not match, least of all against an empty expectation.
        $this->assertFalse($this->_checker->isCorrect('', ['star']));
        $this->assertFalse($this->_checker->isCorrect('', ['']));
    }

    public function test_consults_the_synonym_fallback_only_when_the_strict_check_fails()
    {
        $called = 0;
        $fallback = function () use (&$called) {
            $called += 1;

            return ['gil'];
        };

        $this->assertTrue($this->_checker->isCorrect('elen', ['elen'], $fallback));
        $this->assertSame(0, $called, 'a right answer must not pay for the synonym query');

        $this->assertTrue($this->_checker->isCorrect('gil', ['elen'], $fallback));
        $this->assertSame(1, $called);
    }

    public function test_rejects_a_wrong_answer_even_with_a_fallback()
    {
        $this->assertFalse($this->_checker->isCorrect('anor', ['elen'], fn () => ['gil']));
    }
}
