<?php

namespace App\Repositories;

use App\Models\{
    Account,
    AccountFeed,
    AccountFeedRefreshTime,
    ForumPost,
    Gloss,
    Sentence
};
use App\Models\Initialization\Morphs;
use Illuminate\Support\{
    Carbon,
    Str
};
use Illuminate\Support\Facades\Date;

class AccountFeedRepository
{
    public function generateForAccountId(int $id)
    {
        $account = Account::findOrFail($id);

        $classNames = [
            ForumPost::class,
            Gloss::class,
            Sentence::class,
        ];

        $lastTimestamps = AccountFeedRefreshTime::forAccount($account)
            ->get()
            ->groupBy('feed_content_name');

        $feed = collect([]);

        foreach ($classNames as $className) {
            $morph = Morphs::getAlias($className);

            $newest = null;
            if ($lastTimestamps->has($morph)) {
                $newest = $lastTimestamps->get($morph)->first()->newest_happened_at;
            }

            $output = $this->generateForMorphAndAccount($account, $morph, $newest);

            foreach ($output['entities'] as $record) {
                $record->morph = $morph;
                $feed->push($record);
            }
        }

        if ($feed->count() < 1) {
            return;
        }

        $records = $feed->sortByDesc('created_at')->map(function ($record) use ($account) {
            return [
                'id'                    => (string) Str::uuid(),
                'account_id'            => $account->id,
                'happened_at'           => $record->created_at,
                'content_name'          => $record->morph,
                'content_id'            => $record->id,
                'created_at'            => Date::now(),
                'audit_trail_action_id' => null, // TODO
                'audit_trail_id'        => null  // TODO
            ];
        });

        $newDates = $records->reduce(function ($dates, $record) {
            if (! isset($dates[$record['content_name']])) {
                $dates[$record['content_name']] = $record['happened_at'];
            } else if ($dates[$record['content_name']] < $record['happened_at']) {
                $date[$record['content_name']] = $record['happened_at'];
            }

            return $dates;
        }, collect([]))->reduce(function ($args, $date, $morph) use ($account) {
            $args[] = [
                'account_id' => $account->id,
                'feed_content_name' => $morph,
                'newest_happened_at' => $date,
                'oldest_happened_at' => null, // TODO
            ];
            return $args;
        }, []);

        AccountFeed::insert($records->all());
        AccountFeedRefreshTime::upsert(
            $newDates,
            /* unique by: */ [ 'account_id', 'feed_content_name' ],
            /* update: */ [ 'newest_happened_at', 'oldest_happened_at' ]
        );
    }

    private function generateForMorphAndAccount(Account $account, string $morph, Carbon|null $newest)
    {
        $entityModel = Morphs::getMorphedModel($morph);

        $query = $entityModel::forAccount($account) //
            ->orderBy('id', 'desc');

        if ($newest !== null) {
            $query = $query->where('created_at', '>', $newest);
        }

        $entities = $query //
            ->select('id', 'created_at', 'updated_at') //
            ->get();

        if ($entities->count() < 1) {
            return [
                'oldest_happened_at' => null,
                'newest_happened_at' => null,
                'entities' => collect([])
            ];
        }

        return [
            'oldest_happened_at' => $entities->last()?->created_at,
            'newest_happened_at' => $entities->first()?->created_at,
            'entities' => $entities
        ];
    }
}
