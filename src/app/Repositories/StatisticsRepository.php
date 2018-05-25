<?php

namespace App\Repositories;

use DB;
use App\Models\{ 
    Account, 
    FlashcardResult,
    ForumPost, 
    ForumPostLike, 
    Gloss, 
    Sentence, 
    Word
};

class StatisticsRepository
{
    public function getStatisticsForAccount(Account $account)
    {
        $noOfWords = Word::forAccount($account->id)
            ->count();

        $noOfGlosses = Gloss::notDeleted()
            ->forAccount($account->id)
            ->count();

        $noOfSentences = Sentence::approved()
            ->forAccount($account->id)
            ->count();

        $noOfThanks = ForumPostLike::forAccount($account->id)
            ->count();

        $noOfFlashcards = FlashcardResult::forAccount($account->id)
            ->count();

        $noOfPosts = ForumPost::forAccount($account->id)
            ->count();

        return [
            'noOfWords'      => $noOfWords,
            'noOfGlosses'    => $noOfGlosses,
            'noOfSentences'  => $noOfSentences,
            'noOfThanks'     => $noOfThanks,
            'noOfFlashcards' => $noOfFlashcards,
            'noOfPosts'      => $noOfPosts
        ];
    }

    /**
     * Gets a list of top contributors based on the number of glosses, sentences, contributions,
     * forum_posts, likes, and flashcard cards registered to their account.
     *
     * @param int $numberOfResultsPerCategory the number of results to yield maximum per qualification category.
     * @return mixed an object containing statistics.
     */
    public function getContributors(int $numberOfResultsPerCategory = 5)
    {
        $columns = ['id'];
        $tables = ['glosses', 'sentences', 'contributions', 'forum_posts', 'flashcard_results'];
        $data = [];
        foreach ($tables as $table) {
            $data[$table] = $this->getTopContributors($table, $columns, $numberOfResultsPerCategory);
        }
        $data['forum_post_likes'] = $this->getTopContributorsByLikes($columns, $numberOfResultsPerCategory);
        
        // Retrieve user accounts for the accounts specified in the aforementioned result set.
        $accountIds = array_reduce(array_keys($data), function ($carry, $key) use($data) {
            foreach ($data[$key] as $account) {
                if (!in_array($account->id, $carry)) {
                    $carry[] = $account->id;
                }
            }
            sort($carry);
            return $carry;
        }, []);

        // Transform the array of accounts into an associative array, where the account ID is the key.
        $data['accounts'] = count($accountIds) < 1 
            ? [] 
            : Account::whereIn('id', $accountIds)->get()->reduce(function ($carry, $account) {
                $carry[$account->id] = $account;
                return $carry;
            }, []); 
        return $data;
    }

    /**
     * Prefixes the specified array of columns with the specified table name.
     *
     * @param array $columns an array with columns in the specified table name.
     * @param string $tableName the name of the table, ie. the prefix.
     * @return array
     */
    private function prefixColumnsWithTableName(array $columns, string $tableName)
    {
        return array_map(function ($column) use($tableName) {
            return $tableName.'.'.$column;
        }, $columns);
    }

    /**
     * Adds `count(*)` to the specified array of columns.
     *
     * @param array $columns
     * @param string $columnName
     * @return array
     */
    private function addCountToColumns(array $columns, string $columnName)
    {
        return array_merge($columns, [ DB::raw('count(*) as '.$columnName) ]);
    }

    /**
     * Gets the specified information about the top contributors based on the `account_id`
     * association in the specified table. The table must have the aforementioned column for
     * this method to be successful.
     *
     * @param string $table
     * @param array $columns
     * @param integer $numberOfResults
     * @return array
     */
    private function getTopContributors(string $table, array $columns, int $numberOfResults)
    {
        $columns = $this->prefixColumnsWithTableName($columns, 'accounts');
        return Account::select($this->addCountToColumns($columns, $table))
            ->join($table, $table.'.account_id', '=', 'accounts.id')
            ->groupBy($columns)
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->take($numberOfResults)
            ->get()
            ->all();
    }

    /**
     * Gets the top recipient of likes.
     *
     * @param array $columns
     * @param integer $numberOfResults
     * @return array
     */
    private function getTopContributorsByLikes(array $columns, int $numberOfResults)
    {
        $columns = $this->prefixColumnsWithTableName($columns, 'accounts');
        return ForumPostLike::select($this->addCountToColumns($columns, 'forum_post_likes'))
            ->join('forum_posts', 'forum_posts.id', '=', 'forum_post_likes.forum_post_id')
            ->join('accounts', 'accounts.id', '=', 'forum_posts.account_id')
            ->groupBy($columns)
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->take($numberOfResults)
            ->get()
            ->all();
    }
}
