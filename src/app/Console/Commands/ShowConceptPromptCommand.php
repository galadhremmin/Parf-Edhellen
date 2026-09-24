<?php

namespace App\Console\Commands;

use App\Models\Sense;
use App\Repositories\SenseTermRepository;
use App\Services\Enumerations\SenseKind;
use App\Services\Senses\ConceptDecisionPrompt;
use App\Services\Senses\SenseClassifier;
use App\Services\Senses\SenseNormalizer;
use Illuminate\Console\Command;

class ShowConceptPromptCommand extends Command
{
    protected $signature = 'ed-senses:prompt
        {headword : A headword as a search would spell it, e.g. "light" or "to fall"}
        {--schema : Print the answer schema too}';

    protected $description = 'Prints the prompt that asks which meaning the senses under a headword have, for review. Calls no model.';

    public function handle(SenseTermRepository $senseTerms, SenseNormalizer $normalizer, SenseClassifier $classifier,
        ConceptDecisionPrompt $prompt): int
    {
        $key = $normalizer->normalize($this->argument('headword'))->first()?->key;

        $senses = $senseTerms->normalizable()
            ->with('terms')
            ->whereHas('terms', fn ($query) => $query->where('position', 0)->where('term_key', $key))
            ->whereDoesntHave('concepts')
            ->get()
            ->filter(fn (Sense $sense) => $classifier->classify($sense) === SenseKind::LEXICAL)
            ->values();

        if ($senses->isEmpty()) {
            $this->warn("No unassigned sense has the headword \"{$key}\".");

            return self::FAILURE;
        }

        $this->line($prompt->prompt(collect([$prompt->request($senses)])));

        if ($this->option('schema')) {
            $this->newLine();
            $this->line(json_encode($prompt->schema(), JSON_PRETTY_PRINT));
        }

        return self::SUCCESS;
    }
}
