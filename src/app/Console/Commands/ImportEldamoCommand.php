<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\{ Language, Translation, TranslationGroup };
use App\Repositories\TranslationRepository;

class ImportEldamoCommand extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-import:eldamo {source}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports definitions from eldamo.json. Transform the XML data source to JSON using EDEldamoParser.exe.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = $this->argument('source');
        if (! file_exists($path)) {
            $this->error($path.' does not exist.');
            return;
        }

        $json = trim(file_get_contents($path));
        $data = json_decode($json);
        unset($json); // free memory as the json payload can be huge!

        if (! $data) {
            $this->error('Failed to process '.$path.'. Error: '.json_last_error_msg().' ('.json_last_error().').');
            $this->error('The JSON file must be saved using UTF-8 encoding (without BOM).');
            return;
        }

        $repository = new TranslationRepository();

        // Establish a language mapping between Eldamo and Parf Edhellen. This map is based on
        // Eldamo's XSD (xs:simpleType name="language-type") for v0.5.5
        $languageMap = [
            'ad'   => 'adunaic',
            'aq'   => 'ancient quenya',
            'at'   => 'ancient telerin',
            'av'   => 'avarin',
            'bel'  => 0, // _beleriandic_ not supported
            'bs'   => 'black speech',
            'cir'  => 0, // _cirth_ not supported
            'dan'  => 'ossriandric', // <~~ deviation from "danian"!
            'dun'  => 'dunlending',
            'dor'  => 'doriathrin',
            'eas'  => 'easterling',
            'ed'   => 'edain',
            'edan' => 0,
            'eilk' => 'early ilkorin',
            'en'   => 'early noldorin',
            'ent'  => 'entish',
            'eon'  => 'early old noldorin',
            'eoq'  => 'early old qenya',
            'ep'   => 'early primitive elvish',
            'et'   => 'early telerin',
            'fal'  => 'falathrin',
            'g'    => 'gnomish',
            'ilk'  => 'ilkorin',
            'kh'   => 'khuzdul',
            'khx'  => 'khuzdul', // <~~ deviation from "Khuzdul, External"!
            'lem'  => 'lemberin',
            'ln'   => 'late noldorin',
            'lon'  => 'late old noldorin',
            'mp'   => 'middle primitive elvish',
            'mq'   => 'middle quenya',
            'mt'   => 'middle telerin',
            'n'    => 'noldorin',
            'oss'  => 'ossriandric',
            'p'    => 'primitive elvish',
            'pad'  => 'primitive adunaic',
            'nan'  => 'nandorin',
            'ns'   => 'north sindarin',
            'on'   => 'old noldorin',
            'os'   => 'old sindarin',
            'q'    => 'quenya',
            'roh'  => 'rohirric',
            's'    => 'sindarin',
            'sar'  => 0, // _sarati_ not supported
            'sol'  => 'solosimpi',
            't'    => 'telerin',
            'tal'  => 'talaski',
            'teng' => 0, // _tengwar_ not supported
            'un'   => 'undetermined',
            'val'  => 'valarin',
            'van'  => 'quendya', // vanyarin
            'wes'  => 'westron',
            'wos'  => 'wose'
        ];

        $missingLanguages = [];
        foreach ($languageMap as $key => $id) {
            if (is_numeric($id)) {
                continue;
            }

            $language = Language::where('name', $id)
                ->select('id')
                ->first();

            if (! $language) {
                $missingLanguages[] = $id;
                continue;
            }

            $languageMap[$key] = $language->id;
        }

        if (! empty($missingLanguages)) {
            $this->error('Missing the languages: "'.implode('", "', $missingLanguages).'". Can\'t proceed.');
            return;
        }

        // Find the Eldamo translation group
        $eldamo = TranslationGroup::where('name', 'Eldamo')->firstOrFail();
        $this->line('Eldamo ID: '.$eldamo->id.'.');
        $this->line('Updating '.count($data).' words.');

        // Find the user account for an existing translation from Eldamo. 
        $existing = $account = Translation::where('translation_group_id', $eldamo->id)
            ->select('account_id')
            ->firstOrFail();

        $c = 1;
        foreach ($data as $t) {
            $ot = Translation::latest()
                ->notIndex()
                ->where('external_id', $t->id)
                ->where('translation_group_id', $eldamo->id)
                ->first();

            $found = $ot !== null;
            $this->line($c.' '.$t->word.': '.($found ? $ot->id : 'new'));
            
            if (! $found) {
                $ot = new Translation;
                $ot->external_id = $t->id;
                $ot->translation_group_id = $eldamo->id;
                $ot->account_id = $existing->account_id;
            }

            $word = $t->word;
            $sense = $t->category ?: $t->gloss[0];
            $keywords = array_slice($t->gloss, 1);
            
            $ot->is_uncertain = $t->mark === '?' ||
                                $t->mark === '*' ||
                                $t->mark === '‽' ||
                                $t->mark === '!' ||
                                $t->mark === '#';
            $ot->is_rejected  = $t->mark === '-';
            $ot->is_deleted   = 0;
            
            $ot->source       = implode('; ', $t->sources);
            $ot->translation  = $t->gloss[0];

            $this->line(print_r($ot->toArray(), true));
            break;

            $c += 1;
        }
    }
}
