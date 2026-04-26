<?php

namespace App\Console\Commands;

use App\Helpers\LinkHelper;
use App\Interfaces\IComposesWordOfTheDayTweet;
use App\Interfaces\IPostsTweet;
use App\Repositories\LexicalEntryRepository;
use Illuminate\Console\Command;

class TweetWordOfTheDayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed:tweet-word-of-the-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tweets a word of the day.';

    /**
     * Execute the console command.
     */
    public function handle(
        LexicalEntryRepository $lexicalEntryRepository,
        LinkHelper $linkHelper,
        IComposesWordOfTheDayTweet $tweetComposer,
        IPostsTweet $poster,
    ): int {
        $entry = $lexicalEntryRepository->getRandomLexicalEntry();

        if ($entry === null) {
            $this->warn('No eligible lexical entry found.');

            return Command::FAILURE;
        }

        $language = $entry->language;
        $shortName = $language->short_name ?? '';
        $prefix = $shortName !== ''
            ? strtoupper($shortName).'.'
            : strtoupper(substr($language->name, 0, 1)).'.';

        $word = $entry->word->word;
        $url = $linkHelper->lexicalEntry($entry->id);
        $hashtags = '#'.strtolower(str_replace(' ', '', $language->name)).' #elfdict #elvish #tolkien';
        $body = $tweetComposer->composeTweet($entry);

        $tweet = sprintf('%s "%s" - %s %s %s', $prefix, $word, $body, $url, $hashtags);

        $this->line($tweet);
        $this->info(sprintf('Tweet length: %d characters', mb_strlen($tweet)));

        if (! $poster->postTweet($tweet)) {
            $this->warn('Tweet was composed but could not be posted. Check logs for details.');

            return Command::FAILURE;
        }

        $this->info('Tweet posted successfully.');

        return Command::SUCCESS;
    }
}
