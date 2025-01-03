<?php

namespace App\Http\Controllers\Api\v2;

use App\Helpers\LinkHelper;
use App\Helpers\SentenceHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Http\Discuss\ContextFactory;
use App\Interfaces\IMarkdownParser;
use App\Models\AccountFeed;
use App\Models\AccountFeedRefreshTime;
use App\Models\ForumPost;
use App\Models\Sentence;
use App\Models\Versioning\GlossVersion;
use App\Repositories\AccountFeedRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountFeedApiController extends Controller
{
    private AccountFeedRepository $_feedRepository;

    private ContextFactory $_contextFactory;

    private IMarkdownParser $_markdownParser;

    private SentenceHelper $_sentenceHelper;

    private LinkHelper $_linkHelper;

    const FILTERABLE_PROPS = ['is_deleted', 'is_hidden'];

    public function __construct(AccountFeedRepository $feedRepository, ContextFactory $contextFactory,
        IMarkdownParser $markdownParser, SentenceHelper $sentenceHelper, LinkHelper $linkHelper)
    {
        $this->_feedRepository = $feedRepository;
        $this->_contextFactory = $contextFactory;
        $this->_markdownParser = $markdownParser;
        $this->_sentenceHelper = $sentenceHelper;
        $this->_linkHelper = $linkHelper;
    }

    public function getFeed(Request $request, int $id)
    {
        if (in_array($id, config('ed.restricted_profile_ids'))) {
            return [
                'restricted' => true,
            ];
        }

        $lastChange = AccountFeedRefreshTime::forAccount($id)->forUniverse()->first();
        if ($lastChange === null || Carbon::now()->add(-15, 'minutes') > $lastChange->created_at) {
            $this->_feedRepository->generateForAccountId($id);
        }

        $feed = AccountFeed::forAccount($id) //
            ->with('content') //
            ->orderByDesc('happened_at') //
            ->cursorPaginate(20);

        // TODO: this technically doesn't work for `gloss` nor `forum` because discuss is tracked on `ForumDiscussion` and `GlossVersion`.
        //       we need to figure out a way to handle this complicated case.
        $changed = false;
        $filteredRecords = collect([]);
        foreach ($feed->getCollection() as $record) {
            $pass = true;
            if ($record->content === null) {
                $pass = false;
            }

            if ($pass) {
                foreach (self::FILTERABLE_PROPS as $prop) {
                    if ($record->content->hasAttribute($prop) &&
                        $record->content->$prop) {
                        $pass = false;
                        break;
                    }
                }
            }

            if ($pass) {
                $context = $this->_contextFactory->create($record->content_type);
                if ($context !== null && $record->content !== null && ! $context->available($record->content, $request->user())) {
                    $pass = false;
                }
            }

            if (! $pass) {
                $changed = true;

                continue; // skip this record as it does not pass our checks.
            }

            $c = $record->content;
            if ($c instanceof ForumPost) {
                $c->load('forum_thread');
                $c->content = $this->_markdownParser->parseMarkdownNoBlocks($c->content);
            } elseif ($c instanceof GlossVersion) {
                // noop, relying on `useGloss` hook on client.
            } elseif ($c instanceof Sentence) {
                $c->load('language');
                $c->load('sentence_fragments');
                $c->description = $this->_markdownParser->parseMarkdownNoBlocks($c->description ?: '');
                $c->sentence_url = $this->_linkHelper->sentence($c->language->id, $c->language->name, $c->id, $c->name);
                $c->sentence_transformations = $this->_sentenceHelper->buildSentences($c->sentence_fragments);
            }

            if ($changed) {
                // only use teh filteredRecords collection if there were actual changes made.
                $filteredRecords->push($record);
            }
        }

        // We need to reset the collection if it was modified because it'd change the array to an object if some indices
        // were removed. Reference: Illuminate\Pagination\CursorPaginator
        if ($changed) {
            $feed->setCollection($filteredRecords);
        }

        return $feed;
    }
}
