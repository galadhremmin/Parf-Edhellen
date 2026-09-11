<?php

namespace App\Http\Controllers\Api\v3;

use App\Helpers\LinkHelper;
use App\Helpers\SentenceHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Http\Discuss\ContextFactory;
use App\Interfaces\IMarkdownParser;
use App\Models\AccountFeed;
use App\Models\AccountFeedRefreshTime;
use App\Models\ForumPost;
use App\Models\Sentence;
use App\Models\Versioning\LexicalEntryVersion;
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

        $records = AccountFeed::forAccount($id) //
            ->with('content') //
            ->orderByDesc('happened_at') //
            ->cursorPaginate(20);

        // TODO: this technically doesn't work for `gloss` nor `forum` because discuss is tracked on `ForumDiscussion` and `LexicalEntryVersion`.
        //       we need to figure out a way to handle this complicated case.
        $changed = false;
        $feed = collect([]);

        // The generator can record the same piece of content more than once --
        // there are rows in the wild whose happened_at differs by whole hours for
        // one post -- and the reader should not be shown it twice while that is
        // being sorted out at the source.
        $seen = [];

        foreach ($records->getCollection() as $record) {
            $pass = true;
            if ($record->content === null) {
                $pass = false;
            }

            if ($pass) {
                $key = $record->content_type.':'.$record->content_id;
                if (isset($seen[$key])) {
                    $pass = false;
                } else {
                    $seen[$key] = true;
                }
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

            // Parse from the value as it was loaded, not from the property we are
            // about to overwrite. Eloquent hands the same model instance to every
            // feed record that points at it, so where a post appears twice this
            // loop would otherwise parse its own output: markup escaping turns the
            // <p> from the first pass into &lt;p&gt; on the second.
            if ($c instanceof ForumPost) {
                $c->load('forum_thread');
                $c->content = $this->_markdownParser->parseMarkdownNoBlocks($c->getRawOriginal('content') ?: '');
            } elseif ($c instanceof LexicalEntryVersion) {
                // noop, relying on `useGloss` hook on client.
            } elseif ($c instanceof Sentence) {
                $c->load('language');
                $c->load('sentence_fragments');
                $c->description = $this->_markdownParser->parseMarkdownNoBlocks($c->getRawOriginal('description') ?: '');
                $c->sentence_url = $this->_linkHelper->sentence($c->language->id, $c->language->name, $c->id, $c->name);
                $c->sentence_transformations = $this->_sentenceHelper->buildSentences($c->sentence_fragments);
            }

            $feed->push($record);
        }

        if ($changed) {
            $records->setCollection($feed);
        }

        return $records;
    }
}
