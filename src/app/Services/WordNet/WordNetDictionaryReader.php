<?php

namespace App\Services\WordNet;

use App\Services\Enumerations\WordNetPos;
use Generator;
use RuntimeException;

/**
 * Reads a Princeton WordNet `dict` directory. Yields rows shaped for bulk insertion into the `wordnet_*` tables.
 */
class WordNetDictionaryReader
{
    // 3.1 no longer ships the `lexnames` file; the lexicographer files are numbered identically since 3.0.
    private const LEXNAMES = [
        'adj.all', 'adj.pert', 'adv.all', 'noun.Tops', 'noun.act', 'noun.animal', 'noun.artifact', 'noun.attribute',
        'noun.body', 'noun.cognition', 'noun.communication', 'noun.event', 'noun.feeling', 'noun.food', 'noun.group',
        'noun.location', 'noun.motive', 'noun.object', 'noun.person', 'noun.phenomenon', 'noun.plant', 'noun.possession',
        'noun.process', 'noun.quantity', 'noun.relation', 'noun.shape', 'noun.state', 'noun.substance', 'noun.time',
        'verb.body', 'verb.change', 'verb.cognition', 'verb.communication', 'verb.competition', 'verb.consumption',
        'verb.contact', 'verb.creation', 'verb.emotion', 'verb.motion', 'verb.perception', 'verb.possession',
        'verb.social', 'verb.stative', 'verb.weather', 'adj.ppl',
    ];

    // the sense key's numeric synset type, e.g. oak%1:20:00::
    private const SENSE_KEY_POS = [1 => 'n', 2 => 'v', 3 => 'a', 4 => 'r', 5 => 's'];

    private const DATA_FILES = [WordNetPos::NOUN, WordNetPos::VERB, WordNetPos::ADJECTIVE, WordNetPos::ADVERB];

    public function __construct(private readonly string $_path) {}

    /**
     * @return Generator<array{id: string, pos: string, lexname: string, definition: string}>
     */
    public function synsets(): Generator
    {
        foreach (self::DATA_FILES as $pos) {
            foreach ($this->dataLines($pos) as [$fields, $gloss]) {
                yield [
                    'id' => self::synsetId($fields[0], $pos),
                    'pos' => $fields[2],
                    'lexname' => self::LEXNAMES[(int) $fields[1]],
                    'definition' => $gloss,
                ];
            }
        }
    }

    /**
     * Hypernym and instance hypernym pointers. Only nouns and verbs have them.
     *
     * @return Generator<array{synset_id: string, hypernym_id: string, is_instance: bool}>
     */
    public function hypernyms(): Generator
    {
        foreach ([WordNetPos::NOUN, WordNetPos::VERB] as $pos) {
            foreach ($this->dataLines($pos) as [$fields]) {
                $synsetId = self::synsetId($fields[0], $pos);
                $wordCount = hexdec($fields[3]);
                $cursor = 4 + $wordCount * 2;
                $pointerCount = (int) $fields[$cursor];

                for ($i = 0; $i < $pointerCount; $i++) {
                    [$symbol, $offset, $targetPos] = array_slice($fields, $cursor + 1 + $i * 4, 3);
                    if ($symbol === '@' || $symbol === '@i') {
                        yield [
                            'synset_id' => $synsetId,
                            'hypernym_id' => self::synsetId($offset, WordNetPos::from($targetPos)),
                            'is_instance' => $symbol === '@i',
                        ];
                    }
                }
            }
        }
    }

    /**
     * @return Generator<array{lemma: string, synset_id: string, pos: string, sense_number: int, tag_count: int}>
     */
    public function senses(): Generator
    {
        foreach ($this->lines('index.sense') as $line) {
            [$senseKey, $offset, $senseNumber, $tagCount] = explode(' ', $line);
            [$lemma, $lexSense] = explode('%', $senseKey, 2);
            $pos = WordNetPos::from(self::SENSE_KEY_POS[(int) $lexSense[0]]);

            yield [
                'lemma' => self::lemma($lemma),
                'synset_id' => self::synsetId($offset, $pos),
                'pos' => $pos->value,
                'sense_number' => (int) $senseNumber,
                'tag_count' => (int) $tagCount,
            ];
        }
    }

    /**
     * @return Generator<array{pos: string, form: string, base: string}>
     */
    public function exceptions(): Generator
    {
        // noun.exc repeats some lines verbatim
        $seen = [];
        foreach (self::DATA_FILES as $pos) {
            foreach ($this->lines($pos->file().'.exc') as $line) {
                [$form, $bases] = explode(' ', $line, 2);
                foreach (explode(' ', $bases) as $base) {
                    $row = ['pos' => $pos->value, 'form' => self::lemma($form), 'base' => self::lemma($base)];
                    $key = implode('|', $row);
                    if (! isset($seen[$key])) {
                        $seen[$key] = true;
                        yield $row;
                    }
                }
            }
        }
    }

    /**
     * @return Generator<array{0: string[], 1: string}> the space separated fields before the gloss, and the gloss
     */
    private function dataLines(WordNetPos $pos): Generator
    {
        foreach ($this->lines('data.'.$pos->file()) as $line) {
            // the license header is indented
            if (str_starts_with($line, ' ')) {
                continue;
            }

            [$data, $gloss] = explode(' | ', $line, 2) + [1 => ''];
            yield [explode(' ', trim($data)), trim($gloss)];
        }
    }

    /**
     * @return Generator<string>
     */
    private function lines(string $file): Generator
    {
        $path = $this->_path.DIRECTORY_SEPARATOR.$file;
        $handle = @fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException("Cannot read the WordNet file {$path}.");
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line, "\r\n");
                if ($line !== '') {
                    yield $line;
                }
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Satellites are addressed through the adjective file, so they share its letter.
     */
    private static function synsetId(string $offset, WordNetPos $pos): string
    {
        $letter = $pos === WordNetPos::ADJECTIVE_SATELLITE ? WordNetPos::ADJECTIVE->value : $pos->value;

        return $offset.'-'.$letter;
    }

    private static function lemma(string $lemma): string
    {
        return mb_strtolower(str_replace('_', ' ', $lemma));
    }
}
