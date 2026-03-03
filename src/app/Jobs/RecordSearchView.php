<?php

namespace App\Jobs;

use App\Repositories\SearchDefinitionRepository;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordSearchView implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected SearchIndexSearchValue $searchValue,
        protected string $searchTerm
    ) {
        $this->searchTerm = trim(substr($searchTerm, 0, 128));
    }

    public function handle(SearchDefinitionRepository $repository): void
    {
        $repository->recordView($this->searchValue, $this->searchTerm);
    }
}
