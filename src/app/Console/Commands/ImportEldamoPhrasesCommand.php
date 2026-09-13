<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\MapsEldamoLanguages;
use App\Events\SentenceEdited;
use App\Helpers\SentenceBuilders\SentenceBuilder;
use App\Helpers\TengwarTranscriber;
use App\Models\Account;
use App\Models\Inflection;
use App\Models\Language;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryGroup;
use App\Models\LexicalEntryInflection;
use App\Models\Sentence;
use App\Models\SentenceFragment;
use App\Models\SentenceTranslation;
use App\Repositories\SentenceRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportEldamoPhrasesCommand extends Command
{
    use MapsEldamoLanguages;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-import:eldamo-phrases {source} {--dry-run} {--report=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports grammatically analysed phrases from the JSONL file produced by ed-import:eldamo-phrases-extract.';

    /**
     * Eldamo's grammatical vocabulary, mapped onto Parf Edhellen's inflection names.
     *
     * Eldamo writes a compound form as a space-delimited list of these tokens ("past 1st-sg"),
     * which is exactly how Parf Edhellen models it too: several inflections sharing one group.
     * Tokens absent from this map have no counterpart here -- "elided", "complete", "adjectival",
     * "definite", "nominative", "past-active-participle" and "with-2nd-sg-familiar-object" -- and
     * are dropped rather than invented, so as not to grow the "Eldamo compatibility (do not use)"
     * group the glossary importer leaves behind.
     */
    const INFLECTION_MAP = [
        '1st-pl' => '1st plural',
        '1st-pl-exclusive' => '1st plural exclusive',
        '1st-pl-exclusive-poss' => '1st plural exclusive possessive',
        '1st-pl-inclusive-poss' => '1st plural inclusive possessive',
        '1st-sg' => '1st singular',
        '1st-sg-poss' => '1st singular possessive',
        '2nd-sg-polite' => '2nd singular polite',
        '2nd-sg-polite-poss' => '2nd singular polite possessive',
        '3rd-pl-masc' => '3rd plural masculine',
        '3rd-pl-poss' => '3rd plural possessive',
        '3rd-sg-fem' => '3rd singular feminine',
        '3rd-sg-neut' => '3rd singular neuter',
        '3rd-sg-poss' => '3rd singular possessive',
        'ablative' => 'ablative',
        'active-participle' => 'active participle',
        'allative' => 'allative',
        'aorist' => 'aorist',
        'class-plural' => 'class plural',
        'dative' => 'dative',
        'dual' => 'dual',
        'future' => 'future',
        'genitive' => 'genitive',
        'gerund' => 'gerund',
        'imperative' => 'imperative',
        'infinitive' => 'infinitive',
        'instrumental' => 'instrumental',
        'intensive' => 'intensive',
        'locative' => 'locative',
        'mixed-mutation' => 'mixed mutation',
        'nasal-mutation' => 'nasal mutation',
        'objective' => 'objective',
        'old-genitive' => 'old genitive',
        'partitive-plural' => 'partitive plural',
        'passive' => 'passive',
        'passive-participle' => 'passive participle',
        'past' => 'past',
        'perfect' => 'perfect',
        'plural' => 'plural',
        'possessive' => 'possessive',
        'present' => 'present',
        'similative' => 'similative',
        'soft-mutation' => 'soft mutation',
        'stem' => 'stem',
        'subjunctive' => 'subjunctive',
        'with-pl-object' => 'with plural object',
        'with-sg-object' => 'with singular object',
    ];

    /**
     * Characters that bind two words into one written token. Parf Edhellen stores them as their own
     * fragment (TYPE_CODE_WORD_CONNEXION), which is why "i-Estel" is three fragments, not one.
     */
    const CONNECTORS = ['-', '·', '’', '\''];

    private SentenceRepository $_sentenceRepository;

    private Account $_account;

    /** @var array<string, int> inflection name to ID */
    private array $_inflectionIds = [];

    /** @var array<string, ?object> Eldamo page ID to lexical entry */
    private array $_lexicalEntries = [];

    /** @var array<int, array<string, mixed>> */
    private array $_sentences = [];

    /** @var array<string, SentenceTranslation> translations written by this run, by slot */
    private array $_translations = [];

    /** @var array<string, true> slots in extended since they were last written */
    private array $_extendedSlots = [];

    /** @var array<int, array<string, string>> */
    private array $_report = [];

    /** @var array<string, int> */
    private array $_stats = [
        'texts created' => 0,
        'collections created' => 0,
        'texts enriched' => 0,
        'phrases created' => 0,
        'lines enriched' => 0,
        'unchanged' => 0,
        'skipped' => 0,
        'not worth creating' => 0,
        'fragments linked' => 0,
        'inflections added' => 0,
        'inflections replaced' => 0,
        'translations added' => 0,
        'translations extended' => 0,
    ];

    private TengwarTranscriber $_transcriber;

    public function __construct(SentenceRepository $sentenceRepository, TengwarTranscriber $transcriber)
    {
        parent::__construct();

        $this->_sentenceRepository = $sentenceRepository;
        $this->_transcriber = $transcriber;
    }

    public function handle()
    {
        $path = $this->argument('source');
        if (! file_exists($path)) {
            $this->error($path.' does not exist.');

            return 1;
        }

        $this->initializeImport();

        if ($fp = fopen($path, 'r')) {
            try {
                $lineNumber = 1;
                while (! feof($fp)) {
                    $line = trim(fgets($fp) ?: '');
                    if ($line === '') {
                        continue;
                    }

                    $unit = json_decode($line, true);
                    if (! $unit) {
                        throw new \Exception(sprintf('Line %d is corrupt - entity is null or undefined. JSON: %s', $lineNumber, $line));
                    }

                    $this->importUnit($lineNumber, $unit);
                    $lineNumber += 1;
                }
            } finally {
                fclose($fp);
            }
        }

        $this->writeReport();

        $this->line('');
        foreach ($this->_stats as $label => $value) {
            $this->line(sprintf('!! %-22s %d', $label, $value));
        }

        if ($this->option('dry-run')) {
            $this->line('!! dry run - nothing was written');
        }

        return 0;
    }

    private function initializeImport(): void
    {
        try {
            $group = LexicalEntryGroup::where('name', 'Eldamo')->firstOrFail();
        } catch (ModelNotFoundException $ex) {
            throw new ModelNotFoundException('Failed to initialize the phrase import because the Eldamo gloss group does not exist.', $ex->getCode(), $ex);
        }

        // The same convention the glossary importer follows: the account that owns the existing
        // Eldamo entries owns the phrases too.
        $existing = LexicalEntry::where('lexical_entry_group_id', $group->id)
            ->select('account_id')
            ->firstOrFail();

        $this->_account = Account::findOrFail($existing->account_id);

        // SentenceRepository fires SentenceEdited with the authenticated user's ID, so an
        // unauthenticated console run would fatal on the update path.
        Auth::login($this->_account);

        $this->_inflectionIds = Inflection::whereIn('name', array_values(self::INFLECTION_MAP))
            // The "Eldamo compatibility (do not use)" group holds tokens of the same name; the
            // curated inflections are the ones we want.
            ->where('group_name', 'not like', 'Eldamo%')
            ->pluck('id', 'name')
            ->toArray();

        $this->loadSentences();
    }

    /**
     * Every phrase, with its word fragments flattened into a normalised token list. There are fewer
     * than a hundred, and each one has to be scanned for every incoming phrase, so they are held in
     * memory rather than queried per match.
     */
    private function loadSentences(): void
    {
        $sentences = Sentence::whereNull('deleted_at')->get();
        $fragments = SentenceFragment::whereNull('deleted_at')
            ->orderBy('sentence_id')
            ->orderBy('order')
            ->get();

        $byId = [];
        foreach ($sentences as $sentence) {
            $byId[$sentence->id] = [
                'sentence' => $sentence,
                'fragments' => [],
                'tokens' => [],
            ];
        }

        foreach ($fragments as $fragment) {
            if ($fragment->type !== SentenceBuilder::TYPE_CODE_WORD || ! isset($byId[$fragment->sentence_id])) {
                continue;
            }

            $token = $this->normalize($fragment->fragment);
            if ($token === '') {
                continue;
            }

            $byId[$fragment->sentence_id]['fragments'][] = $fragment;
            $byId[$fragment->sentence_id]['tokens'][] = $token;
        }

        $this->_sentences = $byId;
    }

    /**
     * Imports one text, collection or standalone phrase.
     *
     * Its lines are first looked for in the phrases we already have, where a poem is often stored
     * whole: if any are found, the unit exists here and its lines enrich what is there. Otherwise it
     * is new, and becomes a phrase of its own -- one paragraph per line.
     *
     * @param  array<string, mixed>  $unit
     */
    private function importUnit(int $lineNumber, array $unit): void
    {
        $languageId = $this->resolveLanguageId($unit);
        if (! $languageId) {
            $this->skip($lineNumber, $unit, 'unsupported language '.$unit['language']);

            return;
        }

        // Created by an earlier run. Nothing to do: enrichment only ever fills gaps, and a phrase we
        // authored has none.
        if (Sentence::whereNull('deleted_at')->where('external_id', $unit['external_id'])->exists()) {
            $this->_stats['unchanged'] += 1;

            return;
        }

        $lines = $unit['lines'];
        $analysed = count(array_filter($lines, fn ($line) => ! empty($line['elements'])));

        $matches = [];
        foreach ($lines as $index => $line) {
            $match = $this->findSentence($line, $languageId);
            if ($match !== null) {
                $matches[$index] = $match;
            }
        }

        if (! empty($matches)) {
            // An existing text is worth enriching as long as Eldamo analyses any of it.
            if ($analysed === 0) {
                $this->skip($lineNumber, $unit, 'exists here, but none of its lines are analysed');

                return;
            }

            $enriched = false;
            foreach ($matches as $index => $match) {
                $enriched = $this->updateSentence($lineNumber, $lines[$index], $match) || $enriched;
            }

            if ($enriched && $unit['kind'] !== 'phrase') {
                $this->_stats['texts enriched'] += 1;
            }

            foreach (array_diff_key($lines, $matches) as $line) {
                $this->report($lineNumber, $line, 'line not found',
                    sprintf('not part of any existing phrase, unlike the rest of "%s"', $unit['name']));
            }

            return;
        }

        // Not every standalone phrase is worth adding: see ExtractEldamoPhrasesCommand::isWorthCreating.
        if (! $unit['can_create']) {
            $this->_stats['not worth creating'] += 1;

            return;
        }

        // A new text has to be mostly analysed to be worth adding; a half-explained poem is not.
        if ($unit['kind'] === 'text' && $analysed * 2 <= count($lines)) {
            $this->skip($lineNumber, $unit, sprintf('new, and only %d of %d lines are analysed', $analysed, count($lines)));

            return;
        }

        $this->createSentence($lineNumber, $unit, $languageId);
    }

    /**
     * @param  array<string, mixed>  $phrase
     */
    private function resolveLanguageId(array $phrase): ?int
    {
        $neoLanguageMap = $this->getNeoLanguageMap();
        $languageMap = $this->getLanguageMap();
        $language = $phrase['language'];

        $languageId = $neoLanguageMap[$language] ?? $languageMap[$language] ?? null;

        return $languageId ?: null;
    }

    /**
     * Locates the phrase inside an existing sentence, as a contiguous run of word fragments.
     *
     * A sentence here is often a whole poem, so one of them holds several Eldamo phrases: Gilraen's
     * Linnod is two, one per half-line. Matching whole sentences would find neither.
     *
     * @param  array<string, mixed>  $phrase
     * @return array{sentence: Sentence, fragments: array<int, SentenceFragment>}|null
     */
    private function findSentence(array $phrase, int $languageId): ?array
    {
        $tokens = $this->tokenize($phrase['text']);
        if (empty($tokens)) {
            return null;
        }

        foreach ($this->_sentences as $candidate) {
            // Without this, the Middle Primitive Elvish phrase "the" matches two Sindarin poems.
            if ($candidate['sentence']->language_id !== $languageId) {
                continue;
            }

            $offset = $this->findTokens($candidate['tokens'], $tokens);
            if ($offset === null) {
                continue;
            }

            return [
                'sentence' => $candidate['sentence'],
                'fragments' => array_slice($candidate['fragments'], $offset, count($tokens)),
            ];
        }

        return null;
    }

    /**
     * @param  array<int, string>  $haystack
     * @param  array<int, string>  $needle
     */
    private function findTokens(array $haystack, array $needle): ?int
    {
        $limit = count($haystack) - count($needle);
        for ($i = 0; $i <= $limit; $i += 1) {
            if (array_slice($haystack, $i, count($needle)) === $needle) {
                return $i;
            }
        }

        return null;
    }

    /**
     * Fills the gaps in an existing phrase: a missing dictionary link, missing inflections, a
     * missing translation. Nothing already there is touched -- these sentences are hand-curated,
     * and Eldamo's reading is not automatically the better one.
     *
     * SentenceRepository::saveSentence is deliberately not used here. It destroys and re-creates
     * every fragment, which would discard fragment IDs, keywords and any inflection we are not
     * supplying ourselves.
     *
     * @param  array<string, mixed>  $phrase
     * @param  array{sentence: Sentence, fragments: array<int, SentenceFragment>}  $match
     */
    private function updateSentence(int $lineNumber, array $phrase, array $match): bool
    {
        $sentence = $match['sentence'];
        $fragments = $match['fragments'];
        $changes = ['links' => [], 'inflections' => [], 'replacements' => [], 'translation' => null];

        foreach ($this->assignElements($lineNumber, $phrase, $fragments) as [$element, $fragment]) {
            $lexicalEntry = $this->findLexicalEntry($element);

            if ($lexicalEntry !== null && ! $fragment->lexical_entry_id) {
                $changes['links'][] = [$fragment, $lexicalEntry];
            }

            $inflectionIds = $this->resolveInflections($lineNumber, $phrase, $element);
            if (empty($inflectionIds)) {
                continue;
            }

            $existing = $fragment->lexical_entry_inflections()->orderBy('order')->get();
            $existingIds = $existing->pluck('inflection_id')->unique()->values()->all();
            $missing = array_values(array_diff($inflectionIds, $existingIds));

            $extra = array_diff($existingIds, $inflectionIds);

            if (empty($missing) && empty($extra)) {
                continue;
            }

            // Ours says something Eldamo's doesn't. Eldamo is the authority on Tolkien's grammar, so
            // its reading replaces ours -- recorded in the report, so the change can be reviewed.
            if (! empty($extra)) {
                $this->report($lineNumber, $phrase, 'inflection replaced', sprintf(
                    '"%s" had %s; now %s, as Eldamo has it',
                    $fragment->fragment, $this->describeInflections($existingIds), $this->describeInflections($inflectionIds)
                ));

                $changes['replacements'][] = [
                    $fragment,
                    $lexicalEntry,
                    $inflectionIds,
                    $existing->first()->inflection_group_uuid ?? null,
                ];

                continue;
            }

            // Ours is a subset of Eldamo's, so the missing inflections join the existing group:
            // "with plural object" gains "active participle" as one reading, not two.
            $changes['inflections'][] = [
                $fragment,
                $lexicalEntry,
                $missing,
                $existing->first()->inflection_group_uuid ?? null,
                $existing->isEmpty() ? 0 : $existing->max('order') + 1,
            ];
        }

        $changes['translation'] = $this->createTranslation($phrase, $sentence, $fragments[0]);

        // A translation this run already wrote, which createTranslation has just extended.
        $extended = array_intersect_key($this->_translations, $this->_extendedSlots);
        $extended = array_filter($extended, fn ($t) => $t->sentence_id === $sentence->id);
        $this->_extendedSlots = array_diff_key($this->_extendedSlots, $extended);

        if (empty($changes['links']) && empty($changes['inflections']) && empty($changes['replacements']) &&
            $changes['translation'] === null && empty($extended)) {
            $this->_stats['unchanged'] += 1;

            return false;
        }

        $this->line(sprintf('%d - %s: enriching "%s" (#%d)', $lineNumber, $phrase['page_id'],
            $sentence->name, $sentence->id));

        $this->_stats['lines enriched'] += 1;
        $this->_stats['translations extended'] += count($extended);
        $this->_stats['fragments linked'] += count($changes['links']);
        $this->_stats['translations added'] += $changes['translation'] === null ? 0 : 1;
        $this->_stats['inflections replaced'] += count($changes['replacements']);
        foreach ($changes['inflections'] as [, , $inflectionIds]) {
            $this->_stats['inflections added'] += count($inflectionIds);
        }

        if ($this->option('dry-run')) {
            return true;
        }

        DB::transaction(function () use ($sentence, $changes, $extended) {
            foreach ($changes['links'] as [$fragment, $lexicalEntry]) {
                $fragment->lexical_entry_id = $lexicalEntry->id;
                $fragment->speech_id = $fragment->speech_id ?: $lexicalEntry->speech_id;
                $fragment->save();
            }

            foreach ($changes['replacements'] as [$fragment, $lexicalEntry, $inflectionIds, $groupUuid]) {
                DB::table('lexical_entry_inflections')->where('sentence_fragment_id', $fragment->id)->delete();
                $this->saveInflections($sentence, $fragment, $lexicalEntry, $inflectionIds, $groupUuid);
            }

            foreach ($changes['inflections'] as [$fragment, $lexicalEntry, $inflectionIds, $groupUuid, $firstOrder]) {
                $this->saveInflections($sentence, $fragment, $lexicalEntry, $inflectionIds, $groupUuid, $firstOrder);
            }

            if ($changes['translation'] !== null) {
                $changes['translation']->save();
            }

            foreach ($extended as $translation) {
                // sentence_translations is keyed on (sentence_id, paragraph_number,
                // sentence_number) and has no surrogate ID, which Eloquent's save() assumes when
                // updating a row that already exists.
                DB::table('sentence_translations')
                    ->where('sentence_id', $translation->sentence_id)
                    ->where('paragraph_number', $translation->paragraph_number)
                    ->where('sentence_number', $translation->sentence_number)
                    ->whereNull('deleted_at')
                    ->update([
                        'translation' => $translation->translation,
                        'updated_at' => now(),
                    ]);

                $translation->syncChanges();
            }
        });

        // Keeps the search index and the audit trail in step with the edit.
        event(new SentenceEdited($sentence, $this->_account->id));

        return true;
    }

    /**
     * Creates a phrase Parf Edhellen does not have yet: a standalone phrase, or a text or collection
     * with one paragraph per line.
     *
     * @param  array<string, mixed>  $unit
     */
    private function createSentence(int $lineNumber, array $unit, int $languageId): void
    {
        $sentence = new Sentence([
            'name' => $this->createName($unit),
            'description' => $unit['gloss'] ?: ($unit['name'] ?: $unit['lines'][0]['text']),
            'long_description' => $this->createLongDescription($unit),
            'source' => Str::limit(implode('; ', $this->createSources($unit)), 64, ''),
            'language_id' => $languageId,
            'account_id' => $this->_account->id,
            'is_neologism' => $this->isNeologism($unit),
            'is_approved' => 1,
            'external_id' => $unit['external_id'],
        ]);

        $fragments = [];
        $inflectionsPerFragment = [];
        $translations = [];
        $order = 0;
        $sentenceNumber = 1;

        foreach ($unit['lines'] as $index => $line) {
            $paragraphNumber = $index + 1;

            if ($index > 0) {
                $fragments[] = $this->createFragment('', SentenceBuilder::TYPE_CODE_NEWLINE, $order, $paragraphNumber - 1, $sentenceNumber);
                $inflectionsPerFragment[] = [];
            }

            if ($line['gloss']) {
                $translations[] = new SentenceTranslation([
                    'paragraph_number' => $paragraphNumber,
                    'sentence_number' => $sentenceNumber,
                    'translation' => $line['gloss'],
                ]);
            }

            [$lineFragments, $lineInflections] = $this->createFragments($lineNumber, $line, $order, $paragraphNumber, $sentenceNumber);
            array_push($fragments, ...$lineFragments);
            array_push($inflectionsPerFragment, ...$lineInflections);
        }

        // A phrase none of whose words we can identify is worth less than the noise it adds. A
        // partly identified one still is worth having: the text and its translation are the point,
        // and the missing links are a thing a reader can supply later.
        $linked = count(array_filter($fragments, fn ($f) => (bool) $f->lexical_entry_id));
        $words = count(array_filter($fragments, fn ($f) => $f->type === SentenceBuilder::TYPE_CODE_WORD));
        if ($linked === 0) {
            $this->skip($lineNumber, $unit, sprintf('none of its %d words resolve to an entry', $words));

            return;
        }

        if ($linked * 2 < $words) {
            $this->report($lineNumber, $unit['lines'][0], 'partly linked',
                sprintf('only %d of %d words resolve to an entry', $linked, $words));
        }

        $this->line(sprintf('%d - %s: creating %s "%s" (%d %s)', $lineNumber, $unit['external_id'], $unit['kind'],
            $sentence->name, count($unit['lines']), count($unit['lines']) === 1 ? 'line' : 'lines'));

        $this->_stats[$unit['kind'].'s created'] += 1;
        $this->_stats['fragments linked'] += $linked;
        $this->_stats['translations added'] += count($translations);
        foreach ($inflectionsPerFragment as $inflections) {
            $this->_stats['inflections added'] += count($inflections);
        }

        if ($this->option('dry-run')) {
            return;
        }

        $this->transcribe($fragments, $languageId);

        // saveSentence is safe on this path: the sentence is new, so it raises SentenceCreated,
        // which carries the account rather than dereferencing the authenticated user.
        $this->_sentenceRepository->saveSentence($sentence, $fragments, $inflectionsPerFragment, $translations);
    }

    /**
     * Gives the fragments their tengwar, as the phrase form would have; the reader has nothing to
     * show on the tengwar line without it. A failure costs only the tengwar, which
     * ed-import:transcribe-tengwar can fill in later, so it doesn't stop the import.
     *
     * @param  array<int, SentenceFragment>  $fragments
     */
    private function transcribe(array $fragments, int $languageId): void
    {
        $mode = Language::where('id', $languageId)->value('tengwar_mode');
        $transcribable = array_values(array_filter($fragments,
            fn ($f) => $f->type !== SentenceBuilder::TYPE_CODE_NEWLINE && $f->fragment !== ''));

        if (! $mode || empty($transcribable)) {
            return;
        }

        try {
            $tengwar = $this->_transcriber->transcribe(array_map(
                fn ($f) => ['mode' => $mode, 'text' => $f->fragment], $transcribable));
        } catch (\RuntimeException $ex) {
            $this->warn('!! '.$ex->getMessage().' -- run ed-import:transcribe-tengwar afterwards');

            return;
        }

        foreach ($transcribable as $i => $fragment) {
            $fragment->tengwar = $tengwar[$i];
        }
    }

    private function createFragment(string $text, int $type, int &$order, int $paragraphNumber, int $sentenceNumber,
        ?object $lexicalEntry = null): SentenceFragment
    {
        $fragment = new SentenceFragment([
            'fragment' => Str::limit($text, 48, ''),
            'type' => $type,
            'order' => $order,
            'comments' => '',
            'paragraph_number' => $paragraphNumber,
            'sentence_number' => $sentenceNumber,
            'lexical_entry_id' => $lexicalEntry->id ?? null,
            'speech_id' => $lexicalEntry->speech_id ?? null,
        ]);
        $order += 10;

        return $fragment;
    }

    /**
     * Turns one line's element analysis into fragments, recovering the connectors and punctuation
     * that separate them from the line as written. Sentences are numbered the way the phrase form
     * numbers them, advancing after each full stop.
     *
     * @param  array<string, mixed>  $phrase
     * @return array{0: array<int, SentenceFragment>, 1: array<int, array<int, LexicalEntryInflection>>}
     */
    private function createFragments(int $lineNumber, array $phrase, int &$order, int $paragraphNumber, int &$sentenceNumber): array
    {
        $sequence = $this->createElementSequence($phrase);
        $punctuation = $this->readPunctuation($phrase['text'], count($sequence));

        $fragments = [];
        $inflections = [];

        $append = function (string $text, int $type, ?object $lexicalEntry = null, array $inflectionIds = []) use (&$fragments, &$inflections, &$order, $paragraphNumber, &$sentenceNumber) {
            $fragments[] = $this->createFragment($text, $type, $order, $paragraphNumber, $sentenceNumber, $lexicalEntry);
            $inflections[] = array_map(fn ($id) => new LexicalEntryInflection(['inflection_id' => $id]), $inflectionIds);

            if ($type === SentenceBuilder::TYPE_CODE_INTERPUNCTUATION && preg_match('/[.!?]/u', $text)) {
                $sentenceNumber += 1;
            }
        };

        foreach ($sequence as $index => $element) {
            $surface = (string) ($element['surface'] ?? '');

            // "(hara) máriessë": Tolkien's optional words keep their brackets, as fragments of
            // their own the way the phrase form writes them.
            $opening = preg_match('/^[(\[]+/u', $surface, $m) ? $m[0] : '';
            $closing = preg_match('/[)\]]+$/u', $surface, $m) ? $m[0] : '';
            $surface = mb_substr($surface, mb_strlen($opening), mb_strlen($surface) - mb_strlen($opening) - mb_strlen($closing));
            $word = trim($surface, implode('', self::CONNECTORS));

            if ($word === '') {
                continue;
            }

            if ($opening !== '') {
                $append($opening, SentenceBuilder::TYPE_CODE_OPEN_PARANTHESIS);
            }

            // "-nud" and "’ni" attach to the word before them; "i-" and "e·" to the word after.
            if ($surface !== $word && Str::startsWith($surface, self::CONNECTORS) && ! empty($fragments)) {
                $append(mb_substr($surface, 0, 1), SentenceBuilder::TYPE_CODE_WORD_CONNEXION);
            }

            $append(
                $word,
                SentenceBuilder::TYPE_CODE_WORD,
                $this->findLexicalEntry($element),
                $this->resolveInflections($lineNumber, $phrase, $element)
            );

            if ($closing !== '') {
                $append($closing, SentenceBuilder::TYPE_CODE_CLOSE_PARANTHESIS);
            }

            if (isset($punctuation[$index]) && $punctuation[$index] !== '') {
                $append($punctuation[$index], SentenceBuilder::TYPE_CODE_INTERPUNCTUATION);
            } elseif ($surface !== $word && Str::endsWith($surface, self::CONNECTORS)) {
                $append(mb_substr($surface, -1), SentenceBuilder::TYPE_CODE_WORD_CONNEXION);
            }
        }

        return [$fragments, $inflections];
    }

    /**
     * The elements to build a new phrase from, one per written word.
     *
     * Where Eldamo quotes an attested decomposition, that is the sequence. Where it does not -- and
     * it often does not for a neologism, which has nothing to attest -- the phrase is still worth
     * having, so it is built from its own words, and the analysis is attached to whichever of them
     * it names. "ninya ná cares" has a single element for "ninya"; the other two words come through
     * as plain text rather than not at all.
     *
     * @param  array<string, mixed>  $phrase
     * @return array<int, array<string, mixed>>
     */
    private function createElementSequence(array $phrase): array
    {
        if ($phrase['alignment'] !== 'none') {
            return $phrase['elements'];
        }

        $byHeadword = [];
        foreach ($phrase['elements'] as $element) {
            // Headwords are cited in their dictionary form, so a verb carries a trailing hyphen.
            $byHeadword[$this->normalize($element['v'])] ??= $element;
        }

        $sequence = [];
        foreach (preg_split('/\s+/u', trim($phrase['text']), -1, PREG_SPLIT_NO_EMPTY) as $word) {
            // Punctuation is recovered separately by readPunctuation; left on the word, "ai!" gains a second "!".
            $word = preg_replace('/[,.!?;:…]+$/u', '', $word);
            $element = $byHeadword[$this->normalize($word)] ?? null;

            $sequence[] = [
                'l' => $element['l'] ?? $phrase['language'],
                'v' => $element['v'] ?? $word,
                'form' => $element['form'] ?? [],
                'surface' => $word,
                'external_id' => $element['external_id'] ?? null,
            ];
        }

        return $sequence;
    }

    /**
     * Recovers the punctuation following each element, by walking the phrase as Eldamo wrote it.
     * Only usable when the written words line up with the elements one for one; where they do not,
     * a trailing mark is still worth keeping.
     *
     * @return array<int, string>
     */
    private function readPunctuation(string $text, int $count): array
    {
        preg_match_all('/[^\p{L}\p{N}]+/u', $text, $matches, PREG_OFFSET_CAPTURE);
        $words = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        $punctuation = [];
        if (count($words) === $count) {
            foreach ($matches[0] as $separator) {
                // A separator standing between two word characters binds them; it is a connector,
                // not punctuation, and createFragments recovers it from the surface form instead.
                $index = count(preg_split('/[^\p{L}\p{N}]+/u', mb_strcut($text, 0, $separator[1]), -1, PREG_SPLIT_NO_EMPTY)) - 1;
                $marks = preg_replace('/[^,.!?;:…]/u', '', $separator[0]);

                if ($index >= 0 && $marks !== '') {
                    $punctuation[$index] = ($punctuation[$index] ?? '').$marks;
                }
            }

            return $punctuation;
        }

        if (preg_match('/([,.!?;:…]+)\s*$/u', $text, $trailing)) {
            $punctuation[$count - 1] = $trailing[1];
        }

        return $punctuation;
    }

    /**
     * Pairs each element with the fragment it describes. The window was found by matching tokens
     * one for one, so element surfaces and fragments march in step -- until an element's written
     * form spans two fragments, at which point the pairing is ambiguous and reported rather than
     * guessed at.
     *
     * @param  array<string, mixed>  $phrase
     * @param  array<int, SentenceFragment>  $fragments
     * @return array<int, array{0: array<string, mixed>, 1: SentenceFragment}>
     */
    private function assignElements(int $lineNumber, array $phrase, array $fragments): array
    {
        // The window was found by matching the phrase's own words against the fragments, so when
        // there is one element per fragment the pairing is settled by position alone. That is the
        // reading to trust: a reference's spelling may differ from ours ("alkar" for "alcar")
        // without either being wrong about which word is meant.
        if (count($phrase['elements']) === count($fragments)) {
            return array_map(null, $phrase['elements'], $fragments);
        }

        $pairs = [];
        $cursor = 0;

        foreach ($phrase['elements'] as $element) {
            $tokens = $this->tokenize((string) ($element['surface'] ?? ''));
            if (empty($tokens)) {
                continue;
            }

            $window = array_slice(array_map(fn ($f) => $this->normalize($f->fragment), $fragments), $cursor, count($tokens));
            if ($window !== $tokens) {
                $this->report($lineNumber, $phrase, 'unaligned element', sprintf(
                    '"%s" (%s) does not line up with the fragments at position %d',
                    $element['surface'] ?? '?', $element['v'], $cursor
                ));

                break;
            }

            if (count($tokens) === 1) {
                $pairs[] = [$element, $fragments[$cursor]];
            } else {
                $this->report($lineNumber, $phrase, 'element spans fragments', sprintf(
                    '"%s" (%s) covers %d fragments', $element['surface'], $element['v'], count($tokens)
                ));
            }

            $cursor += count($tokens);
        }

        return $pairs;
    }

    /**
     * @param  array<string, mixed>  $phrase
     */
    private function createTranslation(array $phrase, Sentence $sentence, SentenceFragment $fragment): ?SentenceTranslation
    {
        if (! $phrase['gloss']) {
            return null;
        }

        $paragraph = $fragment->paragraph_number ?: 1;
        $number = $fragment->sentence_number ?: 1;

        $slot = $sentence->id.':'.$paragraph.':'.$number;

        // Gilraen's Linnod is two Eldamo phrases but one numbered line, so both glosses belong in
        // the one slot. Extending a translation this run wrote is not the same as rewriting one
        // somebody curated, which is left alone below.
        if (isset($this->_translations[$slot])) {
            $translation = $this->_translations[$slot];
            if (! Str::contains($translation->translation, $phrase['gloss'])) {
                $translation->translation .= '; '.$phrase['gloss'];
                $this->_extendedSlots[$slot] = true;
            }

            return null;
        }

        $exists = SentenceTranslation::where('sentence_id', $sentence->id)
            ->where('paragraph_number', $paragraph)
            ->where('sentence_number', $number)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return null;
        }

        $translation = new SentenceTranslation([
            'paragraph_number' => $paragraph,
            'sentence_number' => $number,
            'translation' => $phrase['gloss'],
        ]);
        $translation->sentence_id = $sentence->id;
        $this->_translations[$slot] = $translation;

        return $translation;
    }

    /**
     * @param  array<int, int>  $inflectionIds
     */
    private function saveInflections(Sentence $sentence, SentenceFragment $fragment, ?object $lexicalEntry, array $inflectionIds,
        ?string $groupUuid = null, int $firstOrder = 0): void
    {
        $lexicalEntryId = $fragment->lexical_entry_id ?: ($lexicalEntry->id ?? null);
        if (! $lexicalEntryId) {
            return;
        }

        // One written form, one group -- "past" and "1st singular" are one inflection to a reader.
        $uuid = $groupUuid ?? (string) Str::uuid();
        $rows = [];
        foreach (array_values($inflectionIds) as $offset => $inflectionId) {
            $order = $firstOrder + $offset;
            $rows[] = [
                'inflection_group_uuid' => $uuid,
                'lexical_entry_id' => $lexicalEntryId,
                'language_id' => $sentence->language_id,
                'inflection_id' => $inflectionId,
                'speech_id' => $fragment->speech_id ?: ($lexicalEntry->speech_id ?? null),
                'account_id' => $this->_account->id,
                'sentence_id' => $sentence->id,
                'sentence_fragment_id' => $fragment->id,
                'is_neologism' => 0,
                'is_rejected' => 0,
                'source' => null,
                'word' => $fragment->fragment,
                'order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('lexical_entry_inflections')->insert($rows);
    }

    /**
     * @param  array<int, int>  $inflectionIds
     */
    private function describeInflections(array $inflectionIds): string
    {
        $names = Inflection::whereIn('id', $inflectionIds)->pluck('name', 'id');

        return '"'.implode(', ', array_map(fn ($id) => $names[$id] ?? '#'.$id, $inflectionIds)).'"';
    }

    /**
     * @param  array<string, mixed>  $element
     */
    private function findLexicalEntry(array $element): ?object
    {
        $externalId = $element['external_id'] ?? null;
        if (! $externalId) {
            return null;
        }

        if (! array_key_exists($externalId, $this->_lexicalEntries)) {
            $this->_lexicalEntries[$externalId] = LexicalEntry::where('external_id', $externalId)
                ->where('is_deleted', 0)
                // A handful of external IDs are shared by more than one entry; take the oldest so
                // that repeated runs agree with each other.
                ->orderBy('id')
                ->first(['id', 'speech_id']);
        }

        return $this->_lexicalEntries[$externalId];
    }

    /**
     * @param  array<string, mixed>  $phrase
     * @param  array<string, mixed>  $element
     * @return array<int, int>
     */
    private function resolveInflections(int $lineNumber, array $phrase, array $element): array
    {
        $ids = [];
        foreach ($element['form'] as $token) {
            $name = self::INFLECTION_MAP[$token] ?? null;
            if ($name === null || ! isset($this->_inflectionIds[$name])) {
                $this->report($lineNumber, $phrase, 'unmapped inflection', sprintf(
                    '"%s" on %s', $token, $element['v']
                ));

                continue;
            }

            $ids[] = $this->_inflectionIds[$name];
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<string, mixed>  $phrase
     */
    private function createName(array $unit): string
    {
        if ($unit['name']) {
            return Str::limit($unit['name'], 128, '');
        }

        $name = $unit['gloss'] ?: $unit['lines'][0]['text'];

        // Eldamo glosses a phrase the way it would be glossed in a dictionary, lower case and often
        // with a literal reading appended. A title reads better without the latter.
        $name = preg_replace('/,\s*\(lit\.\).*$/u', '', $name);

        return Str::limit(Str::ucfirst(trim($name)), 128, '');
    }

    /**
     * Eldamo's notes are a fragment of HTML, with its own cross-references. Parf Edhellen renders
     * long descriptions as Markdown, and cannot resolve Eldamo's links, so the markup is reduced to
     * the text it decorates.
     *
     * @param  array<string, mixed>  $phrase
     */
    private function createLongDescription(array $phrase): ?string
    {
        $parts = [];

        if ($phrase['notes']) {
            // "@@@" marks a note Eldamo has not written up yet; it is an editorial aside to its
            // own author, not something to show a reader.
            $notes = preg_replace('/^\s*@@@\s*/u', '', $phrase['notes']);
            // An empty <a l="s" v="anna-"/> stands for the word it points at.
            $notes = preg_replace('/<a\b[^>]*\bv="([^"]*)"[^>]*\/>/u', '$1', $notes);
            $notes = preg_replace('/<\/?(?:i|em)>/u', '_', $notes);
            $notes = preg_replace('/<\/?(?:b|strong)>/u', '**', $notes);
            $notes = preg_replace('/<li>/u', '- ', $notes);
            $notes = preg_replace('/<\/p>|<\/li>/u', "\n\n", $notes);
            $notes = strip_tags($notes);
            $notes = html_entity_decode($notes, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $notes = preg_replace('/\n{3,}/u', "\n\n", $notes);
            // Eldamo escapes grammatical placeholders as entities ("&lt;infinitive&gt;"), which
            // decode back into something Markdown would read as a tag and swallow.
            $notes = preg_replace('/<([^<>\s][^<>]*)>/u', '`<$1>`', $notes);

            $parts[] = trim($notes);
        }

        if ($phrase['created_by']) {
            // Eldamo credits the author of a neologism; so should we.
            $parts[] = sprintf('Composed by %s, and published in Eldamo%s.', $phrase['created_by'],
                $phrase['neo_version'] ? ' '.$phrase['neo_version'] : '');
        }

        $parts = array_filter($parts);

        return empty($parts) ? null : implode("\n\n", $parts);
    }

    /**
     * The works a unit is attested in, once each: "LotR; PE17" rather than every page and line.
     *
     * @param  array<string, mixed>  $unit
     * @return array<int, string>
     */
    private function createSources(array $unit): array
    {
        $sources = $unit['sources'];
        foreach ($unit['lines'] as $line) {
            array_push($sources, ...$line['sources']);
        }

        return array_values(array_unique(array_map(fn ($source) => Str::before($source, '/'), $sources)));
    }

    /**
     * @param  array<string, mixed>  $phrase
     */
    private function isNeologism(array $phrase): bool
    {
        return in_array($phrase['language'], ['ns', 'nq', 'np'], true) ||
            $phrase['neo_version'] !== null ||
            $phrase['created_by'] !== null ||
            // "!" marks a form invented by someone other than Tolkien, as in the glossary importer.
            Str::contains((string) $phrase['mark'], '!');
    }

    /**
     * Reduces a written form to the tokens it would be stored as. Used only to compare Eldamo's
     * text with ours; nothing normalised is ever saved.
     *
     * @return array<int, string>
     */
    private function tokenize(string $value): array
    {
        $normalized = $this->normalize($value);

        return $normalized === '' ? [] : explode(' ', $normalized);
    }

    private function normalize(string $value): string
    {
        $value = \Normalizer::normalize($value, \Normalizer::FORM_C) ?: $value;
        $value = mb_strtolower($value, 'UTF-8');

        // Eldamo spells a headword with a circumflex but quotes it with a macron: "batân", "batān".
        $value = strtr($value, ['ā' => 'â', 'ē' => 'ê', 'ī' => 'î', 'ō' => 'ô', 'ū' => 'û']);

        // Connectors and punctuation are fragments of their own here, so they are boundaries, not
        // characters to drop: "i-Estel" has to become "i estel" to match the fragments "i", "Estel".
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);

        return trim($value);
    }

    /**
     * @param  array<string, mixed>  $phrase
     */
    private function skip(int $lineNumber, array $phrase, string $reason): void
    {
        $this->_stats['skipped'] += 1;
        $this->line(sprintf('%d - %s: skipped (%s)', $lineNumber, $phrase['page_id'] ?? $phrase['external_id'], $reason));
        $this->report($lineNumber, $phrase, 'skipped', $reason);
    }

    /**
     * @param  array<string, mixed>  $phrase
     */
    private function report(int $lineNumber, array $phrase, string $category, string $detail): void
    {
        $this->_report[] = [
            'line' => (string) $lineNumber,
            // A unit is reported by its own ID and name, a line by its phrase's.
            'page_id' => (string) ($phrase['page_id'] ?? $phrase['external_id']),
            'text' => $phrase['text'] ?? $phrase['name'] ?? $phrase['lines'][0]['text'],
            'category' => $category,
            'detail' => $detail,
        ];
    }

    private function writeReport(): void
    {
        $path = $this->option('report');
        if (! $path) {
            $this->line('!! '.count($this->_report).' report entries (pass --report to write them out)');

            return;
        }

        $fp = fopen($path, 'w');
        if (! $fp) {
            $this->error('Failed to open '.$path.' for writing.');

            return;
        }

        try {
            fputcsv($fp, ['line', 'page_id', 'text', 'category', 'detail'], "\t");
            foreach ($this->_report as $entry) {
                fputcsv($fp, array_values($entry), "\t");
            }
        } finally {
            fclose($fp);
        }

        $this->line('!! wrote '.count($this->_report).' report entries to '.$path);
    }
}
