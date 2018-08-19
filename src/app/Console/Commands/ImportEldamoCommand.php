<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Repositories\{
    GlossRepository,
    KeywordRepository
};
use App\Helpers\StringHelper;
use App\Models\{
    Gloss, 
    GlossDetail,
    GlossGroup,  
    Language, 
    Speech, 
    Translation
};

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

    private $_glossRepository;
    private $_keywordRepository;

    public function __construct(GlossRepository $glossRepository, KeywordRepository $keywordRepository)
    {
        parent::__construct();
        $this->_glossRepository = $glossRepository;
        $this->_keywordRepository = $keywordRepository;
    }

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

        $json = null;
        unset($json); // free memory as the json payload can be huge!

        if (! is_array($data)) {
            $this->error('Failed to process '.$path.'. Error: '.json_last_error_msg().' ('.json_last_error().').');
            $this->error('The JSON file must be saved using UTF-8 encoding (without BOM).');
            return;
        }

        $noOfItems = count($data);
        if ($noOfItems < 1) {
            $this->error('Data source is empty.');
        }

        // check whether the array contains IDs, or definitions
        $sum = array_sum(array_map(function ($item) {
            return is_numeric($item) ? 1 : 0;
        }, $data));

        if ($sum === $noOfItems) {
            $this->deleteDeprecated($data);
        } else {
            $this->import($data, $path);
        }
    }

    private function getEldamo()
    {
        return GlossGroup::where('name', 'Eldamo')->firstOrFail();
    }

    private function getNeologisms()
    {
        return GlossGroup::where('name', 'Neologism')->firstOrFail();
    }

    private function deleteDeprecated(array $ids)
    {
        sort($ids);
        
        $eldamo = $this->getEldamo();

        $glosses = $eldamo->glosses()
            ->where('is_latest', 1)
            ->select('id', 'external_id')
            ->get();

        $deleted = [];
        foreach ($glosses as $gloss) {
            if (in_array($gloss->external_id, $ids)) {
                continue;
            }

            $this->info('Deleting deprecated '.$gloss->external_id);

            // $this->_glossRepository->deleteGlossWithId($gloss->id);
            $deleted[] = $gloss->id;
        }

        $this->info('Deleted '.count($deleted).' deprecated glosses.');
    }

    private function import(array $data, string $path)
    {
        $speechMap = [
            '?'        => '?',
            'adj'      => 'adjective',
            'adv'      => 'adverb',
            'affix'    => 'affix',
            'article'  => 'article',
            'cardinal' => 'cardinal',
            'conj'     => 'conjunction',

            'collective-name' => 'collective name',
            'collective-noun' => 'collective noun',
            'family-name'     => 'family name',
            'fem-name'        => 'feminine name',
            
            'fraction'  => 'fraction',
            'grammar'   => 0, // not supported
            'infix'     => 'infix',
            'interj'    => 'interjection',
            'masc-name' => 'masculine name',
            'n'         => 'noun',
            'ordinal'   => 'ordinal',
            'particle'  => 'particle',
            'phoneme'   => 'phoneme',
            'phonetics' => 0,

            'phonetic-group' => 0,
            'phonetic-rule'  => 0,
            'phrase'         => 0,

            'place-name'  => 'place name',
            'pref'        => 'prefix',
            'prep'        => 'preposition',
            'pron'        => 'pronoun',
            'proper-name' => 'proper name',
            'radical'     => 'radical',
            'root'        => 'root',
            'text'        => 0,
            'suf'         => 'suffix',
            'vb'          => 'verb',

            'interj particle' => 0, // not supported
            'prep adv'        => 'preposition/adverb',
            'n adj'           => 'noun/adjective',
            'adj n'           => 'noun/adjective',
            'n adv'           => 'noun/adverb',
            'adv n'           => 'noun/adverb',
            'interj prep'     => 'interjection/preposition',
            'adv conj'        => 'adverb/conjunction',
            'adv adj'         => 'adverb/adjective',
            'adj adv'         => 'adverb/adjective',
            'adv prep'        => 'preposition and adverb',
            'conj adv'        => 'adverb/conjunction',
            'prep pref'       => 'preposition/prefix',
            'adv interj'      => 'adverb/interjection',
            'interj adv'      => 'adverb/interjection',
            'n vb'            => 'noun/verb'
        ];

        foreach ($speechMap as $key => $id) {
            if (is_numeric($id)) {
                continue;
            }

            $speech = Speech::where('name', $id)
                ->select('id')
                ->first();

            if (! $speech) {
                $speech = new Speech;
                $speech->name = $id;
                $speech->save();
            }

            $speechMap[$key] = $speech->id;
        }

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
            'eon'  => 0, // 'early old noldorin',
            'eoq'  => 0, // 'early old qenya',
            'ep'   => 'early primitive elvish',
            'eq'   => 'qenya', // <~~ deviation from "early quenya"!
            'et'   => 0, // 'early telerin',
            'fal'  => 0, // 'falathrin',
            'g'    => 'gnomish',
            'ilk'  => 'doriathrin', // <~~ deviation from "ilkorin"!
            'kh'   => 'khuzdul',
            'khx'  => 'khuzdul', // <~~ deviation from "Khuzdul, External"!
            'lem'  => 'lemberin',
            'ln'   => 'noldorin', // <~~ deviation from "late noldorin"!
            'lon'  => 'old noldorin', // <~~ deviation from "late old noldorin"!
            'mp'   => 'middle primitive elvish',
            'mq'   => 'qenya', // <~~ deviation from "middle quenya"!
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
            'tal'  => 'taliska',
            'teng' => 0, // _tengwar_ not supported
            'un'   => 'undetermined',
            'val'  => 'valarin',
            'van'  => 'quendya', // vanyarin
            'wes'  => 'westron',
            'wos'  => 'wose',
        ];

        $missing = [];
        foreach ($languageMap as $key => $id) {
            if (is_numeric($id)) {
                continue;
            }

            $language = Language::where('name', $id)
                ->select('id')
                ->first();

            if (! $language) {
                $missing[] = $id;
                continue;
            }

            $languageMap[$key] = $language->id;
        }

        $neoLanguageMap = [
            'ns' => $languageMap['s'],
            'nq' => $languageMap['q']
        ];

        if (! empty($missing)) {
            $this->error('Missing the languages: "'.implode('", "', $missing).'". Can\'t proceed.');
            return;
        }

        // Find the Eldamo gloss group
        $eldamo = $this->getEldamo();
        $neologism = $this->getNeologisms();

        $this->line('Data source: '.$path);
        $this->line('Eldamo ID: '.$eldamo->id.'.');
        $this->line('Updating '.count($data).' words.');

        // Find the user account for an existing gloss from Eldamo. 
        $existing = Gloss::where('gloss_group_id', $eldamo->id)
            ->select('account_id')
            ->firstOrFail();

        $c = 1;
        $ignored = [];
        foreach ($data as $t) {
            $noOfTranslations = count($t->translations);
            if ($noOfTranslations === 0 && (! empty($t->notes) || count($t->glossDetails) > 0)) {
                $t->translations[] = $t->word;
            }
            
            if ($noOfTranslations < 1) {
                $ignored[] = $t;
                continue;
            }

            $ot = Gloss::latest()
                ->notIndex()
                ->where('external_id', $t->id)
                ->whereIn('gloss_group_id', [$eldamo->id, $neologism->id])
                ->first();

            $found = $ot !== null;
            
            if (! $found) {
                $ot = new Gloss;
                $ot->external_id = $t->id;
                $ot->account_id = $existing->account_id;
            }

            $sense = $t->translations[0];
            $word = $t->word;

            $keywords = array_keys((array) $t->variations); // are automatically populated, anyway.

            $translations = array_map(function ($v) {
                return new Translation(['translation' => $v]);
            }, array_unique(array_map(function ($v) {
                return self::removeMark($v);
            }, $t->translations)));

            $inflections = array_unique(array_map(function ($v) {
                return mb_strtolower($v->entity->word, 'utf-8');
            }, $t->inflections));

            $details = array_map(function ($d) use($ot) {
                return new GlossDetail([
                    'category' => $d->category,
                    'text' => $d->text,
                    'account_id' => $ot->account_id
                ]);
            }, $t->glossDetails);

            for ($i = 1; $i <= count($details); $i += 1) {
                $details[$i - 1]->order = $i * 10;
            }

            $ot->is_uncertain = $t->mark === '?' ||
                                $t->mark === '*' ||
                                $t->mark === '‽' ||
                                $t->mark === '!' ||
                                $t->mark === '#';
            $ot->is_rejected  = $t->mark === '-';
            $ot->is_deleted   = 0;
            $ot->source       = implode('; ', $t->exactSources);

            if (isset($neoLanguageMap[$t->language])) {
                $ot->language_id    = $neoLanguageMap[$t->language];
                $ot->is_uncertain   = true;
                $ot->gloss_group_id = $neologism->id;
            } else {
                $ot->language_id    = $languageMap[$t->language] ?: null;
                $ot->gloss_group_id = $eldamo->id;
            }

            $ot->speech_id    = $speechMap[$t->speech] ?: null;
            $ot->comments     = $t->notes;

            if (! $ot->language_id) {
                $this->line($word.': '.$t->word.' ('.$t->id.')');
                $this->line("\tIgnored");
                
                continue;
            }

            try {
                $this->line($c.' '.$t->language.' '.$t->word);
                
                if ($found) {
                    $this->line("\tExisting ID: ".$ot->id);
                } else {
                    $this->line("\tExisting ID: null");
                }

                $this->line("\tGloss group: ".$ot->gloss_group_id);
                $this->line("\tClassification: ".($ot->is_uncertain ? 'uncertain' : 'regular'));
                $this->line("\tAttempting to save...");

                $t = $this->_glossRepository->saveGloss($word, $sense, $ot, $translations, $keywords, $details, false);
                
                $this->line("\tSuccess! ID: ".$t->id);
                $this->line("\tInflections: ".count($inflections));

                foreach ($inflections as $inflection) {
                    $this->line("\t- ".$inflection);
                    $this->_keywordRepository->createKeyword($t->word, $t->sense, $t, $inflection);
                }

            } catch (\Exception $ex) {
                $this->error("\tFailed due to an exception!");
                $this->error($ex->getMessage());
                $this->error($ex->getTraceAsString());
                dd([$word, $sense, $ot, $translations, $keywords]);
            }

            $c += 1;
        }

        foreach ($ignored as $t) {
            $this->line("- ".$t->id.": ".$t->word);
        }
        $this->line(count($ignored). ' ignored.');
    }

    private static function removeMark(string $word)
    {
        if (mb_strlen($word) < 1) {
            return $word;
        }

        if ($word[0] === '*' ||
            $word[0] === '#' ||
            $word[0] === '?') {
            return substr($word, 1); 
        }

        return $word;
    }
}
