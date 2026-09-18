<?php

namespace App\Jobs;

use App\Interfaces\ISystemLanguageFactory;
use App\Models\LexicalEntry;
use App\Repositories\SearchIndexRepository;
use App\Repositories\WordRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Replaces a lexical entry's search index. Deleting and recreating in one job keeps the two in order on the
 * queue, and the entry is re-read when the job runs, so a job that runs late still indexes current data.
 */
class ProcessLexicalEntryReindex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(protected LexicalEntry $lexicalEntry) {}

    public function handle(SearchIndexRepository $searchIndexRepository, WordRepository $wordRepository,
        ISystemLanguageFactory $systemLanguageFactory): void
    {
        $lexicalEntry = $this->lexicalEntry;
        $searchIndexRepository->deleteAll($lexicalEntry);

        if ($lexicalEntry->is_deleted) {
            return;
        }

        $lexicalEntry->load('word', 'language', 'glosses', 'keywords.wordEntity', 'keywords.keyword_language', 'lexical_entry_inflections');
        $systemLanguage = $systemLanguageFactory->language();

        $glosses = $lexicalEntry->glosses->map(function ($t) {
            return $t->translation;
        });

        foreach ($lexicalEntry->keywords as $keyword) {
            if (! $glosses->contains($keyword->keyword)) {
                $keywordLanguage = $keyword->keyword_language ?: $systemLanguage;
                $searchIndexRepository->createIndex($lexicalEntry, $keyword->wordEntity, $keywordLanguage, $keyword->keyword);
            }
        }

        foreach ($glosses as $glossTranslation) {
            $glossWord = $wordRepository->save($glossTranslation, $lexicalEntry->account_id);
            $searchIndexRepository->createIndex($lexicalEntry, $glossWord, $systemLanguage);
        }

        foreach ($lexicalEntry->lexical_entry_inflections as $inflection) {
            $searchIndexRepository->createIndex($lexicalEntry, $lexicalEntry->word, $lexicalEntry->language, $inflection->word);
        }
    }
}
