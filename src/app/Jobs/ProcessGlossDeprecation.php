<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Gloss;
use App\Repositories\GlossRepository;

class ProcessGlossDeprecation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ids;
    protected $columnName;

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
    public function handle(GlossRepository $glossRepository)
    {
        foreach ($this->ids as $id) {
            $glossRepository->deleteGlossWithId($id);
        }
    }
}
