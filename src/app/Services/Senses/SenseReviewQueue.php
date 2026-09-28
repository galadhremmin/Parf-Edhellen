<?php

namespace App\Services\Senses;

use App\Helpers\LinkHelper;
use App\Models\Gloss;
use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\SenseConceptReview;
use App\Repositories\Enumerations\ConceptReviewReason;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * The senses no rule and no judge could place, waiting for an editor. Most used first: deciding a sense a hundred
 * entries are glossed with moves more of the dictionary than deciding a singleton does.
 */
class SenseReviewQueue
{
    // enough entries to show how a sense is used without burying the meanings beneath them
    private const USAGES = 12;

    public function __construct(
        protected readonly ConceptDecisionPrompt $_prompt,
        protected readonly LinkHelper $_link,
    ) {}

    /**
     * How many senses are waiting, by the reason they are.
     *
     * @return Collection<string, int>
     */
    public function summary(): Collection
    {
        return SenseConceptReview::query()
            ->selectRaw('reason, COUNT(*) AS senses')
            ->groupBy('reason')
            ->pluck('senses', 'reason');
    }

    /**
     * How many senses are waiting, of the kind asked for.
     */
    public function count(?ConceptReviewReason $reason = null): int
    {
        return $this->waiting($reason)->count();
    }

    /**
     * The next sense to decide, passing over the ones the editor has already skipped.
     *
     * @param  int[]  $skip  senses the editor has passed over in this sitting
     */
    public function next(array $skip = [], ?ConceptReviewReason $reason = null): ?SenseReviewItem
    {
        $review = $this->waiting($reason)
            ->when($skip !== [], fn (Builder $query) => $query->whereNotIn('sense_id', $skip))
            ->first();

        return $review === null ? null : $this->describe($review);
    }

    /**
     * One waiting sense by ID, or null when nobody is waiting on it any more.
     */
    public function item(int $senseId): ?SenseReviewItem
    {
        $review = $this->waiting()->where('sense_id', $senseId)->first();

        return $review === null ? null : $this->describe($review);
    }

    /**
     * The senses waiting, the most used first.
     */
    private function waiting(?ConceptReviewReason $reason = null): Builder
    {
        return SenseConceptReview::query()
            ->select('sense_concept_reviews.*')
            ->selectSub(LexicalEntry::active()
                ->selectRaw('COUNT(*)')
                ->whereColumn('lexical_entries.sense_id', 'sense_concept_reviews.sense_id'), 'entries')
            ->when($reason !== null, fn (Builder $query) => $query->where('reason', $reason))
            ->orderByDesc('entries')
            ->orderBy('sense_id');
    }

    /**
     * Everything an editor needs to see, from the same request the judges were given, so the page offers the meanings
     * the model was offered.
     */
    private function describe(SenseConceptReview $review): SenseReviewItem
    {
        $sense = Sense::with(['word', 'terms', 'lexical_entries'])->findOrFail($review->sense_id);

        return new SenseReviewItem(
            $sense->id,
            $sense->word->word,
            $review->reason,
            $review->detail,
            $review->confidence,
            (int) ($review->entries ?? 0),
            $this->_prompt->request(collect([$sense])),
            $this->usages($sense),
        );
    }

    /**
     * The entries glossed with the sense, each linking to where it is read in the dictionary. The evidence the judges
     * were given is prose; an editor wants to follow it.
     *
     * @return Collection<int, SenseReviewUsage>
     */
    private function usages(Sense $sense): Collection
    {
        return LexicalEntry::active()
            ->where('sense_id', $sense->id)
            ->with(['word:id,word', 'language:id,name', 'speech:id,name', 'glosses:id,lexical_entry_id,translation'])
            ->limit(self::USAGES)
            ->get()
            ->map(fn (LexicalEntry $entry) => new SenseReviewUsage(
                $entry->id,
                // a few entries mark a word up ("ar <u>i</u> nánë"), which is nothing but noise in a list of links
                strip_tags($entry->word->word),
                $entry->language?->name ?? 'unknown language',
                $entry->speech?->name,
                $entry->glosses
                    ->map(fn (Gloss $gloss) => trim($gloss->translation))
                    // a gloss that only restates the sense adds nothing to what is already on the page
                    ->reject(fn (string $gloss) => mb_strtolower($gloss) === mb_strtolower(trim($sense->word->word)))
                    ->unique()
                    ->implode('; '),
                $this->_link->lexicalEntry($entry->id),
            ));
    }
}
