<?php

namespace App\Jobs;

use App\Models\GlossInflection;
use App\Repositories\GlossInflectionRepository;
use App\Repositories\GlossRepository;
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
    public function handle(GlossRepository $glossRepository, GlossInflectionRepository $glossInflectionRepository)
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
            $glossEntity = $glossRepository->saveGloss($word, $sense, $gloss, $translations, $keywords, $details);
            $glossInflectionRepository->saveManyOnGloss($glossEntity, collect($inflections)->map(function ($i) use ($glossEntity) {
                return new GlossInflection(array_merge($i, [
                    'gloss_id' => $glossEntity->id,
                ]));
            }));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
