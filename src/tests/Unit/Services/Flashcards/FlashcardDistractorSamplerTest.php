<?php

namespace Tests\Unit\Services\Flashcards;

use App\Interfaces\IFlashcardCandidateProvider;
use App\Services\Flashcards\FlashcardAnswerNormalizer;
use App\Services\Flashcards\FlashcardCandidate;
use App\Services\Flashcards\FlashcardDirection;
use App\Services\Flashcards\FlashcardDistractorSampler;
use App\Services\Flashcards\VerbSpeechCatalogue;
use Tests\TestCase;

/**
 * The sampler reaches the database only through IFlashcardCandidateProvider, so every rule it holds
 * is exercised here against a stub pool — no database, no fixtures.
 */
class FlashcardDistractorSamplerTest extends TestCase
{
    private const VERB_SPEECH_ID = 7;

    private ?RecordingCandidateProvider $_provider = null;

    /**
     * @param  FlashcardCandidate[]  $pool
     */
    private function makeSampler(array $pool): FlashcardDistractorSampler
    {
        $this->_provider = new RecordingCandidateProvider($pool, self::VERB_SPEECH_ID);

        $verbs = new class extends VerbSpeechCatalogue
        {
            public function getIds(): array
            {
                return [FlashcardDistractorSamplerTest::verbSpeechId()];
            }
        };

        return new FlashcardDistractorSampler(
            $this->_provider, resolve(FlashcardAnswerNormalizer::class), $verbs
        );
    }

    /**
     * The pool queries the sampler issued, in order.
     */
    private function calls(): array
    {
        return $this->_provider->calls;
    }

    public static function verbSpeechId(): int
    {
        return self::VERB_SPEECH_ID;
    }

    private function candidate(int $id, string $word, string $translation, ?int $senseId = null, int $languageId = 1, ?int $speechId = 1): FlashcardCandidate
    {
        return new FlashcardCandidate(
            lexicalEntryId: $id,
            glossId: $id * 10,
            word: $word,
            normalizedWord: mb_strtolower($word),
            translation: $translation,
            languageId: $languageId,
            speechId: $speechId,
            senseId: $senseId,
        );
    }

    /**
     * @return FlashcardCandidate[]
     */
    private function genericPool(int $size = 40): array
    {
        $pool = [];
        for ($i = 100; $i < 100 + $size; $i += 1) {
            $pool[] = $this->candidate($i, 'word'.$i, 'meaning '.$i);
        }

        return $pool;
    }

    public function test_deals_the_requested_number_of_options()
    {
        $subject = $this->candidate(1, 'elen', 'star');
        $sampler = $this->makeSampler($this->genericPool());

        $set = $sampler->sample([$subject], FlashcardDirection::Forward, [1 => ['star']], [1], [], 4);

        $this->assertSame(4, $set->optionCount);
        $this->assertCount(3, $set->distractors[1]);
        $this->assertEmpty($set->skipped);
    }

    public function test_never_offers_the_subject_itself()
    {
        $subject = $this->candidate(1, 'elen', 'star');
        $pool = array_merge([$this->candidate(1, 'elen', 'star')], $this->genericPool());
        $sampler = $this->makeSampler($pool);

        $set = $sampler->sample([$subject], FlashcardDirection::Forward, [1 => ['star']], [1], [], 4);

        foreach ($set->distractors[1] as $distractor) {
            $this->assertNotSame(1, $distractor->lexicalEntryId);
        }
    }

    public function test_excludes_entries_sharing_a_sense()
    {
        $subject = $this->candidate(1, 'elen', 'star', senseId: 55);
        $synonym = $this->candidate(2, 'gil', 'star, bright spark', senseId: 55);
        $sampler = $this->makeSampler(array_merge([$synonym], $this->genericPool()));

        $set = $sampler->sample([$subject], FlashcardDirection::Forward, [1 => ['star']], [1], [], 4);

        $ids = array_map(fn ($c) => $c->lexicalEntryId, $set->distractors[1]);
        $this->assertNotContains(2, $ids, 'a distractor sharing the subject sense is a synonym');
    }

    public function test_forward_excludes_every_gloss_on_the_subject_entry_not_only_the_one_shown()
    {
        $subject = $this->candidate(1, 'elen', 'star');
        // "heavenly body" is another gloss on the very same entry, so it is a correct answer too.
        $alsoCorrect = $this->candidate(2, 'tinwe', 'Heavenly Body');
        $sampler = $this->makeSampler(array_merge([$alsoCorrect], $this->genericPool()));

        $set = $sampler->sample(
            [$subject], FlashcardDirection::Forward, [1 => ['star', 'heavenly body']], [1], [], 4
        );

        $texts = array_map(fn ($c) => mb_strtolower($c->translation), $set->distractors[1]);
        $this->assertNotContains('heavenly body', $texts);
    }

    public function test_reverse_rejects_a_different_word_that_also_means_the_prompt()
    {
        $subject = $this->candidate(1, 'elen', 'star');
        // A genuinely correct alternative answer: offering it would mark a right answer wrong.
        $alsoCorrect = $this->candidate(2, 'gil', 'star');
        $sampler = $this->makeSampler(array_merge([$alsoCorrect], $this->genericPool()));

        $set = $sampler->sample(
            [$subject],
            FlashcardDirection::Reverse,
            [1 => ['star'], 2 => ['star']],
            [1],
            [],
            4
        );

        $words = array_map(fn ($c) => $c->word, $set->distractors[1]);
        $this->assertNotContains('gil', $words);
    }

