<?php

namespace App\Jobs;

use App\Models\Language;
use App\Models\ModelBase;
use App\Models\Word;
use App\Repositories\SearchIndexRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSearchIndexCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ModelBase $entity;

    protected Word $keyword;

    protected Language $keywordLanguage;

    protected ?string $inflection;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ModelBase $entity, Word $keyword, Language $keywordLanguage, ?string $inflection = null)
    {
        $this->entity = $entity;
        $this->keyword = $keyword;
        $this->keywordLanguage = $keywordLanguage;
        $this->inflection = $inflection;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SearchIndexRepository $searchIndexRepository)
    {
        $searchIndexRepository->createIndex($this->entity, $this->keyword, $this->keywordLanguage, $this->inflection);
    }
}
