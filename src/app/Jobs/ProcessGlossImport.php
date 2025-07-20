<?php

namespace App\Jobs;

use App\Models\LexicalEntryInflection;
use App\Repositories\LexicalEntryInflectionRepository;
use App\Repositories\LexicalEntryRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessGlossImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $glossData)
    {
        $this->data = $glossData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LexicalEntryRepository $glossRepository, LexicalEntryInflectionRepository $glossInflectionRepository)
    {
        $data = &$this->data;

        $details = $data['details'];
        $gloss = $data['gloss'];
        $inflections = $data['inflections'];
        $keywords = $data['keywords'];
        $sense = $data['sense'];
        $translations = $data['translations'];
        $word = $data['word'];

        try {
            $glossEntity = $glossRepository->saveLexicalEntry($word, $sense, $gloss, $translations, $keywords, $details);
            $glossInflectionRepository->saveManyOnLexicalEntry($glossEntity, collect($inflections)->map(function ($i) use ($glossEntity) {
                return new LexicalEntryInflection(array_merge($i, [
                    'lexical_entry_id' => $glossEntity->id,
                ]));
            }));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
