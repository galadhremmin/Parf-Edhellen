<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\{
    ModelBase,
    Keyword,
    Word
};
use App\Repositories\SearchIndexRepository;

class ProcessSearchIndexCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $entity;
    protected $keyword;
    protected $inflection;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ModelBase $entity, Word $keyword, string $inflection = null)
    {
        $this->entity     = $entity;
        $this->keyword    = $keyword;
        $this->inflection = $inflection;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SearchIndexRepository $searchIndexRepository)
    {
        $searchIndexRepository->createIndex($this->entity, $this->keyword, $this->inflection);
    }
}
