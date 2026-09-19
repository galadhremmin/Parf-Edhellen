<?php

namespace App\Console\Commands;

use App\Repositories\SenseTermRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class NormalizeSensesCommand extends Command
{
    protected $signature = 'ed-senses:normalize
        {--from=0 : Resume from this sense ID}
        {--batch=500 : Senses per batch}
        {--pause=100 : Milliseconds to wait between batches}';

    protected $description = 'Rebuilds sense_terms for every sense in use. Safe to re-run; --from resumes an interrupted run.';

    public function handle(SenseTermRepository $repository): int
    {
        $started = microtime(true);
        $senses = 0;
        $terms = 0;

        $repository->normalizable()
            ->where('id', '>=', (int) $this->option('from'))
            ->chunkById((int) $this->option('batch'), function (Collection $batch) use ($repository, &$senses, &$terms) {
                $batchStarted = microtime(true);
                $senses += $batch->count();
                $terms += $repository->rebuild($batch);

                $this->line(sprintf('[%s] up to sense %d: %d senses, %d terms, last batch %d ms',
                    now()->format('H:i:s'), $batch->last()->id, $senses, $terms, (microtime(true) - $batchStarted) * 1000));

                usleep((int) $this->option('pause') * 1000);
            });

        $this->info(sprintf('Normalised %d senses into %d terms in %.1f s.', $senses, $terms, microtime(true) - $started));

        return self::SUCCESS;
    }
}
