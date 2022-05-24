<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\{
    GlossRepository,
    KeywordRepository
};
use App\Events\GlossEdited;

class ProcessGlossImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

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
    public function handle(GlossRepository $glossRepository, KeywordRepository $keywordRepository)
    {
        $data = & $this->data;

        $details      = $data['details'];
        $gloss        = $data['gloss'];
        $inflections  = $data['inflections'];
        $keywords     = $data['keywords'];
        $sense        = $data['sense'];
        $translations = $data['translations'];
        $word         = $data['word'];

        $changed = false;
        try {
            $importedGloss = $glossRepository->saveGloss($word, $sense, $gloss, $translations, $keywords, $details, $changed);
        } catch (\Exception $ex) {
            throw $ex;
        }

        if ($changed) {
            foreach ($inflections as $inflection) {
                $keyword = $keywordRepository->createKeyword($importedGloss->word, $importedGloss->sense, $importedGloss, $inflection->word);
            }
        }
    }
}
