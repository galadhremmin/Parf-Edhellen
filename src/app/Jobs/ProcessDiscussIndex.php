<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Models\ForumPost;
use App\Repositories\{
    SearchIndexRepository,
    WordRepository
};
use App\Interfaces\IIdentifiesPhrases;
use App\Interfaces\ISystemLanguageFactory;
use App\Models\Language;

class ProcessDiscussIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ForumPost $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SearchIndexRepository $searchIndexRepository, WordRepository $wordRepository, IIdentifiesPhrases $analyzer,
        ISystemLanguageFactory $systemLanguageFactory)
    {
        $post = $this->post;

        $searchIndexRepository->deleteAll($post);
        if (! $post->is_deleted) {
            try {
                $keywords = $analyzer->detectKeyPhrases($post->content);
                foreach ($keywords as $keyword) {
                    $word = $wordRepository->save($keyword, $post->account_id);
                    $searchIndexRepository->createIndex($post, $word, $systemLanguageFactory->language());
                }
            } catch (\Exception $ex) {
                // Errors can fail silently but make sure to log the error.
                Log::warning(sprintf('[DiscussPostIndexerSubscriber] Failed to detect key phrases process for %s.', $post->content));
            }
        }
    }
}
