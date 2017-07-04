<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Helpers\StringHelper;

class RefreshNormalizationCommand extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-normalization:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes all normalizations.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // TODO
    }
}