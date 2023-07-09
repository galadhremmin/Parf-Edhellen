<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

use App\Helpers\StringHelper;
use App\Jobs\{
    ProcessGlossDeprecation,
    ProcessGlossImport
};
use App\Models\{
    Account,
    Gloss,
    GlossDetail,
    GlossGroup,
    Inflection,
    Language, 
    Translation
};
use Ramsey\Uuid\Uuid;

class ImportDictionaryCommand extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-import:dictionary {source} {--gloss-group-id=} {--account-id=} {--external-id-prefix=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports definitions from any JSON file formatted using the ElfDict JSON standard.';

    /**
     * @var Collection
     */
    private $_languageMap;

    /**
     * @var GlossGroup
     */
    private $_glossGroup;
    /**
     * @var Account
     */
    private $_importAccount;
    private $_externalIdPrefix;

    public function __construct()
    {
        parent::__construct();

        $this->_languageMap = Language::get()->keyBy('name');

        $this->_glossGroup = null;
        $this->_importAccount = null;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('gloss-group-id') !== null) {
            $this->_glossGroup = GlossGroup::findOrFail(intval($this->option('gloss-group-id')));
        }

        if ($this->option('account-id') === null) {
            $this->error('--account-id: You must specify the ID for the account you\'d like to use for the import.');
            return;
        }
        $this->_importAccount = Account::findOrFail(intval($this->option('account-id')));

        if ($this->option('external-id-prefix') !== null) {
            $this->_externalIdPrefix = $this->option('external-id-prefix');
        }

        $path = $this->argument('source');
        if (! file_exists($path)) {
            $this->error($path.' does not exist.');
            return;
        }

        if (! $this->confirm(sprintf('Do you want to import %s with the account %s (%d)? Gloss group: %s. External ID prefix: %s.',
            $path, $this->_importAccount->nickname, $this->_importAccount->id, $this->_glossGroup ? $this->_glossGroup->name : 'none',
            $this->_externalIdPrefix ?? 'none'))) {
            return;
        }
        
        // Create glossary by reading line by line (expecting jsonl file).
        if ($fp = fopen($path, 'r')) {
            try {
                $lineNumber = 1;
                while (! feof($fp)) {
                    $line = fgets($fp);
                    $entity = json_decode($line, /* as_assoc_array: */ false);
                    if (! $entity) {
                        throw new \Exception(sprintf('Line %d is corrupt - entity is null or undefined. JSON: %s', $lineNumber, $line));
                    }

                    $data = $this->createImportData($entity);
                    if ($data == null) {
                        $this->line(sprintf('Skipping %s (line %d): unsupported entity.', $data['gloss']->external_id, $lineNumber));

                    } else {
                        $this->import($lineNumber, $data);
                    }

                    $lineNumber += 1;
                    unset($data);
                }
            } finally {
                fclose($fp);
            }
        }
    }

    function createImportData(\stdClass $entity): array
    {
        // This is just simplifying things. If it is null, initiate it as an empty collection to avoid having to check for nulls...
        if (! is_array($entity->references)) {
            $entity->references = [];
        }

        $externalId = $this->_externalIdPrefix.'_'.($entity->id !== null
            ? $entity->id
            : md5(sprintf('%s|%s|%s|%s', $entity->language, $entity->word, $entity->mark ?? 'NULL', implode(';', $entity->translations))) // generate an imperfect key  
        );

        if (! $this->_languageMap->has($entity->language)) {
            return null;
        }
        $languageId = $this->_languageMap[$entity->language]->id;
        $notes = $entity->notes;
        $source = implode('; ',
            array_unique(
                array_map(function ($reference) {
                    return $reference->source;
                }, array_filter($entity->references, function($reference) {
                    return ! empty($reference->source);
                }))
            )
        );

        $uncertain = $entity->mark === '*' || $entity->mark === '#';

        $gloss = Gloss::firstOrNew(['external_id' => $externalId]);
        $gloss->fill([
            'language_id'    => $languageId,
            'is_deleted'     => 0,
            'comments'       => $notes,
            'source'         => $source,
            'is_uncertain'   => $uncertain,
            'gloss_group_id' => $this->_glossGroup ? $this->_glossGroup->id : null,
            'account_id'     => $this->_importAccount->id
        ]);

        $languageMap = $this->_languageMap;
        $inflections = array_map(function ($reference) use($languageMap, $languageId) {
            $inflectionLanguageId = ($reference->language === null || ! $languageMap->has($reference->language))
                ? $languageId : $languageMap[$reference->language]->id;
            $uuid = Uuid::uuid4();
            return [
                /*
                TODO: Not presently supported
                'inflection_id'         => $inflection->id,
                'speech_id'             => $gloss->speech_id ?: null,
                */
                'language_id'           => $inflectionLanguageId,
                'source'                => $reference->source,
                'word'                  => $reference->inflect->word,
                'inflection_group_uuid' => $uuid
            ];
        }, array_filter($entity->references, function ($reference) {
            return $reference->inflect !== null;
        }));

        $keywords = array_map(function ($inflection) {
            return $inflection['word'];
        }, $inflections);

        $sense = $entity->preferredSense !== null ? $entity->preferredSense : $entity->translations[0];

        $translations = array_map(function ($translation) {
            return new Translation(['translation' => $translation]);
        }, $entity->translations);
        
        $word = $entity->word;

        return [
            'details' => [],
            'gloss' => $gloss,
            'inflections' => $inflections,
            'keywords' => $keywords,
            'sense' => $sense,
            'translations' => $translations,
            'word' => $word
        ];
    }

    function import($index, array $data)
    {
        $this->line($index.' - dispatching job');
        ProcessGlossImport::dispatch($data)->onQueue('import');
        $this->line($index.' - dispatched job');
    }
}
