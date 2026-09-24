<?php

namespace App\Console\Commands;

use App\Models\Sense;
use App\Models\SenseTerm;
use App\Repositories\SenseTermRepository;
use App\Services\Senses\ConceptDecisionPrompt;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * Writes out the senses nobody has placed yet, as prompts anyone can answer: this session, another model, or a
 * person. The answers come back through `ed-senses:import-decisions`.
 */
class ExportSenseConceptQuestionsCommand extends Command
{
    private const SENSES_PER_QUERY = 500;

    protected $signature = 'ed-senses:export-questions
        {--groups=0 : Headword groups per file; 0 puts them all in one}
        {--directory=sense-backfill : Where under storage/app to write}';

    protected $description = 'Writes the senses waiting for a judgement as prompt files, with a manifest of what each one asked, for answering elsewhere.';

    public function handle(SenseTermRepository $senseTerms, ConceptDecisionPrompt $prompt): int
    {
        $directory = storage_path('app/'.$this->option('directory'));
        File::ensureDirectoryExists($directory.'/questions');
        File::put($directory.'/instructions.md', $this->instructions($prompt));

        $groups = $this->waitingGroups();
        $perFile = (int) $this->option('groups') ?: $groups->count();
        $manifest = [];
        $files = 0;
        $senses = 0;

        $fileCount = (int) ceil($groups->count() / $perFile);

        foreach ($groups->chunk($perFile) as $index => $groupsInFile) {
            $number = sprintf('%04d', $index + 1);
            $path = sprintf('%s/questions/%s.md', $directory, $number);
            $handle = fopen($path, 'w');
            fwrite($handle, sprintf("# Questions %s of %04d: %d headword groups. Answer as answers/%s.json.\n\n",
                $number, $fileCount, $groupsInFile->count(), $number));
            // every file carries the schema, so one file on its own is enough to answer
            fwrite($handle, $prompt->instructions()."\n\n".$this->answerFormat($prompt, $number)."\n");

            // one query per batch of groups, so a file of thousands doesn't hold every sense in memory
            foreach ($groupsInFile->chunk(self::SENSES_PER_QUERY / 2) as $batch) {
                $loaded = $senseTerms->normalizable()->with('terms')->whereIn('senses.id', $batch->flatten())->get()->keyBy('id');

                foreach ($batch as $termKey => $senseIds) {
                    $group = $senseIds->map(fn (int $senseId) => $loaded->get($senseId))->filter()->values();
                    if ($group->isEmpty()) {
                        continue;
                    }

                    $request = $prompt->request($group);
                    fwrite($handle, "\n".$prompt->renderGroup($request)."\n");

                    $manifest[] = [
                        'file' => $number,
                        'headword' => $termKey,
                        'candidate_synset_ids' => $request->candidates->pluck('synsetId')->all(),
                        'sense_ids' => $group->pluck('id')->all(),
                    ];
                    $senses += $group->count();
                }
            }

            fclose($handle);
            $files++;
            $this->line(sprintf('[%s] %s: %d KB', now()->format('H:i:s'), basename($path), File::size($path) / 1024));
        }

        File::put($directory.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info(sprintf('Wrote %d question file(s) covering %d senses in %d headword groups to %s.',
            $files, $senses, count($manifest), $directory));
        $this->line('Answer each file with JSON in the schema from instructions.md, saved as answers/<same number>.json.');

        return self::SUCCESS;
    }

    /**
     * How to answer, with the schema, for the end of a question file.
     */
    private function answerFormat(ConceptDecisionPrompt $prompt, string $number): string
    {
        return sprintf("Answer with one JSON object, and nothing else, following this schema:\n\n```json\n%s\n```\n\nSave it as answers/%s.json.",
            json_encode($prompt->schema(), JSON_PRETTY_PRINT), $number);
    }

    /**
     * The senses waiting for a judgement, grouped by headword, as the prompt groups them.
     *
     * @return Collection<string, Collection<int, int>> sense IDs by headword key
     */
    private function waitingGroups(): Collection
    {
        return SenseTerm::where('position', 0)
            ->whereIn('sense_id', Sense::whereHas('concept_review')->select('id'))
            ->orderBy('term_key')
            ->get(['sense_id', 'term_key'])
            ->groupBy('term_key')
            ->map(fn (Collection $terms) => $terms->pluck('sense_id'));
    }

    /**
     * The instructions file: what to do with the question files, and the schema the answers must follow.
     */
    private function instructions(ConceptDecisionPrompt $prompt): string
    {
        return implode("\n\n", [
            '# Placing senses in WordNet',
            'Each file under `questions/` asks about the senses of one dictionary, grouped by headword. Answer every'
                ."\nsense in the file. Save the answer as `answers/<the same number>.json`.",
            '## The question',
            $prompt->instructions(),
            '## The answer',
            "One JSON object per file, following this schema:\n\n```json\n"
                .json_encode($prompt->schema(), JSON_PRETTY_PRINT)."\n```",
            "Nothing else in the file: no prose around the JSON.\n",
        ]);
    }
}
