<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshKeywordsCommand extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-keywords:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the keywords table.';

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