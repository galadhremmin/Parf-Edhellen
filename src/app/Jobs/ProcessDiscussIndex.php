<?php

namespace App\Jobs;

use App\Helpers\StringHelper;
use App\Interfaces\IIdentifiesPhrases;
use App\Interfaces\IMarkdownParser;
use App\Interfaces\ISystemLanguageFactory;
use App\Models\ForumPost;
use App\Repositories\SearchIndexRepository;
use App\Repositories\WordRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDiscussIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ForumPost $post;

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
        ISystemLanguageFactory $systemLanguageFactory, IMarkdownParser $markdownParser)
    {
        $post = $this->post;

        if ($post->is_deleted) {
            $searchIndexRepository->deleteAll($post);

            return;
        }

        try {
            $phrases = collect($analyzer->detectKeyPhrases($this->toPlainText($post->content, $markdownParser)))
                ->map(fn (string $phrase) => StringHelper::cleanKeyPhrase($phrase))
                ->filter()
                ->unique();

            // The analyzer returns nothing when it fails, so keep the old index rather than wipe it.
            if ($phrases->isEmpty()) {
                Log::warning(sprintf('[ProcessDiscussIndex] No key phrases detected for forum post %d; index left unchanged.', $post->id));

                return;
            }

            $searchIndexRepository->deleteAll($post);
            foreach ($phrases as $phrase) {
                $word = $wordRepository->save($phrase, $post->account_id);
                $searchIndexRepository->createIndex($post, $word, $systemLanguageFactory->language());
            }
        } catch (\Exception $ex) {
            // Errors can fail silently but make sure to log the error.
            Log::warning(sprintf('[ProcessDiscussIndex] Failed to index forum post %d: %s', $post->id, $ex->getMessage()));
        }
    }

    private function toPlainText(string $markdown, IMarkdownParser $markdownParser): string
    {
        // Space before each tag so paragraphs don't run together once the tags are gone.
        $html = str_replace('<', ' <', $markdownParser->parseMarkdown($markdown));

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
