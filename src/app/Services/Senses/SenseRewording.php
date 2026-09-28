<?php

namespace App\Services\Senses;

use App\Helpers\StringHelper;
use App\Models\Account;
use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Repositories\ConceptAuditRepository;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\LexicalEntryRepository;
use App\Repositories\ValueObjects\ConceptAuditEntry;
use App\Services\Enumerations\ConceptOutcome;
use Illuminate\Support\Facades\DB;

/**
 * Corrects the English a group of entries is glossed with. Some senses cannot be placed because they are not meanings
 * at all but lexicographers' shorthand: "card" over fourteen Sindarin numerals is the abbreviation for "cardinal",
 * and no amount of judging will make a playing card of it. Rewording moves every entry to the sense it should have
 * had, and the ordinary machinery then places that sense on its own.
 */
class SenseRewording
{
    // the queue's largest sense holds a few dozen entries; rewriting hundreds in a request belongs in a command
    private const MAX_ENTRIES = 100;

    public function __construct(
        protected readonly LexicalEntryRepository $_lexicalEntries,
        protected readonly ConceptRepository $_concepts,
        protected readonly ConceptAuditRepository $_audit,
    ) {}

    /**
     * Moves every entry glossed with the sense to the given wording, and takes the old one off the editors' list.
     *
     * @param  Sense  $sense  with `word` loaded
     */
    public function reword(Sense $sense, string $wording, Account $account): SenseRewordingResult
    {
        $wording = StringHelper::toLower(trim($wording));
        if ($wording === '' || $wording === StringHelper::toLower(trim($sense->word->word))) {
            return SenseRewordingResult::refused('That is the wording these entries already have.');
        }

        $entries = LexicalEntry::active()->where('sense_id', $sense->id)->get();
        if ($entries->isEmpty()) {
            return SenseRewordingResult::refused('No entries are glossed with this sense any more.');
        }

        if ($entries->count() > self::MAX_ENTRIES) {
            return SenseRewordingResult::refused(sprintf(
                'This sense has %d entries, more than the %d a single rewording may move.',
                $entries->count(), self::MAX_ENTRIES));
        }

        $senseId = DB::transaction(function () use ($entries, $wording) {
            $moved = 0;
            foreach ($entries as $entry) {
                // the repository's own path: it finds or creates the sense, rewrites the keywords, versions the entry
                // and raises the events that normalise and place what it moved to
                $moved = $this->_lexicalEntries->saveSense($entry, $wording)->sense_id;
            }

            return $moved;
        });

        // nothing is glossed with the old wording now, so there is nothing left to decide about it
        $this->_concepts->clearReview($sense->id);
        $this->_audit->record(new ConceptAuditEntry($sense, ConceptSource::EDITOR,
            ConceptApplication::of(ConceptOutcome::REWORDED), 'editor',
            decision: new ConceptDecision($sense->id, [], $wording, null, 100), accountId: $account->id));

        return SenseRewordingResult::moved($entries->count(), $wording, $senseId);
    }
}
