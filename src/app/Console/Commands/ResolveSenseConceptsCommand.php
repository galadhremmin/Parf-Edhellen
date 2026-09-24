<?php

namespace App\Console\Commands;

use App\Models\Sense;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Repositories\SenseTermRepository;
use App\Services\Senses\SenseConceptResolver;
use Illuminate\Console\Command;

class ResolveSenseConceptsCommand extends Command
{
    protected $signature = 'ed-senses:resolve
        {--limit=100 : Senses to resolve, so a scheduled run cannot spend the whole Gemini budget}
        {--retry-unjudged : Also retry senses left waiting because no judge could be asked}
        {--pause=200 : Milliseconds to wait between senses}';

    protected $description = 'Resolves the concepts of senses that have none, asking Gemini only where the rules cannot tell. Meant for a scheduled catch-up run after contributions.';

    public function handle(SenseTermRepository $senseTerms, SenseConceptResolver $resolver): int
    {
        $outcomes = collect();

        $senses = $senseTerms->normalizable()
            ->with('terms')
            ->whereHas('terms')
            ->whereDoesntHave('concepts')
            ->when(! $this->option('retry-unjudged'),
                fn ($query) => $query->whereDoesntHave('concept_review'),
                fn ($query) => $query->whereDoesntHave('concept_review',
                    fn ($review) => $review->where('reason', '!=', ConceptReviewReason::NOT_JUDGED)))
            ->limit((int) $this->option('limit'))
            ->get();

        $senses->each(function (Sense $sense) use ($resolver, $outcomes) {
            $outcome = $resolver->resolve($sense);
            $outcomes->push($outcome->value);
            $this->line(sprintf('  %-28s sense %d: %s', $outcome->value, $sense->id, $sense->word->word));

            usleep((int) $this->option('pause') * 1000);
        });

        if ($outcomes->isEmpty()) {
            $this->info('Every sense in use has a concept or is waiting for an editor.');

            return self::SUCCESS;
        }

        $this->newLine();
        $outcomes->countBy()->sortDesc()->each(fn (int $count, string $outcome) => $this->line(sprintf('  %-28s %5d', $outcome, $count)));

        return self::SUCCESS;
    }
}
