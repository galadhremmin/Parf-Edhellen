<?php

namespace App\Console\Commands;

use App\Models\SearchViewEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneSearchViewEventsCommand extends Command
{
    protected $signature = 'ed:prune-search-view-events
        {--days=30 : Retention period in days}
        {--batch=10000 : Rows to delete per batch}
        {--sleep=100 : Milliseconds to sleep between batches}';

    protected $description = 'Prune search_view_events older than the retention period (batched to reduce lock impact)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $batchSize = (int) $this->option('batch');
        $sleepMs = (int) $this->option('sleep');

        $cutoff = Carbon::now()->subDays($days);
        $totalDeleted = 0;

        $this->info("Pruning search_view_events older than {$days} days (batch size: {$batchSize})...");

        do {
            $ids = SearchViewEvent::query()
                ->where('viewed_at', '<', $cutoff)
                ->limit($batchSize)
                ->pluck('id');

            $deleted = $ids->isEmpty()
                ? 0
                : SearchViewEvent::query()->whereIn('id', $ids)->delete();

            $totalDeleted += $deleted;

            if ($deleted > 0 && $sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        } while ($ids->count() >= $batchSize);

        $this->info("Deleted {$totalDeleted} old search view events.");

        return Command::SUCCESS;
    }
}
