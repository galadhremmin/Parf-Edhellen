<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use App\Interfaces\IIdentifiesPhrases;
use App\Models\{
    ForumPost
};

class RefreshDiscussIndexesCommand extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-search:refresh-discuss';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes all discuss indexes. Depends on Amazon Comprehend.';

    public function __construct(IIdentifiesPhrases $analyzer)
    {
        parent::__construct();
        $this->_analyzer = $analyzer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (ForumPost::cursor() as $post) {
            $keywords = $this->_analyzer->detectKeyPhrases($post->content);

            $this->info('===============================================================================================');
            $this->info($post->content);
            $this->info('');
            if (count($keywords) > 0) {
                $this->info($keywords);
            } else {
                $this->info('> No keywords.');
            }

            // TODO: Associate the keywords with the ForumPost entity.

            // Deliberately wait 10 milliseconds per post. Rather be slow and steady, than fast and sorry.
            usleep(10 * 1000);
        }
    }
}
