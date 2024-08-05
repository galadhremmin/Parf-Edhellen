<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Abstracts\Controller;
use App\Http\Discuss\ContextFactory;
use App\Models\AccountFeed;
use App\Models\AccountFeedRefreshTime;
use App\Repositories\AccountFeedRepository;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\CursorPaginator;

class AccountFeedApiController extends Controller 
{
    /**
     * @var AccountFeedRepository
     */
    private $_feedRepository;
    /**
     * @var ContextFactory
     */
    private $_contextFactory;

    const FILTERABLE_PROPS = ['is_deleted', 'is_hidden'];

    public function __construct(AccountFeedRepository $feedRepository, ContextFactory $contextFactory) 
    {
        $this->_feedRepository = $feedRepository;
        $this->_contextFactory = $contextFactory;
    }

    public function getFeed(Request $request, int $id)
    {
        $lastChange = AccountFeedRefreshTime::forAccount($id)->forUniverse()->first();
        if ($lastChange === null || $lastChange->created_at < Carbon::now()->add(1, 'week')) {
            $this->_feedRepository->generateForAccountId($id);
        }

        $feed = AccountFeed::forAccount($id) //
            ->with('content') //
            ->orderByDesc('happened_at') //
            ->cursorPaginate(20);

        // TODO: this technically doesn't work for `gloss` nor `forum` because discuss is tracked on `ForumDiscussion` and `GlossVersion`.
        //       we need to figure out a way to handle this complicated case.
        for ($offset = 0; $offset < $feed->getCollection()->count(); $offset += 1) {
            $record = $feed->getCollection()->get($offset);

            if ($record->content === null) {
                continue;
            }

            $pass = true;
            foreach (self::FILTERABLE_PROPS as $prop) {
                if ($record->content->hasAttribute($prop) &&
                    $record->content->$prop) {
                    $feed->getCollection()->offsetUnset($offset);
                    $pass = false;
                    break;
                }
            }

            if ($pass === true) {
                $context = $this->_contextFactory->create($record->content_type);
                if ($context !== null && $record->content !== null && ! $context->available($record->content, $request->user())) {
                    $feed->getCollection()->offsetUnset($offset);
                }
            }
        }

        return $feed;
    }
}
