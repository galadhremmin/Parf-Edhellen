<?php

namespace App\Jobs;

use App\Helpers\SentenceBuilders\SentenceBuilder;
use App\Helpers\StringHelper;
use App\Models\Sentence;
use App\Repositories\SearchIndexRepository;
use App\Repositories\WordRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Replaces a phrase's search index. Deleting and recreating in one job keeps the two in order on the queue,
 * and the phrase is re-read when the job runs, so a job that runs late still indexes current data.
 */
class ProcessSentenceReindex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(protected Sentence $sentence) {}

    public function handle(SearchIndexRepository $searchIndexRepository, WordRepository $wordRepository): void
    {
        $sentence = $this->sentence;
        $sentence->load('sentence_fragments.lexical_entry.word', 'sentence_fragments.lexical_entry.language', 'language');

        foreach ($sentence->sentence_fragments as $fragment) {
            $searchIndexRepository->deleteAll($fragment);

            if ($fragment->type !== SentenceBuilder::TYPE_CODE_WORD) {
                continue;
            }

            $entry = $fragment->lexical_entry;

            if ($entry !== null) {
                // Indexed as an inflection of the entry, so results read "car- -> cared".
                $inflection = StringHelper::toLower($fragment->fragment);

                if ($inflection === StringHelper::toLower($entry->word->word)) {
                    $inflection = null;
                }

                // Fragments have no language of their own, so the phrase's language goes in explicitly.
                $searchIndexRepository->createIndex($fragment, $entry->word, $entry->language, $inflection, $sentence->language);

                continue;
            }

            // A word need not be linked to a dictionary entry -- the contribution form allows it, and an
            // imported phrase may quote a word we don't have. Index it under itself so the phrase is still
            // findable by that word.
            $fragmentString = StringHelper::toLower(StringHelper::clean($fragment->fragment));

            if (empty($fragmentString)) {
                continue;
            }

            $searchIndexRepository->createIndex(
                $fragment,
                $wordRepository->save($fragmentString, $sentence->account_id),
                $sentence->language,
                null,
                $sentence->language
            );
        }
    }
}
