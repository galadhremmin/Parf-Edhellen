<?php

namespace App\Repositories;

use App\Models\{
    Account,
    AccountFeedRefreshTime,
    ForumPost,
    Gloss,
    Sentence
};
use App\Models\Initialization\Morphs;
use Illuminate\Support\Carbon;

class AccountFeedRepository
{
    public function generateForAccountId(int $id)
    {
        $account = Account::findOrFail($id);

        $classNames = [
            Sentence::class,
            Gloss::class,
            ForumPost::class,
        ];

        $lastTimestamps = AccountFeedRefreshTime::forAccount($account)
            ->get()
            ->groupBy('feed_content_name');

        foreach ($classNames as $className) {
            $morph = Morphs::getAlias($className);

            $oldest = null;
            $newest = null;
            if ($lastTimestamps->has($morph)) {
                $oldest = $lastTimestamps->get($morph)->oldest_happened_at;
                $newest = $lastTimestamps->get($morph)->newest_happened_at;
            }

            $output = $this->generateForMorphAndAccount($account, $morph, $oldest, $newest);

            if ($output === null) {
                // generation failed
                continue;
            }

            AccountFeedRefreshTime::upsert(
                array_merge($output, [
                    'account_id' => $account->id
                ]),
                [ 'account_id' ]
            );
        }
    }

    private function generateForMorphAndAccount(Account $account, string $morph, Carbon $oldest, Carbon $newest)
    {
        

        return [
            'oldest_happened_at' => null,
            'newest_happened_at' => null
        ];
    }
}
