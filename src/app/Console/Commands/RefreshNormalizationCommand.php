<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use App\Helpers\StringHelper;
use App\Models\{Word, Keyword};

class RefreshNormalizationCommand extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed-normalization:refresh {context}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes all normalizations.';

    /**
     * Minimum time elapsed during normalization.
     *
     * @var integer
     */
    private $_minTime = PHP_INT_MAX;

    /**
     * Maximum time elapsed during normalization.
     *
     * @var integer
     */
    private $_maxTime = PHP_INT_MIN;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        switch ($this->argument('context')) {
            case 'words':
                $this->handleWords();
                break;
            case 'keywords':
                $this->handleKeywords();
                break;
        }

        $this->info('All normalizations have been updated. Minimum time: '.$this->_minTime.' ms, maximum time: '.$this->_maxTime.' ms.');
    }

    private function handleWords() 
    {
        $words = Word::get();
        foreach ($words as $word) {
            $time = microtime(true);
            $normalizedWord = StringHelper::normalize($word->word);
            $this->log($word->word, $normalizedWord, $time);

            $word->normalized_word = $normalizedWord;
            $word->reversed_normalized_word = strrev($normalizedWord);

            $word->save();
        }

        unset($words);
    }

    private function handleKeywords() 
    {
        $n = 0;

        do {
            $keywords = Keyword::skip($n)->take(1000)->get();

            foreach ($keywords as $keyword) {
                $time = microtime(true);
                $normalizedWord = StringHelper::normalize($keyword->keyword);
                $this->log($keyword->keyword, $normalizedWord, $time);

                $time = microtime(true);
                $normalizedWordUnaccented = StringHelper::normalize($keyword->keyword, /* accentsMatter: */ false);
                $this->log($keyword->keyword, $normalizedWord, $time);

                $keyword->normalized_keyword = $normalizedWord;
                $keyword->reversed_normalized_keyword = strrev($normalizedWord);
                $keyword->normalized_keyword_unaccented = $normalizedWordUnaccented;
                $keyword->reversed_normalized_keyword_unaccented = strrev($normalizedWordUnaccented);

                $keyword->normalized_keyword_length = mb_strlen($keyword->normalized_keyword);
                $keyword->reversed_normalized_keyword_length = mb_strlen($keyword->reversed_normalized_keyword);
                $keyword->normalized_keyword_unaccented_length = mb_strlen($keyword->normalized_keyword_unaccented);
                $keyword->reversed_normalized_keyword_unaccented_length = mb_strlen($keyword->reversed_normalized_keyword_unaccented);

                $keyword->save();
            }

            $n += $keywords->count();
        } while ($keywords->count());

        unset($keywords);
    }

    private function log(string $before, string $new, int $time) 
    {
        $time = microtime(true) - $time;
        $time /= 1000;

        $this->_minTime = min($this->_minTime, $time);
        $this->_maxTime = max($this->_maxTime, $time);
    }
}