    public function test_reverse_collapses_repeats_of_the_same_word()
    {
        $subject = $this->candidate(1, 'elen', 'star');
        // The same Elvish word across several entries — offering it twice on one card is a bug.
        $pool = [
            $this->candidate(2, 'anor', 'sun'),
            $this->candidate(3, 'anor', 'the sun'),
            $this->candidate(4, 'anor', 'sunlight'),
            $this->candidate(5, 'aur', 'day'),
            $this->candidate(6, 'ross', 'rain'),
            $this->candidate(7, 'nen', 'water'),
        ];
        $sampler = $this->makeSampler($pool);

        $set = $sampler->sample([$subject], FlashcardDirection::Reverse, [1 => ['star']], [1], [], 4);

        $words = array_map(fn ($c) => $c->word, $set->distractors[1]);
        $this->assertSame(array_unique($words), $words, 'the same word must not appear twice');
    }

    public function test_option_count_is_uniform_across_the_deck()
    {
        // Only two usable distractors exist, so no card can reach four options.
        $pool = [
            $this->candidate(50, 'aur', 'day'),
            $this->candidate(51, 'ross', 'rain'),
        ];
        $subjects = [
            $this->candidate(1, 'elen', 'star'),
            $this->candidate(2, 'menel', 'sky'),
        ];
        $sampler = $this->makeSampler($pool);

        $set = $sampler->sample(
            $subjects, FlashcardDirection::Forward, [1 => ['star'], 2 => ['sky']], [1], [], 4
        );

        $this->assertSame(3, $set->optionCount);
        foreach ($set->distractors as $options) {
            $this->assertCount(2, $options, 'every card carries the same number of options');
        }
    }

    public function test_a_card_that_cannot_reach_the_floor_is_skipped_not_dealt_short()
    {
        $subject = $this->candidate(1, 'elen', 'star', senseId: 9);
        // The only other word in the world shares the subject's sense, so nothing may be offered.
        $sampler = $this->makeSampler([$this->candidate(2, 'gil', 'star', senseId: 9)]);

        $set = $sampler->sample([$subject], FlashcardDirection::Forward, [1 => ['star']], [1], [], 4);

        $this->assertSame([1], $set->skipped);
        $this->assertArrayNotHasKey(1, $set->distractors);
    }

    public function test_buckets_verbs_apart_from_other_words()
    {
        $subjects = [
            $this->candidate(1, 'mata', 'to eat', speechId: self::VERB_SPEECH_ID),
            $this->candidate(2, 'elen', 'star', speechId: 1),
        ];
        $pool = array_merge(
            $this->genericPool(),
            [$this->candidate(200, 'sila', 'to shine', speechId: self::VERB_SPEECH_ID)]
        );
        $sampler = $this->makeSampler($pool);

        $sampler->sample($subjects, FlashcardDirection::Forward, [1 => ['to eat'], 2 => ['star']], [1], [], 4);

        $verbFlags = array_column($this->calls(), 'verbsOnly');
        $this->assertContains(true, $verbFlags, 'the verb card draws from a verbs-only pool');
        $this->assertContains(false, $verbFlags, 'the noun card draws from a pool without verbs');
    }

    public function test_issues_one_pool_query_per_bucket_not_per_card()
    {
        $subjects = [];
        for ($i = 1; $i <= 20; $i += 1) {
            $subjects[] = $this->candidate($i, 'word'.$i, 'meaning '.$i, speechId: 1);
        }
        $sampler = $this->makeSampler($this->genericPool(200));

        $translations = [];
        foreach ($subjects as $subject) {
            $translations[$subject->lexicalEntryId] = [$subject->translation];
        }

        $sampler->sample($subjects, FlashcardDirection::Forward, $translations, [1], [], 4);

        $this->assertCount(1, $this->calls(), '20 same-bucket cards must cost exactly one pool query');
    }

    public function test_excludes_deck_subjects_from_the_pool_query()
    {
        $subjects = [
            $this->candidate(1, 'elen', 'star'),
            $this->candidate(2, 'menel', 'sky'),
        ];
        $sampler = $this->makeSampler($this->genericPool());

        $sampler->sample($subjects, FlashcardDirection::Forward, [1 => ['star'], 2 => ['sky']], [1], [], 4);

        $this->assertSame([1, 2], $this->calls()[0]['excludeLexicalEntryIds']);
    }

    public function test_an_empty_deck_is_handled_without_querying()
    {
        $sampler = $this->makeSampler($this->genericPool());

        $set = $sampler->sample([], FlashcardDirection::Forward, [], [1], [], 4);

        $this->assertEmpty($set->distractors);
        $this->assertEmpty($this->calls());
    }
}

/**
 * A pool the sampler can draw from, recording every query it made so that the tests can assert on
 * the query count as well as the result.
 */
class RecordingCandidateProvider implements IFlashcardCandidateProvider
{
    public array $calls = [];

    /**
     * @param  FlashcardCandidate[]  $pool
     */
    public function __construct(private array $pool, private int $verbSpeechId) {}

    public function getPool(array $languageIds, array $lexicalEntryGroupIds, ?bool $verbsOnly, int $limit, array $excludeLexicalEntryIds = []): array
    {
        $this->calls[] = compact('languageIds', 'verbsOnly', 'limit', 'excludeLexicalEntryIds');

        return array_values(array_filter($this->pool, function (FlashcardCandidate $c) use ($languageIds, $verbsOnly, $excludeLexicalEntryIds) {
            if (! empty($languageIds) && ! in_array($c->languageId, $languageIds, true)) {
                return false;
            }
            if ($verbsOnly === true && $c->speechId !== $this->verbSpeechId) {
                return false;
            }
            if ($verbsOnly === false && $c->speechId === $this->verbSpeechId) {
                return false;
            }

            return ! in_array($c->lexicalEntryId, $excludeLexicalEntryIds, true);
        }));
    }
}
