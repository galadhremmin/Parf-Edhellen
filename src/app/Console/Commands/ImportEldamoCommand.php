<?php

namespace App\Console\Commands;

use App\Jobs\ProcessGlossDeprecation;
use App\Jobs\ProcessGlossImport;
use App\Models\Account;
use App\Models\Gloss;
use App\Models\GlossDetail;
use App\Models\GlossGroup;
use App\Models\Inflection;
use App\Models\Language;
use App\Models\Speech;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\Uuid;

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

    private $_languageMap;

    private $_speechMap;

    private $_inflectionMap;

    private $_glossGroups;

    /**
     * Import destination account.
     *
     * @var Account
     */
    private $_eldamoAccount;

    public function __construct()
    {
        parent::__construct();

        $this->_languageMap = null;
        $this->_speechMap = null;
        $this->_inflectionMap = Inflection::get()->keyBy('name');

        $this->_glossGroups = [];
        $this->_eldamoAccount = null;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initializeImport();

        $path = $this->argument('source');
        if (! file_exists($path)) {
            $this->error($path.' does not exist.');

            return;
        }

        // Create glossary by reading line by line (expecting jsonl file).
        if ($fp = fopen($path, 'r')) {
            try {
                $lineNumber = 1;
                while (! feof($fp)) {
                    $line = fgets($fp);
                    $entity = json_decode($line);
                    if (! $entity) {
                        throw new \Exception(sprintf('Line %d is corrupt - entity is null or undefined. JSON: %s', $lineNumber, $line));
                    }

                    if (isset($entity->allIds)) {
                        $this->line('allIds records: '.count($entity->allIds));
                        foreach ($this->_glossGroups as $_ => $group) {
                            $this->deleteAllBut($group, $entity->allIds);
                        }

                    } else {
                        $data = $this->createImportData($entity);
                        if (! $data['gloss']->language_id) {
                            $this->line(sprintf('Skipping %s (line %d): unsupported language %s.', $data['gloss']->external_id, $lineNumber, $entity['gloss']->language));

                        } else {
                            $this->validateImports($lineNumber, $data);
                            $this->import($lineNumber, $data);

                        }
                    }

                    $lineNumber += 1;
                    unset($data);
                }
            } finally {
                fclose($fp);
            }
        }
    }

    private function initializeImport()
    {
        try {
            $this->_glossGroups = [
                'default' => GlossGroup::where('name', 'Eldamo')->firstOrFail(),
                'adaptations' => GlossGroup::where('name', 'Eldamo - neologism/adaptations')->firstOrFail(),
                'fan invented' => GlossGroup::where('name', 'Eldamo - neologism/reconstructions')->firstOrFail(),
            ];
        } catch (ModelNotFoundException $ex) {
            throw new ModelNotFoundException('Failed to initialize import of Eldamo dataset because the required gloss groups do not exist.', $ex->getCode(), $ex);
        }

        // Find the user account for an existing gloss from Eldamo.
        $existing = Gloss::where('gloss_group_id', $this->_glossGroups['default']->id)
            ->select('account_id')
            ->firstOrFail();

        $this->_eldamoAccount = Account::findOrFail($existing->account_id); // TODO -- what exactly do we do if the account doesn't exist?
    }

    private function createImportData(object $data): array
    {
        $gloss = Gloss::firstOrNew(['external_id' => $data->gloss->id]);

        $gloss->account_id = $this->_eldamoAccount->id;
        $gloss->source = implode('; ', $data->sources);
        $gloss->comments = $data->gloss->notes;
        $gloss->is_deleted = 0;
        $gloss->is_uncertain = $data->gloss->mark === '?' ||
                                 $data->gloss->mark === '*' ||
                                 $data->gloss->mark === '‽' ||
                                 $data->gloss->mark === '!' ||
                                 $data->gloss->mark === '^' ||
                                 $data->gloss->mark === '⚠️';
        $gloss->is_rejected = $data->gloss->mark === '-';

        $groupName = 'default';
        switch ($data->gloss->mark) {
            case '!':
                // ! marks words that are pure neologisms: fabrications and inventions by authors other than Tolkien
                $groupName = 'fan invented';
                break;
            case '^':
                $groupName = 'adaptations';
                break;
            case '?':
                $gloss->label = 'Speculative';
                break;
            case '*':
                $gloss->label = 'Reconstructed';
                break;
                /*
                    For your purposes, you may not want to indicate "#" markers. Those are for items derived from well
                    known principles from attested forms, and are pretty "safe". I think marking those as "Derived" would
                    confuse your target audience of less knowledgeable students.
                case '#':
                    $gloss->label = 'Derived';
                    break;
                */
                /*
                    You may want to be careful about using deprecated tags and ⚠️ markers. Both are reflections
                    of my opinion only.
                case '⚠️':
                    $gloss->label = 'Not recommended';
                    break;
                */
        }
        $gloss->gloss_group_id = $this->_glossGroups[$groupName]->id;

        $this->setLanguage($data, $gloss);
        $this->setSpeech($data, $gloss);

        $word = $data->gloss->word;
        $details = $this->createDetails($data, $gloss);
        $inflections = $this->createInflections($data, $gloss);
        $keywords = $this->createKeywords($data, $gloss);
        $translations = $this->createTranslations($data, $gloss);
        $sense = $translations[0]->translation;

        return [
            'details' => $details,
            'gloss' => $gloss,
            'inflections' => $inflections,
            'keywords' => $keywords,
            'sense' => $sense,
            'translations' => $translations,
            'word' => $word,
        ];
    }

    private function validateImports(int $index, array $data): void
    {
        $details = $data['details'];
        $gloss = $data['gloss'];
        $inflections = $data['inflections'];
        $keywords = $data['keywords'];
        $sense = $data['sense'];
        $translations = $data['translations'];
        $word = $data['word'];

        $id = $gloss->external_id;

        // Validate details
        foreach ($details as $detail) {
            if (empty($detail->category)) {
                throw new \Exception(sprintf('Details title is empty for %d.', $id));
            }

            if (empty($detail->text)) {
                throw new \Exception(sprintf('Details body is empty for %d.', $id));
            }
        }

        if (! $gloss->account_id) {
            throw new \Exception(sprintf('Invalid account ID for %d.', $id));
        }

        if (! $gloss->language_id) {
            throw new \Exception(sprintf('Invalid language ID for %d.', $id));
        }

        foreach ($keywords as $keyword) {
            if (empty($keyword)) {
                throw new \Exception(sprintf('Invalid keyword for %d.', $id));
            }

            if (! is_string($keyword)) {
                throw new \Exception(sprintf('Invalid keyword "%s" for %d.', $keyword, $id));
            }
        }
    }

    private function deleteAllBut(GlossGroup $eldamoGroup, array $externalIds)
    {
        $ids = Gloss::active() //
            ->where('gloss_group_id', $eldamoGroup->id) //
            ->whereNotIn('external_id', $externalIds) //
            ->pluck('id') //
            ->toArray();

        if (count($ids) < 1) {
            // no clean-up necessary.
            return;
        }

        $this->line('!! dispatching deletion of '.count($ids).' entities');
        ProcessGlossDeprecation::dispatch($ids)->onQueue('import');
        $this->line('!! dispatched job');
    }

    private function import(int $index, array $data): void
    {
        $this->line($index.' - dispatching job');
        ProcessGlossImport::dispatch($data)->onQueue('import');
        $this->line($index.' - dispatched job');
    }

    private function setLanguage(object $data, Gloss $gloss): void
    {
        $languageMap = $this->getLanguageMap();
        $neoLanguageMap = $this->getNeoLanguageMap();

        if (isset($neoLanguageMap[$data->gloss->language])) {
            $gloss->language_id = $neoLanguageMap[$data->gloss->language];
            $gloss->is_uncertain = true;

        } elseif (isset($languageMap[$data->gloss->language])) {
            $gloss->language_id = $languageMap[$data->gloss->language];

        } else {
            $this->line("\tUnrecognised language for ".$data->gloss->id.': '.$data->gloss->language);
        }
    }

    private function setSpeech(object $data, Gloss $gloss): void
    {
        $speechMap = $this->getSpeechMap();
        $gloss->speech_id = isset($speechMap[$data->gloss->speech])
            ? ($speechMap[$data->gloss->speech] ?: null)
            : null;
    }

    private function createDetails(object $data, Gloss $gloss): array
    {
        $order = [
            'Variations' => 10,
            'Changes' => 20,
            'Derivatives' => 25,
            'Derivations' => 30,
            'Cognates' => 40,
            'Element in' => 50,
            'Elements' => 60,
            'Phonetic Developments' => 70,
            'Inflections' => 80,
        ];

        $details = array_map(function ($d) use ($order) {
            if (! isset($order[$d->title])) {
                throw new \Exception(sprintf('Unknown gloss detail category: %s.', $d->title));
            }

            return new GlossDetail([
                'category' => $d->title,
                'text' => $d->body,
                'order' => $order[$d->title],
                'type' => isset($d->type) ? $d->type : null,
            ]);
        }, $data->details);

        return $details;
    }

    private function createInflections(object $data, Gloss $gloss): array
    {
        $inflections = [];

        foreach ($data->inflections as $i) {
            $eligibleInflections = [];
            // In the event that the inflection does not exist in our database currently,
            // create a new set of inflections based on Eldamo's pattern. These will have
            // to be transitioned at some point in the future.
            if (! isset($this->_inflectionMap[$i->form])) {
                $inflection = new Inflection([
                    'name' => $i->form,
                    'group_name' => 'Eldamo compatibility (do not use)',
                ]);
                $inflection->save();
                $this->_inflectionMap[$inflection->name] = $inflection;
                $eligibleInflections[] = $inflection;
            } else {
                $inflection = $this->_inflectionMap[$i->form];
                $eligibleInflections = is_array($inflection) ? $inflection : [$inflection];
            }

            $uuid = Uuid::uuid4();
            foreach ($eligibleInflections as $inflection) {
                $inflection = [
                    'inflection_id' => $inflection->id,
                    'speech_id' => $gloss->speech_id ?: null,
                    'language_id' => $gloss->language_id,
                    'source' => $i->source,
                    'word' => $i->word,
                    'inflection_group_uuid' => $uuid,
                ];

                switch ($i->mark) {
                    case '-':
                        $inflection['is_rejected'] = true;
                        break;
                    case '!':
                    case '*':
                    case '?':
                        $inflection['is_neologism'] = true;
                        break;
                }

                $inflections[] = $inflection;
            }
        }

        return $inflections;
    }

    private function createKeywords(object $data, Gloss $gloss): array
    {
        // This is by design - the new EldamoParser implementation creates keywords for us.
        return $data->keywords;
    }

    private function createTranslations(object $data, Gloss $gloss): array
    {
        $translations = $data->gloss->translations;

        if ($translations === null) {
            // This is sometimes the case for names without translations, so assign it to the same word.
            $translations = [$data->gloss->word];
        }

        return array_map(function ($v) {
            return new Translation(['translation' => $v]);
        }, $translations);
    }

    private function getLanguageMap()
    {
        if (is_array($this->_languageMap)) {
            return $this->_languageMap;
        }

        // Establish a language mapping between Eldamo and Parf Edhellen. This map is based on
        // Eldamo's XSD (xs:simpleType name="language-type") for v0.5.5
        $languageMap = [
            'ad' => 'adunaic',
            'aq' => 'ancient quenya',
            'at' => 'ancient telerin',
            'av' => 'avarin',
            'bel' => 0, // _beleriandic_ not supported
            'bs' => 'black speech',
            'cir' => 0, // _cirth_ not supported
            'dan' => 'ossriandric', // <~~ deviation from "danian"!
            'dun' => 'dunlending',
            'dor' => 'doriathrin',
            'eas' => 'easterling',
            'ed' => 'edain',
            'edan' => 0,
            'eilk' => 'early ilkorin',
            'en' => 'early noldorin',
            'ent' => 'entish',
            'eon' => 0, // 'early old noldorin',
            'eoq' => 0, // 'early old qenya',
            'ep' => 'early primitive elvish',
            'eq' => 'early quenya',
            'et' => 'solosimpi', // 'early telerin',
            'fal' => 'doriathrin', // 'falathrin',
            'g' => 'gnomish',
            'ilk' => 'doriathrin', // <~~ deviation from "ilkorin"!
            'kh' => 'khuzdul',
            'khx' => 'khuzdul', // <~~ deviation from "Khuzdul, External"!
            'lem' => 'lemberin',
            'ln' => 'noldorin', // <~~ deviation from "late noldorin"!
            'lon' => 'old noldorin', // <~~ deviation from "late old noldorin"!
            'mp' => 'middle primitive elvish',
            'mq' => 'qenya', // <~~ deviation from "middle quenya"!
            'mt' => 'middle telerin',
            'n' => 'noldorin',
            'oss' => 'ossriandric',
            'p' => 'primitive elvish',
            'pad' => 'primitive adunaic',
            'nan' => 'nandorin',
            'ns' => 'north sindarin',
            'on' => 'old noldorin',
            'os' => 'old sindarin',
            'q' => 'quenya',
            'roh' => 'rohirric',
            's' => 'sindarin',
            'sar' => 0, // _sarati_ not supported
            'sol' => 'solosimpi',
            't' => 'telerin',
            'tal' => 'taliska',
            'teng' => 0, // _tengwar_ not supported
            'un' => 'undetermined',
            'val' => 'valarin',
            'van' => 'quendya', // vanyarin
            'wes' => 'westron',
            'wos' => 'wose',
            'maq' => 'middle ancient quenya',
            'norths' => 'north sindarin',
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

        if (! empty($missing)) {
            $this->error('Missing the languages: "'.implode('", "', $missing).'". Can\'t proceed.');
            exit;
        }

        $this->_languageMap = $languageMap;

        return $languageMap;
    }

    private function getNeoLanguageMap()
    {
        $languageMap = $this->getLanguageMap();

        return [
            'ns' => $languageMap['s'],
            'nq' => $languageMap['q'],
            'np' => $languageMap['p'],
        ];
    }

    private function getSpeechMap()
    {
        if (is_array($this->_speechMap)) {
            return $this->_speechMap;
        }

        $speechMap = [
            '?' => '?',
            'adj' => 'adjective',
            'adv' => 'adverb',
            'affix' => 'affix',
            'article' => 'article',
            'cardinal' => 'cardinal',
            'conj' => 'conjunction',

            'collective-name' => 'collective name',
            'collective-noun' => 'collective noun',
            'family-name' => 'family name',
            'fem-name' => 'feminine name',

            'fraction' => 'fraction',
            'grammar' => 0, // not supported
            'infix' => 'infix',
            'interj' => 'interjection',
            'masc-name' => 'masculine name',
            'n' => 'noun',
            'ordinal' => 'ordinal',
            'particle' => 'particle',
            'phoneme' => 'phoneme',
            'phonetics' => 0,

            'phonetic-group' => 0,
            'phonetic-rule' => 0,
            'phrase' => 0,

            'place-name' => 'place name',
            'pref' => 'prefix',
            'prep' => 'preposition',
            'pron' => 'pronoun',
            'proper-name' => 'proper name',
            'radical' => 'radical',
            'root' => 'root',
            'text' => 0,
            'suf' => 'suffix',
            'vb' => 'verb',

            'prep adv' => 'preposition/adverb',
            'n adj' => 'noun/adjective',
            'adj n' => 'noun/adjective',
            'n adv' => 'noun/adverb',
            'adv n' => 'noun/adverb',
            'interj prep' => 'interjection/preposition',
            'adv conj' => 'adverb/conjunction',
            'adv adj' => 'adverb/adjective',
            'adj adv' => 'adverb/adjective',
            'adv prep' => 'preposition and adverb',
            'conj adv' => 'adverb/conjunction',
            'prep pref' => 'preposition/prefix',
            'adv interj' => 'adverb/interjection',
            'interj adv' => 'adverb/interjection',
            'n vb' => 'noun/verb',

            'interj particle' => 0, // not supported
            'pron adv' => 0,
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

        $this->_speechMap = $speechMap;

        return $speechMap;
    }
}
