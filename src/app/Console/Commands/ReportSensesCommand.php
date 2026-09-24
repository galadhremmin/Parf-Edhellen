<?php

namespace App\Console\Commands;

use App\Models\Sense;
use App\Models\SenseTerm;
use App\Repositories\SenseTermRepository;
use App\Services\Senses\NormalizedTerm;
use App\Services\Senses\NounLemmatizer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReportSensesCommand extends Command
{
    protected $signature = 'ed-senses:report
        {--top=25 : Largest headword groups to list}
        {--sample=25 : Random merged groups to list}';

    protected $description = 'Prints what sense normalisation groups together, for review: the largest groups, a random sample, and every plural it reduced.';

    public function handle(SenseTermRepository $repository, NounLemmatizer $lemmatizer): int
    {
        $headwords = SenseTerm::where('position', 0);
        $groups = (clone $headwords)->selectRaw('term_key, COUNT(*) AS senses')->groupBy('term_key');

        $this->info(sprintf('%d senses in %d headword groups; %d share a group with another sense.',
            (clone $headwords)->count(),
            (clone $headwords)->distinct()->count('term_key'),
            (clone $groups)->having('senses', '>', 1)->get()->sum('senses')));

        $this->newLine();
        $this->info('Largest groups');
        $this->printGroups((clone $groups)->orderByDesc('senses')->limit((int) $this->option('top'))->get());

        $this->newLine();
        $this->info('Random merged groups');
        $this->printGroups((clone $groups)->having('senses', '>', 1)->inRandomOrder()->limit((int) $this->option('sample'))->get());

        $this->newLine();
        $this->info('Plurals the lemmatiser reduced');
        $reduced = collect();
        $repository->normalizable()->chunkById(1000, function (Collection $senses) use ($repository, &$reduced) {
            $reduced = $reduced->merge($senses->flatMap(fn (Sense $sense) => $repository->normalize($sense)
                ->map(fn (NormalizedTerm $term) => $term->reducedFrom)
                ->filter()));
        });

        $reduced->countBy()->sortDesc()->each(fn (int $count, string $word) => $this->line(sprintf('  %s → %s (%d)', $word, $lemmatizer->lemmatize($word), $count)));

        return self::SUCCESS;
    }

    /**
     * One line per group: its key, how many senses share it, and a few of their spellings.
     */
    private function printGroups(Collection $groups): void
    {
        foreach ($groups as $group) {
            $terms = SenseTerm::where('position', 0)->where('term_key', $group->term_key)->distinct()->limit(8)->pluck('term');
            $this->line(sprintf('  %-20s %4d  %s', $group->term_key, $group->senses, $terms->implode(' | ')));
        }
    }
}
