<?php

namespace App\Console\Commands;

use App\Helpers\SentenceBuilders\SentenceBuilder;
use App\Helpers\TengwarTranscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TranscribeSentenceTengwarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-import:transcribe-tengwar {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transcribes phrase fragments that have no tengwar, as the phrase form would have.';

    public function __construct(private TengwarTranscriber $_transcriber)
    {
        parent::__construct();
    }

    public function handle()
    {
        $fragments = DB::table('sentence_fragments as f')
            ->join('sentences as s', 's.id', '=', 'f.sentence_id')
            ->join('languages as l', 'l.id', '=', 's.language_id')
            ->whereNull('f.deleted_at')
            ->whereNull('s.deleted_at')
            // A new line has nothing to transcribe; the form leaves its tengwar empty too.
            ->where('f.type', '<>', SentenceBuilder::TYPE_CODE_NEWLINE)
            ->where('f.fragment', '<>', '')
            // A connector has no glyph, so its empty tengwar is finished rather than missing.
            ->where(fn ($q) => $q->whereNull('f.tengwar')->orWhere(fn ($q) => $q
                ->where('f.tengwar', '')
                ->where('f.type', '<>', SentenceBuilder::TYPE_CODE_WORD_CONNEXION)))
            ->orderBy('f.id')
            ->get(['f.id', 'f.fragment', 'l.name as language', 'l.tengwar_mode']);

        $this->line('!! '.$fragments->count().' fragments without tengwar');

        $untranscribable = $fragments->whereNull('tengwar_mode');
        foreach ($untranscribable->groupBy('language') as $language => $group) {
            $this->line(sprintf('!! %d in %s, which has no tengwar mode', $group->count(), $language));
        }

        $fragments = $fragments->whereNotNull('tengwar_mode')->values();
        $tengwar = $this->_transcriber->transcribe($fragments->map(fn ($f) => [
            'mode' => $f->tengwar_mode,
            'text' => $f->fragment,
        ])->all());

        $updated = 0;
        foreach ($fragments as $i => $fragment) {
            // Glaemscribe drops what it has no glyph for (a hyphen, an apostrophe), which leaves
            // an empty string: the same thing the form stores.
            if ($tengwar[$i] === null) {
                $this->line(sprintf('%d - "%s": no transcription', $fragment->id, $fragment->fragment));

                continue;
            }

            $updated += 1;
            if (! $this->option('dry-run')) {
                DB::table('sentence_fragments')->where('id', $fragment->id)->update(['tengwar' => $tengwar[$i]]);
            }
        }

        $this->line('!! transcribed '.$updated.' fragments'.($this->option('dry-run') ? ' (dry run - nothing was written)' : ''));

        return 0;
    }
}
