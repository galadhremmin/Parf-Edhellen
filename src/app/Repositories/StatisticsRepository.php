<?php

namespace App\Repositories;

use DB;
use Carbon\Carbon;
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
    public function getGlobalStatistics()
    {
        $noOfWords = Word::count();
        $noOfGlosses = Gloss::active()
            ->count();
        $noOfSentences = Sentence::approved()
            ->count();
        $noOfThanks = ForumPostLike::count();
        $noOfFlashcards = FlashcardResult::count();
        $noOfPosts = ForumPost::count();

        return [
            'noOfWords'      => $noOfWords,
            'noOfGlosses'    => $noOfGlosses,
            'noOfSentences'  => $noOfSentences,
            'noOfThanks'     => $noOfThanks,
            'noOfFlashcards' => $noOfFlashcards,
            'noOfPosts'      => $noOfPosts
        ];
    }

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
        $tables = ['forum_posts', 'flashcard_results', 'sentences'];
        $data = [];
        foreach ($tables as $table) {
            $data[$table] = $this->getTopContributors($table, $columns, $numberOfResultsPerCategory);
        }

        // Only count approved contributions
        $data['contributions'] = $this->getTopContributors('contributions', $columns, $numberOfResultsPerCategory, function ($query) {
            return $query->where('contributions.is_approved', 1);
        });
        
        // Likes are saved in a rather peculiar manner and must be extracted individually.
        $data['forum_post_likes'] = $this->getTopContributorsByLikes($columns, $numberOfResultsPerCategory);
        
        // Retrieve a list of all categories, and their total count
        $data['categories'] = array_keys($data);
        $data['totals'] = $this->getNumberOfEntities($data['categories']);

        // Retrieve approved, latest glosses
        $data['glosses'] = $this->getTopContributors('glosses', $columns, $numberOfResultsPerCategory, function ($query) {
            return $query->where([
                ['glosses.is_deleted', 0]
            ]);
        });
        $data['categories'][] = 'glosses';
        $data['totals']['glosses'] = Gloss::active()->count();

        // Retrieve growth over time (grouped by day) and involve the members previously identified as parth of the growth.
        $data['growth'] = $this->getGrowthOverTime($data['categories'], Carbon::now()->addYears(-1), Carbon::now(), 
            // create an array [category] => [account ids]
            array_reduce($data['categories'], function ($carry, $category) use ($data) {
                $carry[$category] = array_unique(array_map(function ($v) {
                    return $v->id;
                }, $data[$category]));

                return $carry;
            }, []));
        $data['growth']['new_accounts'] = $this->getGrowthOverTime(['accounts'], Carbon::now()->addYears(-1), Carbon::now())['accounts'];

        // Retrieve newest accounts
        $data['new_accounts'] = $this->getNewestAccounts($numberOfResultsPerCategory);
        $data['categories'][] = 'new_accounts';
        
        // Retrieve user accounts for the accounts specified in the aforementioned result set.
        $accountIds = array_reduce($data['categories'], function ($carry, $key) use($data) {
            foreach ($data[$key] as $account) {
                if (! in_array($account->id, $carry)) {
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
     * @param callable $where
     * @return array
     */
    private function getTopContributors(string $table, array $columns, int $numberOfResults, callable $where = null)
    {
        $columns = $this->prefixColumnsWithTableName($columns, 'accounts');
        $query = Account::select($this->addCountToColumns($columns, 'number_of_items'))
            ->join($table, $table.'.account_id', '=', 'accounts.id')
            ->groupBy($columns)
            ->orderBy(DB::raw('count(*)'), 'desc');

        if ($where !== null) {
            $query = $where($query);
        }

        return $query->take($numberOfResults)
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
        return ForumPostLike::select($this->addCountToColumns($columns, 'number_of_items'))
            ->join('forum_posts', 'forum_posts.id', '=', 'forum_post_likes.forum_post_id')
            ->join('accounts', 'accounts.id', '=', 'forum_posts.account_id')
            ->groupBy($columns)
            ->orderBy(DB::raw('count(*)'), 'desc')
            ->take($numberOfResults)
            ->get()
            ->all();
    }

    /**
     * Gets the most recently created accounts.
     * 
     * @param int $numberOfResults 
     * @return array
     */
    private function getNewestAccounts(int $numberOfResults) 
    {
        return Account::orderBy('id', 'desc')
            ->select('id')
            ->take($numberOfResults)
            ->get()
            ->all();
    }

    /**
     * Gets the number of entities within the specified tables.
     * 
     * @param array $tableNames
     * @return array
     */
    private function getNumberOfEntities(array $tableNames)
    {
        $data = [];
        foreach ($tableNames as $tableName) {
            $data[$tableName] = DB::table($tableName)->count();
        }

        return $data;
    }

    /**
     * Gets growth over the specified time period. Returns an array with `date` and `number_of_items`.
     * @param array $tableNames
     * @param Carbon $from
     * @param Carbom $to
     * @param array $accountsPerTable
     * @return array
     */
    private function getGrowthOverTime(array $tableNames, Carbon $from, Carbon $to, array $accountsPerTable = [])
    {
        $data = [];
        foreach ($tableNames as $tableName) {
            $totalGrowth = DB::table($tableName)
                ->select(DB::raw('COUNT(*) AS number_of_items'), DB::raw('DATE(created_at) AS date'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->where([
                    ['created_at', '>=', $from],
                    ['created_at', '<=', $to] 
                ])
                ->orderBy('created_at')
                ->get();
            
            $accounts = isset($accountsPerTable[$tableName]) ? $accountsPerTable[$tableName] : [];
            if (count($accounts) > 0) {
                $growthPerAccountAndDay = DB::table($tableName)
                    ->select(DB::raw('COUNT(*) AS number_of_items'), 'accounts.nickname', DB::raw('DATE('.$tableName.'.created_at) AS date'))
                    ->join('accounts', 'accounts.id', $tableName.'.account_id')
                    ->groupBy(DB::raw('DATE('.$tableName.'.created_at)'), 'accounts.nickname')
                    ->where([
                        [$tableName.'.created_at', '>=', $from],
                        [$tableName.'.created_at', '<=', $to] 
                    ])
                    ->whereIn($tableName.'.account_id', $accounts)
                    ->orderBy($tableName.'.created_at')
                    ->get()
                    ->groupBy('date');
                
                $totalGrowthPerDay = $totalGrowth->reduce(function ($carry, $value) {
                    $carry[$value->date] = &$value;
                    return $carry;
                }, []);
                
                foreach ($growthPerAccountAndDay as $date => $growthByAccounts) {
                    $contextualGrowth = &$totalGrowthPerDay[$date];
                    foreach ($growthByAccounts as $growth) {
                        $nickname = $growth->nickname;
                        $contextualGrowth->$nickname = $growth->number_of_items;
                    }
                }
            }

            $data[$tableName] = $totalGrowth->all();
        }

        return $data;
    }
}
