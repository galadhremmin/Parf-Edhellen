<?php

namespace App\Subscribers;

use App\Events\LexicalEntryCreated;
use App\Events\LexicalEntryDestroyed;
use App\Events\LexicalEntryEdited;
use App\Events\LexicalEntryInflectionsCreated;
use App\Interfaces\ISystemLanguageFactory;
use App\Jobs\ProcessSearchIndexCreation;
use App\Models\LexicalEntry;
use App\Models\Language;
use App\Repositories\SearchIndexRepository;
use App\Repositories\WordRepository;
use Illuminate\Support\Collection;

class GlossIndexerSubscriber
{
    private SearchIndexRepository $_searchIndexRepository;

    private WordRepository $_wordRepository;

    private ?Language $_systemLanguage;

    public function __construct(SearchIndexRepository $searchIndexRepository, WordRepository $wordRepository,
        ISystemLanguageFactory $systemLanguageFactory)
    {
        $this->_searchIndexRepository = $searchIndexRepository;
        $this->_wordRepository = $wordRepository;
        $this->_systemLanguage = $systemLanguageFactory->language();
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe()
    {
        return [
            LexicalEntryCreated::class => 'onGlossCreated',
            LexicalEntryEdited::class => 'onGlossEdited',
            LexicalEntryInflectionsCreated::class => 'onGlossInflectionsCreated',
            LexicalEntryDestroyed::class => 'onGlossDestroyed',
        ];
    }

    public function onGlossCreated(LexicalEntryCreated $event): void
    {
        $this->update($event->lexicalEntry, $event->lexicalEntry->lexical_entry_inflections);
    }

    public function onGlossEdited(LexicalEntryEdited $event): void
    {
        $this->update($event->lexicalEntry, $event->lexicalEntry->lexical_entry_inflections);
    }

    public function onGlossInflectionsCreated(LexicalEntryInflectionsCreated $event): void
    {
        if ($event->incremental) {
            // Incremental are only adding to what's already there. This is useful when iteratively adding new indexes
            // although it comes with the downside that you have to manage the history (to avoid dead index links).
            $lexicalEntry = $event->lexicalEntry;
            foreach ($event->lexicalEntryInflections as $inflection) {
                ProcessSearchIndexCreation::dispatch($inflection->lexical_entry, $lexicalEntry->word, $lexicalEntry->language, //
                    $inflection->word)->onQueue('indexing');
            }
        } else {
            $this->update($event->lexicalEntry, $event->lexicalEntryInflections);
        }
    }

    public function onGlossDestroyed(LexicalEntryDestroyed $event): void
    {
        $this->delete($event->lexicalEntry);
    }

    private function update(LexicalEntry $lexicalEntry, Collection $inflections): void
    {
        $this->delete($lexicalEntry);

        $translations = $lexicalEntry->translations->map(function ($t) {
            return $t->translation;
        });

        foreach ($lexicalEntry->keywords as $keyword) {
            if (! $translations->contains($keyword->keyword)) {
                $keywordLanguage = $keyword->keyword_language ?: $this->_systemLanguage;
                ProcessSearchIndexCreation::dispatch($lexicalEntry, $keyword->wordEntity, $keywordLanguage, $keyword->keyword) //
                    ->onQueue('indexing');
            }
        }

        foreach ($translations as $translation) {
            $translationWord = $this->_wordRepository->save($translation, $lexicalEntry->account_id);
            ProcessSearchIndexCreation::dispatch($lexicalEntry, $translationWord, $this->_systemLanguage) //
                ->onQueue('indexing');
        }

        foreach ($inflections as $inflection) {
            ProcessSearchIndexCreation::dispatch($lexicalEntry, $lexicalEntry->word, $lexicalEntry->language, $inflection->word) //
                ->onQueue('indexing');
        }
    }

    private function delete(LexicalEntry $lexicalEntry): void
    {
        $this->_searchIndexRepository->deleteAll($lexicalEntry);
    }
}
