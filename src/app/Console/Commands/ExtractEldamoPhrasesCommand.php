<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ExtractEldamoPhrasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-import:eldamo-phrases-extract {source} {--out=eldamo-phrases.jsonl}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extracts the texts in Eldamo, and the phrases worth importing on their own, into a JSONL file for ed-import:eldamo-phrases.';

    /**
     * Languages whose phrases are worth a reader's time on their own. Tolkien's earlier conceptions
     * (Qenya, Gnomish, Noldorin, …) survive in Eldamo as drafts of these, and are left out. The
     * neo-languages are Eldamo's names for community reconstructions, imported as their parents.
     */
    const MAJOR_LANGUAGES = ['s', 'q', 't', 'ad', 'bs', 'kh', 'ns', 'nq'];

    /**
     * A standalone phrase with no notes of its own needs at least this many words to explain
     * itself. Shorter ones are mostly Tolkien's two-word teaching examples.
     */
    const MINIMUM_WORDS_WITHOUT_NOTES = 5;

    /**
     * Tolkien's short example phrases from Parma Eldalamberon 17. Too slight to stand alone, they
     * read well as small lessons, one theme apiece. The lines refer to Eldamo headwords.
     */
    const COLLECTIONS = [
        [
            'external_id' => 'eldamo-pe17-parma',
            'language' => 'q',
            'name' => 'Phrases about books',
            'description' => 'Tolkien’s short examples of phrases with parma “book”.',
            'lines' => ['antanen parma sen', 'henta parma', 'cesë parma', 'paranye parmanen'],
        ],
        [
            'external_id' => 'eldamo-pe17-speak',
            'language' => 'q',
            'name' => 'Being able to speak',
            'description' => 'Tolkien’s short examples of istan and polin “I can”, with quet- “to speak”.',
            'lines' => ['istan quetë', 'polin quetë', 'queta Quenya'],
        ],
        [
            'external_id' => 'eldamo-pe17-marie',
            'language' => 'q',
            'name' => 'Wishing someone well',
            'description' => 'Tolkien’s short examples of well-wishing, with márië “happiness”.',
            'lines' => ['áva márië', '(hara) máriessë', 'nai Eru tye mánata'],
        ],
        [
            'external_id' => 'eldamo-pe17-genitive-plural',
            'language' => 's',
            'name' => 'Genitive plurals',
            'description' => 'Tolkien’s short examples of the Sindarin genitive plural in -ion.',
            'lines' => ['mellyn enin Edhellion', 'glim maewion', 'lais geledhion'],
        ],
    ];

    /**
     * Eldamo headword ("<language>\0<word>") to page ID. This is what lets the importer resolve an
     * <element l="s" v="anna-"/> reference to a lexical entry, whose external_id is the page ID.
     *
     * @var array<string, string>
     */
    private array $_pageIds = [];

    /** @var array<string, array<string, mixed>> phrases by headword */
    private array $_phrases = [];

    /** @var array<int, array<string, mixed>> texts, in document order */
    private array $_texts = [];

    public function handle()
    {
        $path = $this->argument('source');
        if (! file_exists($path)) {
            $this->error($path.' does not exist.');

            return 1;
        }

        $this->line('Pass 1: indexing headwords.');
        $this->indexPageIds($path);
        $this->line('!! indexed '.count($this->_pageIds).' headwords');

        $this->line('Pass 2: reading phrases and texts.');
        $this->readPhrasesAndTexts($path);
        $this->line('!! read '.count($this->_phrases).' phrases and '.count($this->_texts).' texts');

        $out = $this->option('out');
        $fp = fopen($out, 'w');
        if (! $fp) {
            $this->error('Failed to open '.$out.' for writing.');

            return 1;
        }

        $counts = ['text' => 0, 'collection' => 0, 'phrase' => 0];
        try {
            foreach ($this->createUnits() as $unit) {
                $counts[$unit['kind']] += 1;
                fwrite($fp, json_encode($unit, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
            }
        } finally {
            fclose($fp);
        }

        $this->line(sprintf('!! wrote %d texts, %d collections and %d phrases to %s',
            $counts['text'], $counts['collection'], $counts['phrase'], $out));

        return 0;
    }

    /**
     * Everything to import, each phrase exactly once: as a line of the text it belongs to, as a line
     * of a collection, or on its own.
     *
     * @return \Generator<array<string, mixed>>
     */
    private function createUnits(): \Generator
    {
        $claimed = [];

        foreach ($this->_texts as $text) {
            $lines = [];
            foreach ($text['lines'] as $key) {
                $claimed[$key] = true;
                if (isset($this->_phrases[$key])) {
                    $lines[] = $this->_phrases[$key];
                }
            }

            // Eldamo keeps a text's drafts beside it. Only the final version is the text.
            if (Str::contains(Str::lower($text['name']), 'draft') || empty($lines)) {
                continue;
            }

            // Whether a new text is worth adding depends on how much of it is analysed, which the
            // importer weighs once it knows whether the text is new.
            yield array_merge($text, ['can_create' => true, 'lines' => $lines]);
        }

        foreach (self::COLLECTIONS as $collection) {
            $lines = [];
            foreach ($collection['lines'] as $headword) {
                $key = $collection['language']."\0".$headword;
                if (! isset($this->_phrases[$key]) || isset($claimed[$key])) {
                    $this->warn('!! collection '.$collection['external_id'].' has no free phrase "'.$headword.'"');

                    continue;
                }

                $claimed[$key] = true;
                $lines[] = $this->_phrases[$key];
            }

            if (empty($lines)) {
                continue;
            }

            yield [
                'kind' => 'collection',
                'external_id' => $collection['external_id'],
                'language' => $collection['language'],
                'name' => $collection['name'],
                'gloss' => $collection['description'],
                'notes' => null,
                'mark' => null,
                'neo_version' => null,
                'created_by' => null,
                'sources' => ['PE17'],
                'can_create' => true,
                'lines' => $lines,
            ];
        }

        foreach ($this->_phrases as $key => $phrase) {
            // Every analysed phrase may enrich one we already have; only some are worth adding new.
            if (isset($claimed[$key]) || $phrase['is_draft'] || empty($phrase['elements'])) {
                continue;
            }

            yield [
                'kind' => 'phrase',
                'external_id' => $phrase['page_id'],
                'language' => $phrase['language'],
                'name' => null,
                'gloss' => $phrase['gloss'],
                'notes' => $phrase['notes'],
                'mark' => $phrase['mark'],
                'neo_version' => $phrase['neo_version'],
                'created_by' => $phrase['created_by'],
                'sources' => $phrase['sources'],
                'can_create' => $this->isWorthCreating($phrase),
                'lines' => [$phrase],
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $phrase
     */
    private function isWorthCreating(array $phrase): bool
    {
        if (! in_array($phrase['language'], self::MAJOR_LANGUAGES, true)) {
            return false;
        }

        if (trim(strip_tags((string) $phrase['notes'])) !== '') {
            return true;
        }

        // Without notes, a phrase has to carry its own context: a translation, a citation, and
        // enough words for the translation to say something.
        $words = count(preg_split('/\s+/u', $this->fold($phrase['text']), -1, PREG_SPLIT_NO_EMPTY));

        return $phrase['gloss'] !== null && ! empty($phrase['sources']) &&
            $words >= self::MINIMUM_WORDS_WITHOUT_NOTES;
    }

    /**
     * Walks every <word> in the document and records its page ID.
     */
    private function indexPageIds(string $path): void
    {
        $reader = new \XMLReader;
        $reader->open($path);

        try {
            while ($reader->read()) {
                if ($reader->nodeType !== \XMLReader::ELEMENT || $reader->localName !== 'word') {
                    continue;
                }

                $language = $reader->getAttribute('l');
                $word = $reader->getAttribute('v');
                $pageId = $reader->getAttribute('page-id');

                if ($language === null || $word === null || $pageId === null) {
                    continue;
                }

                // Homographs share a headword; Eldamo disambiguates them with a superscript in @v
                // ("i¹" versus "i²"), so collisions here are genuinely the same entry appearing twice.
                $this->_pageIds[$language."\0".$word] ??= $pageId;
            }
        } finally {
            $reader->close();
        }
    }

    /**
     * Reads every phrase and text. A phrase nested inside another phrase is one of its drafts, and a
     * text lists its lines as references to phrases by headword.
     */
    private function readPhrasesAndTexts(string $path): void
    {
        $reader = new \XMLReader;
        $reader->open($path);
        $document = new \DOMDocument;

        // The speech of each open <word>, outermost first.
        $ancestors = [];

        try {
            while ($reader->read()) {
                if ($reader->localName !== 'word') {
                    continue;
                }

                if ($reader->nodeType === \XMLReader::END_ELEMENT) {
                    array_pop($ancestors);

                    continue;
                }

                if ($reader->nodeType !== \XMLReader::ELEMENT) {
                    continue;
                }

                $speech = $reader->getAttribute('speech');
                $isDraft = in_array('phrase', $ancestors, true);

                if ($speech === 'phrase' || $speech === 'text') {
                    // expand() copies the subtree without moving the cursor, so read() carries on
                    // into the children -- which is how nested drafts are reached.
                    $node = $reader->expand($document);
                    if ($node !== false) {
                        $word = simplexml_import_dom($node);
                        if ($speech === 'phrase') {
                            $this->_phrases[(string) $word['l']."\0".(string) $word['v']] ??=
                                $this->createPhrase($word) + ['is_draft' => $isDraft];
                        } else {
                            $this->_texts[] = $this->createText($word);
                        }
                    }
                }

                if (! $reader->isEmptyElement) {
                    $ancestors[] = $speech;
                }
            }
        } finally {
            $reader->close();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function createText(\SimpleXMLElement $word): array
    {
        $lines = [];
        foreach ($word->element as $element) {
            $lines[] = (string) $element['l']."\0".(string) $element['v'];
        }

        return [
            'kind' => 'text',
            'external_id' => (string) $word['page-id'],
            'language' => (string) $word['l'],
            'name' => (string) $word['v'],
            'gloss' => $word['gloss'] !== null ? (string) $word['gloss'] : null,
            'notes' => $word->notes->count() > 0 ? (string) $word->notes[0] : null,
            'mark' => $word['mark'] !== null ? (string) $word['mark'] : null,
            'neo_version' => null,
            'created_by' => null,
            'sources' => [],
            'lines' => $lines,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function createPhrase(\SimpleXMLElement $word): array
    {
        $language = (string) $word['l'];
        $text = (string) $word['v'];

        // Direct children only: a nested draft phrase has <element> children of its own.
        $analysis = [];
        foreach ($word->element as $element) {
            $analysis[] = [
                'l' => (string) $element['l'],
                'v' => (string) $element['v'],
                'form' => $element['form'] !== null ? preg_split('/\s+/', trim((string) $element['form'])) : [],
            ];
        }

        [$surfaces, $alignment] = $this->alignSurfaceForms($word, $text, count($analysis));

        $elements = [];
        foreach ($analysis as $i => $element) {
            $elements[] = $element + [
                'surface' => $surfaces[$i] ?? null,
                'external_id' => $this->_pageIds[$element['l']."\0".$element['v']] ?? null,
            ];
        }

        $sources = [];
        foreach ($word->ref as $ref) {
            $sources[] = (string) $ref['source'];
        }

        return [
            'page_id' => (string) $word['page-id'],
            'language' => $language,
            'text' => $text,
            'gloss' => $word['gloss'] !== null ? (string) $word['gloss'] : null,
            'mark' => $word['mark'] !== null ? (string) $word['mark'] : null,
            'neo_version' => $word['neo-version'] !== null ? (string) $word['neo-version'] : null,
            'created_by' => $word['created'] !== null ? (string) $word['created'] : null,
            'notes' => $word->notes->count() > 0 ? (string) $word->notes[0] : null,
            'sources' => $sources,
            'elements' => $elements,
            'alignment' => $alignment,
        ];
    }

    /**
     * The <element> analysis names headwords, not the forms as written, so each element needs its
     * written form -- its surface -- to be placed in the phrase.
     *
     * The phrase's own spelling is the one to keep. Eldamo's references quote manuscripts, which
     * disagree with it and with each other: "kese parma" for "cesë parma", and for one line of Löa
     * Yucainen a different draft altogether. So the phrase is split into words, and, if that doesn't
     * give one per element, split again at the characters that bind words ("tenn’aldar"). Only if
     * neither lines up is a reference used, and only one that reads the same as the phrase.
     *
     * @return array{0: array<int, string>, 1: string}
     */
    private function alignSurfaceForms(\SimpleXMLElement $word, string $text, int $expected): array
    {
        if ($expected === 0) {
            return [[], 'none'];
        }

        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) === $expected) {
            return [$words, 'text'];
        }

        // Split after a connector, so that it stays with the word it follows: "tenn’", "aldar".
        $bound = preg_split('/\s+|(?<=[-·’\'])(?=\p{L})/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        if (count($bound) === $expected) {
            return [$bound, 'text'];
        }

        foreach ($word->ref as $ref) {
            if ($ref->element->count() === $expected && $this->fold((string) $ref['v']) === $this->fold($text)) {
                $surfaces = [];
                foreach ($ref->element as $element) {
                    $surfaces[] = (string) $element['v'];
                }

                return [$surfaces, 'ref'];
            }
        }

        return [[], 'none'];
    }

    /**
     * Reduces a written form to letters alone, so that two spellings of one phrase can be compared.
     * Eldamo writes a headword with a circumflex but quotes it with a macron -- "batân", "batān".
     */
    private function fold(string $value): string
    {
        $value = \Normalizer::normalize($value, \Normalizer::FORM_C) ?: $value;
        $value = mb_strtolower($value, 'UTF-8');
        $value = strtr($value, ['ā' => 'â', 'ē' => 'ê', 'ī' => 'î', 'ō' => 'ô', 'ū' => 'û']);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);

        return trim($value);
    }
}
