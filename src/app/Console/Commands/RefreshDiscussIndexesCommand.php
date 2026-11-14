<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDiscussIndex;
use App\Models\ForumPost;
use App\Models\Initialization\Morphs;
use App\Models\SearchKeyword;
use Illuminate\Console\Command;

class RefreshDiscussIndexesCommand extends Command
{
    // Characters StringHelper::cleanKeyPhrase() removes: Markdown and fulltext syntax that older indexing stored.
    private const DIRTY_KEYWORD_PATTERN = "[\n\r*_>#`~|@<\"()]|\\[|\\]|  ";

    protected $signature = 'ed-search:refresh-discuss
        {--all : Re-index every forum post, not only those with dirty keywords}
        {--seconds-per-post=1 : Minimum seconds per post, to stay under the analyzer\'s rate limit}';

    protected $description = 'Re-indexes forum posts through the key phrase analyzer (one request per post). Rerun to resume.';

    public function handle(): int
    {
        $query = ForumPost::where('is_deleted', 0);
        if (! $this->option('all')) {
            $query->whereIn('id', SearchKeyword::where('entity_name', Morphs::getAlias(ForumPost::class))
                ->whereRaw('keyword REGEXP ?', [self::DIRTY_KEYWORD_PATTERN])
                ->select('entity_id'));
        }

        $total = $query->count();
        $minimumSeconds = max(0.0, (float) $this->option('seconds-per-post'));
        $this->info(sprintf('[%s] Re-indexing %d forum posts.', now()->format('H:i:s'), $total));

        $done = 0;
        foreach ($query->orderBy('id')->lazyById(100) as $post) {
            $started = microtime(true);
            ProcessDiscussIndex::dispatchSync($post);
            $done++;

            if ($done % 25 === 0 || $done === $total) {
                $this->info(sprintf('[%s] %d of %d (last post %d)', now()->format('H:i:s'), $done, $total, $post->id));
            }

            $remaining = $minimumSeconds - (microtime(true) - $started);
            if ($remaining > 0) {
                usleep((int) ($remaining * 1_000_000));
            }
        }

        return self::SUCCESS;
    }
}
