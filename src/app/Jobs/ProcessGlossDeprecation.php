<?php

namespace App\Jobs;

use App\Repositories\LexicalEntryRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessGlossDeprecation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $ids;

    protected string $columnName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LexicalEntryRepository $glossRepository)
    {
        foreach ($this->ids as $id) {
            $glossRepository->deleteLexicalEntryWithId($id);
        }
    }
}
